<?php
/**
 * FreePBX Click-to-Call PHP Script
 * Adapted from Alisson Pelizaro's version: https://github.com/alissonpelizaro/Asterisk-Click-to-Call
 * Provides input validation, JSON results, and improved readability.
 * Provides pjsip support and error handling.
 * This script is intended to be used with FreePBX and Asterisk for click-to-call functionality.
 */

// Configuration settings
$config = [
    'host' => '127.0.0.1',
    'port' => 5038, // Default AMI port
    'user' => 'admin', // AMI username
    'secret' => 'MYSECRETPASS', // AMI password
    'callerIdTemplate' => 'CTR Plugin (%s)',
    'context' => 'from-internal',
    'waitTime' => 30,
    'priority' => 1,
    'maxRetry' => 2,
];

// Retrieve and sanitize request parameters
$extension = isset($_REQUEST['exten']) ? trim($_REQUEST['exten']) : '';
$number = isset($_REQUEST['number']) ? trim(strtolower($_REQUEST['number'])) : '';
$tech = isset($_REQUEST['tech']) && strtolower($_REQUEST['tech']) === 'pjsip' ? 'PJSIP' : 'SIP'; // Default to SIP if not specified

// Initialize result array
$result = [
    'Success' => true,
    'ValidInput' => true,
    'Description' => '',
];

// Validate input parameters
if (!preg_match('/^\\+?[0-9]+$/', $number)) {
    $result = setError($result, "Invalid number format: %s", $number);
}

if (!preg_match('/^[0-9]+$/', $extension)) {
    $result = setError($result, "Invalid extension format: %s", $extension);
}

// Check if request is from a local IP address
if ($result['Success'] && filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
    $result = setError($result, "Unauthorized IP address: %s", $_SERVER["REMOTE_ADDR"]);
}

// Establish socket connection and authenticate
if ($result['Success']) {
    $socket = stream_socket_client("tcp://{$config['host']}:{$config['port']}", $errno, $errstr);

    if (!$socket) {
        $result = setError($result, "Socket connection failed: %s (%s)", $errstr, $errno);
    } else {
        $authRequest = "Action: Login\r\nUsername: {$config['user']}\r\nSecret: {$config['secret']}\r\nEvents: off\r\n\r\n";
        fwrite($socket, $authRequest);
        usleep(200000);
        $authResponse = fread($socket, 4096);

        if (strpos($authResponse, 'Success') === false) {
            $result = setError($result, "Authentication failed");
        } else {
            // Originate call with support for both chan_sip and chan_pjsip
            $originateRequest = "Action: Originate\r\nChannel: $tech/$extension\r\nWaitTime: {$config['waitTime']}\r\nCallerId: " . sprintf($config['callerIdTemplate'], $number) . "\r\nExten: $number\r\nContext: {$config['context']}\r\nPriority: {$config['priority']}\r\nAsync: yes\r\n\r\n";
            fwrite($socket, $originateRequest);
            usleep(200000);
            $originateResponse = fread($socket, 4096);

            if (strpos($originateResponse, 'Success') !== false) {
                $result['Description'] = "Extension $extension is calling $number.";
            } else {
                $result = setError($result, "Call initiation failed");
            }

            // Logoff
            fwrite($socket, "Action: Logoff\r\n\r\n");
        }

        fclose($socket);
    }
}

// Helper function to set error
function setError($result, $message, ...$args) {
    $result['Success'] = false;
    $result['ValidInput'] = false;
    $result['Description'] = vsprintf($message, $args);
    return $result;
}

// Output JSON result
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);

?>