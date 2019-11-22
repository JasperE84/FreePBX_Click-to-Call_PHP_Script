<?php
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
        $errno = 0;
        $errstr = 0;
        $strCallerId = sprintf($strCallerId, $strNumber);

        $oSocket = fsockopen ($strHost, 5038, $errno, $errstr, 20);

        if (!$oSocket)
        {
                $result['Success'] = false;
                $result['Description'] = sprintf("Error while creating socket to [%s], %s (%s)", $strHost, $errstr, $errno);
        }
        else
        {
                # Auth
                fputs($oSocket, "Action: login\r\n");
                fputs($oSocket, "Username: $strUser\r\n");
                fputs($oSocket, "Secret: $strSecret\r\n\r\n");

                # Call
                fputs($oSocket, "Action: originate\r\n");
                fputs($oSocket, "Events: off\r\n");
                fputs($oSocket, "Channel: SIP/$strExten\r\n");
                fputs($oSocket, "WaitTime: $strWaitTime\r\n");
                fputs($oSocket, "CallerId: $strCallerId\r\n");
                fputs($oSocket, "Exten: $strNumber\r\n");
                fputs($oSocket, "Context: $strContext\r\n");
                fputs($oSocket, "Priority: $strPriority\r\n\r\n");
                fputs($oSocket, "Async: yes\r\n\r\n");

                # Deauth
                fputs($oSocket, "Action: Logoff\r\n\r\n");

                sleep(2);
                fclose($oSocket);

                $result['Description'] = sprintf("Extension %s should be calling %s.", $strExten, $strNumber);
        }
}

printf(json_encode($result, JSON_PRETTY_PRINT));
?>
