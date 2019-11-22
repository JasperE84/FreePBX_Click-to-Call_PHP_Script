<?php
# This script is an adapted version of Alissons Pelizaro's version found here: https://github.com/alissonpelizaro/Asterisk-Click-to-Call
# Script adapted to provide input validation and JSON results 

# Asterisk host details
$strHost = "127.0.0.1";
$strUser = "admin";             #specify the asterisk manager username you want to login with
$strSecret = "MYSECRETPASS";    #specify the password for the above user, can be fetched from /etc/asterisk/manager.conf

# Config
$strCallerId = "CTR Plugin (%s)"; #specify caller id with number to be called in %s placeholder
$strContext = "from-internal";
$strWaitTime = "30";            #specify the amount of time you want to try calling the specified channel before hangin up
$strPriority = "1";             #specify the priority you wish to place on making this call
$strMaxRetry = "2";             #specify the maximum amount of retries

# Request data
$strExten = $_REQUEST['exten'];
$strNumber = strtolower($_REQUEST['number']);

#
# Script execution
#
$result = array('Success' => true, 'ValidInput' => true, 'Description' => '');

# Data validation
if(!preg_match('/^\\+?[0-9]+$/i', $strNumber))
{
        $result['ValidInput'] = false;
        $result['Success'] = false;
        $result['Description'] = sprintf("Error, number to call must be provided and may only contain numeric characters and a leading plus symbol, number called was: %s", $strNumber);
}

if(!preg_match('/^[0-9]+$/i', $strExten))
{
        $result['ValidInput'] = false;
        $result['Success'] = false;
        $result['Description'] = sprintf("Error, extension to call from must be provided and may only contain numeric characters, extension provided was: %s", $strExten);
}


# Exit if not a local request
if (filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) )
{
        $result['Success'] = false;
        $result['Description'] = sprintf("Error, %s is not a local IP",$_SERVER["REMOTE_ADDR"]);
}

# Open socket
if($result['Success'] === true)
{
        $strCallerId = sprintf($strCallerId, $strNumber);

        # Create socket
        $oSocket = stream_socket_client("tcp://$strHost:$strPort/ws");
        if (!$oSocket)
        {
                $result['Success'] = false;
                $result['Description'] = sprintf("Error while creating socket to [%s]:%s, %s (%s)", $strHost, $intPort, $errstr, $errno);
        }

        # Authenticate
        if($result['Success'] === true)
        {
                // Prepare authentication request
                $authenticationRequest = "Action: Login\r\n";
                $authenticationRequest .= "Username: $strUser\r\n";
                $authenticationRequest .= "Secret: $strSecret\r\n";
                $authenticationRequest .= "Events: off\r\n\r\n";

                // Send authentication request
                $authenticate = stream_socket_sendto($oSocket, $authenticationRequest);
                if($authenticate < 0)
                {
                        $result['Success'] = false;
                        $result['Description'] = sprintf("Error while writing login request to tcp socket");
                }
                else
                {
                        // Wait for server response
                        usleep(200000);

                        // Read server response
                        $authenticateResponse = fread($oSocket, 4096);

                        // Check if authentication was successful
                        if(strpos($authenticateResponse, 'Success') === false)
                        {
                                $result['Success'] = false;
                                $result['Description'] = sprintf("Error, could not authenticate to Asterisk Manager Interface");
                        }
                }
        }

        # Originate call
        if($result['Success'] === true)
        {
                // Prepare originate request
                $originateRequest = "Action: Originate\r\n";
                $originateRequest .= "Channel: SIP/$strExten\r\n";
                $originateRequest .= "WaitTime: $strWaitTime\r\n";
                $originateRequest .= "CallerId: $strCallerId\r\n";
                $originateRequest .= "Exten: $strNumber\r\n";
                $originateRequest .= "Context: $strContext\r\n";
                $originateRequest .= "Priority: $strPriority\r\n";
                $originateRequest .= "Async: yes\r\n\r\n";

                $originate = stream_socket_sendto($oSocket, $originateRequest);
                if($originate < 0)
                {
                        $result['Success'] = false;
                        $result['Description'] = sprintf("Error, could not write call initiation request to socket.");
                }
                else
                {
                        // Wait for server response
                        usleep(200000);

                        // Read server response
                        $originateResponse = fread($oSocket, 4096);

                        // Check if originate was successful
                        if(strpos($originateResponse, 'Success') !== false)
                        {
                                $result['Description'] = sprintf("Extension %s should be calling %s.", $strExten, $strNumber);
                        }
                        else
                        {
                                $result['Success'] = false;
                                $result['Description'] = sprintf("Error, could not initiate call");
                        }
                }
        }

        # Deauth
        stream_socket_sendto($oSocket, "Action: Logoff\r\n\r\n");
}

printf(json_encode($result, JSON_PRETTY_PRINT));

