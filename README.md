# FreePBX Click-to-Call PHP Script

[![PHP Version](https://img.shields.io/badge/PHP-7.0%2B-blue.svg)](https://www.php.net/)
[![FreePBX Compatibility](https://img.shields.io/badge/FreePBX-14%2B-green.svg)](https://www.freepbx.org/)
[![Asterisk Compatibility](https://img.shields.io/badge/Asterisk-13%2B-orange.svg)](https://www.asterisk.org/)

A simple and effective PHP script designed for FreePBX and Asterisk servers, providing a convenient Click-to-Call Web API. This script allows users to initiate calls directly from a web interface. It leverages the Asterisk Manager Interface (AMI).

Adapted from [Alisson Pelizaro's Click-to-Call script](https://github.com/alissonpelizaro/Asterisk-Click-to-Call), this version adds input validation, JSON responses, improved readability, PJSIP support, and enhanced error handling.

## Table of Contents

- [Features](#features)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Security Considerations](#security-considerations)
- [Troubleshooting](#troubleshooting)
- [Remote Work Usage](#remote-work-usage)
- [Version History](#version-history)
- [License](#license)
- [Credits](#credits)

## Features

- **Easy Integration**: Quickly integrate with your existing FreePBX and Asterisk setup.
- **JSON Response**: Provides clear JSON responses for easy integration with other applications.
- **CHAN_SIP and PJSIP Support**: Fully compatible with both CHAN_SIP and PJSIP technology.
- **Secure and Validated**: Includes input validation and IP address checks for enhanced security.
- **Asynchronous Call Origination**: Calls are originated asynchronously for better performance.
- **Detailed Error Handling**: Provides descriptive error messages for troubleshooting.
- **Technology Detection**: Automatically detects whether an extension uses PJSIP or SIP technology.
- **Customizable Caller ID**: Supports custom caller ID templates.

## Prerequisites

Before installing the Click-to-Call script, ensure your system meets the following requirements:

- **PHP**: Version 7.0 or higher with `json` and `sockets` extensions enabled
- **FreePBX**: Version 14 or higher (tested with v14)
- **Asterisk**: Version 13 or higher (tested with v18)
- **Network**: Script must be accessed from a local network (private IP range) for security
- **AMI Access**: Asterisk Manager Interface must be enabled and configured

## Installation

Follow these simple steps to install and configure the Click-to-Call script on your FreePBX server:

1. **Upload the Script**
   - Place the `freepbx-click-to-call.php` file in your web directory:
     ```bash
     /var/www/html/
     ```

2. **Configure AMI Credentials**
   - Edit the script and set your AMI credentials found in:
     ```bash
     /etc/asterisk/manager.conf
     ```
   - Update the `$config` array in the script with your AMI username and password.

3. **Set Permissions**
   - Ensure the script has appropriate permissions so that the web server can execute the PHP script:
     ```bash
     chmod 644 /var/www/html/freepbx-click-to-call.php
     chown www-data:www-data /var/www/html/freepbx-click-to-call.php
     ```

4. **Verify Installation**
   - Test the script by accessing it through your web browser:
     ```
     http://YOUR.PBX.IP/freepbx-click-to-call.php?exten=123&number=0612345678
     ```
   - You should receive a JSON response indicating success or any configuration issues.

## Configuration

The script includes several configuration options that can be customized to fit your specific needs. These options are defined in the `$config` array at the beginning of the script:

| Option | Default | Description |
|--------|---------|-------------|
| `host` | 127.0.0.1 | The IP address of the Asterisk Manager Interface (AMI). Usually localhost (127.0.0.1) if the script is running on the same server as FreePBX. |
| `port` | 5038 | The port number for the AMI. The default is 5038, but it may be different in your configuration. |
| `user` | admin | The username for AMI authentication. This should match a user defined in `/etc/asterisk/manager.conf`. |
| `secret` | MYSECRETPASS | The password for AMI authentication. This should match the password for the user defined in `/etc/asterisk/manager.conf`. |
| `callerIdTemplate` | CTR Plugin (%s) | The template for the caller ID. The `%s` will be replaced with the number being called. |
| `context` | from-internal | The dialplan context to use for the outgoing call. The default is `from-internal`, which is standard for FreePBX. |
| `waitTime` | 30 | The number of seconds to wait for the extension to answer before timing out. |
| `priority` | 1 | The dialplan priority to use for the outgoing call. |
| `maxRetry` | 2 | The maximum number of retry attempts if the call fails. |

### Example Configuration Changes

To change the caller ID template to show "Outbound Call to %s":

```php
$config = [
    // ... other settings ...
    'callerIdTemplate' => 'Outbound Call to (%s)',
    // ... other settings ...
];
```

To increase the wait time to 45 seconds:

```php
$config = [
    // ... other settings ...
    'waitTime' => 45,
    // ... other settings ...
];
```

## Usage

### Basic Usage

To initiate a call, simply open the following URL in your browser or call it via an HTTP request:

```http
http://YOUR.PBX.IP/freepbx-click-to-call.php?exten=123&number=0612345678
```

- Replace `123` with the extension number you want to ring first.
- Replace `0612345678` with the phone number you wish to call.

The specified extension will ring first. Once answered, FreePBX will automatically dial the provided phone number.

### Parameter Details

| Parameter | Description | Validation Rules |
|-----------|-------------|------------------|
| `exten` | The extension that will ring first. This must be a valid extension configured in your FreePBX system. | Must contain only digits (0-9). |
| `number` | The phone number to call after the extension answers. This can be an internal extension, a local number, or an international number depending on your dialplan. | Must contain only digits (0-9) and optionally a leading plus sign (+). |

### API Usage Examples

#### Using cURL

```bash
curl "http://YOUR.PBX.IP/freepbx-click-to-call.php?exten=123&number=0612345678"
```

#### Using PHP

```php
$response = file_get_contents('http://YOUR.PBX.IP/freepbx-click-to-call.php?exten=123&number=0612345678');
$result = json_decode($response, true);
if ($result['Success']) {
    echo "Call initiated successfully!";
} else {
    echo "Error: " . $result['Description'];
}
```

#### Using JavaScript/jQuery

```javascript
$.getJSON('http://YOUR.PBX.IP/freepbx-click-to-call.php?exten=123&number=0612345678', function(data) {
    if (data.Success) {
        alert("Call initiated successfully!");
    } else {
        alert("Error: " + data.Description);
    }
});
```

## Remote Work Usage

This Click-to-Call script is particularly useful for remote workers who wish to use their work PBX server for initiating calls. By configuring the personal extension to forward incoming calls to an employee's mobile number, remote workers can seamlessly use their work phone number instead of their personal mobile number.

### How It Works for Remote Workers

1. The employee clicks a Click-to-Call link or button in an application (e.g., CRM, email client)
2. The script initiates a call to the employee's extension
3. The extension forwards the call to the employee's mobile phone
4. When the employee answers, the PBX connects them to the outgoing number
5. All calls are billed according to the PBX dial plan, not the employee's mobile plan

### Integration Example: Outlook Plugin

This setup has been successfully integrated into a custom Outlook plugin (not part of this project), allowing remote employees to initiate calls directly from telephone numbers listed in emails. When the user clicks a number:

1. The Outlook plugin calls the Click-to-Call API
2. The PBX initiates the call to the employee's extension
3. The call is forwarded to their mobile
4. Upon answering, the call is connected to the clicked number

This eliminates the need for manual dialing and ensures all calls appear to come from the company's phone system.

## API Documentation

The Click-to-Call script provides a simple HTTP API that returns JSON responses. This makes it easy to integrate with other applications.

### Endpoint

```
GET /freepbx-click-to-call.php
```

### Parameters

| Parameter | Required | Description |
|-----------|----------|-------------|
| `exten` | Yes | The extension that will ring first. |
| `number` | Yes | The phone number to call after the extension answers. |

### Response Format

The API always returns a JSON object with the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `Success` | boolean | Indicates whether the call was initiated successfully. |
| `ValidInput` | boolean | Indicates whether the input parameters were valid. |
| `Description` | string | A human-readable description of the result or error. |
| `Technology` | string | The technology used for the extension (PJSIP or SIP). Only present on successful calls. |
| `OriginateResponse` | string | The raw response from the Asterisk Manager Interface. Only present on successful calls. |

### Response Examples

#### Successful Response

```json
{
  "Success": true,
  "ValidInput": true,
  "Description": "Extension 123 is calling 0612345678.",
  "Technology": "PJSIP",
  "OriginateResponse": "Response: Success\r\nMessage: Originate successfully queued\r\n\r\n"
}
```

#### Invalid Number Format

```json
{
  "Success": false,
  "ValidInput": false,
  "Description": "Invalid number format: abc123",
  "Technology": "",
  "OriginateResponse": ""
}
```

#### Invalid Extension Format

```json
{
  "Success": false,
  "ValidInput": false,
  "Description": "Invalid extension format: abc",
  "Technology": "",
  "OriginateResponse": ""
}
```

#### Unauthorized IP Address

```json
{
  "Success": false,
  "ValidInput": false,
  "Description": "Unauthorized IP address: 203.0.113.1",
  "Technology": "",
  "OriginateResponse": ""
}
```

#### Authentication Failure

```json
{
  "Success": false,
  "ValidInput": false,
  "Description": "Authentication failed",
  "Technology": "",
  "OriginateResponse": ""
}
```

#### Technology Retrieval Failure

```json
{
  "Success": false,
  "ValidInput": false,
  "Description": "Failed to retrieve technology for extension: 123",
  "Technology": "",
  "OriginateResponse": ""
}
```

#### Call Initiation Failure

```json
{
  "Success": false,
  "ValidInput": false,
  "Description": "Call initiation failed",
  "Technology": "PJSIP",
  "OriginateResponse": "Response: Error\r\nMessage: Channel PJSIP/123 not available\r\n\r\n"
}
```

## Security Considerations

The Click-to-Call script includes several security features to protect your PBX system:

### IP Address Validation

The script checks if the request is coming from a local IP address (private range). This prevents unauthorized access from the public internet. The relevant code is:

```php
if (filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
    $result = setError($result, "Unauthorized IP address: %s", $_SERVER["REMOTE_ADDR"]);
}
```

If you need to allow access from specific public IP addresses, you'll need to modify this check.

### Input Validation

All input parameters are validated to prevent injection attacks:

- Extension must contain only digits
- Number must contain only digits and optionally a leading plus sign

## Troubleshooting

If you encounter issues with the Click-to-Call script, here are some common problems and solutions:

### Extension Does Not Ring

- **Check Extension Registration**: Verify the extension is correctly registered and reachable.
  ```bash
  asterisk -rx "pjsip show endpoint <extension>"  # For PJSIP
  asterisk -rx "sip show peer <extension>"        # For CHAN_SIP
  ```

- **Check Dialplan Context**: Ensure the context (`from-internal` by default) is correctly configured.
  ```bash
  asterisk -rx "dialplan show from-internal"
  ```

- **Inspect Firewall and NAT Settings**: Ensure proper connectivity.
  ```bash
  iptables -L -n
  ```

### Authentication Failures

- **Verify AMI Credentials**: Check that the username and password in the script match those in `/etc/asterisk/manager.conf`.
  ```bash
  grep -A 10 "\[admin\]" /etc/asterisk/manager.conf
  ```

- **Check AMI Permissions**: Ensure the AMI user has the necessary permissions.
  ```
  read = system,call,log,verbose,command,agent,user,config,dtmf,reporting,cdr,dialplan,originate
  write = system,call,log,verbose,command,agent,user,config,dtmf,reporting,cdr,dialplan,originate
  ```

### Technology Detection Issues

- **Check DEVICE Database**: Verify the extension's technology is correctly stored in the Asterisk database.
  ```bash
  asterisk -rx "database show DEVICE"
  ```

### Debugging with Asterisk CLI

For detailed debugging, use the Asterisk CLI with increased verbosity:

```bash
asterisk -rvvvvv
```

Then watch for events related to your extension and calls:

```
core set verbose 5
```

### Checking Logs

Review Asterisk logs for more information:

```bash
tail -f /var/log/asterisk/full
```

## Version History

### v1.0.0 (2023-04-01)
- Initial release
- Support for PJSIP and CHAN_SIP
- JSON response format
- Input validation
- IP address security check

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

This project has been partly developed in time donated by [Contour - Sheet Metal Supplier](https://www.contour.eu/en/).

Dit project is deels ontwikkeld in de tijd van [Contour - Plaatwerkleverancier](https://www.contour.eu/plaatwerk/).

Adapted from [Alisson Pelizaro's Asterisk Click-to-Call](https://github.com/alissonpelizaro/Asterisk-Click-to-Call) script.
