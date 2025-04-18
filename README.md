# FreePBX Click-to-Call PHP Script

A simple and effective PHP script designed for FreePBX and Asterisk servers, providing a convenient Click-to-Call Web API. This script allows users to initiate calls directly from a web interface, enhancing productivity and ease of use. It leverages the Asterisk Manager Interface (AMI) and supports modern PJSIP technology.

## Features

- **Easy Integration**: Quickly integrate with your existing FreePBX and Asterisk setup.
- **JSON Response**: Provides clear JSON responses for easy integration with other applications.
- **CHAN_SIP and PJSIP Support**: Fully compatible with modern PJSIP technology. Also supports CHAN_SIP extensions.
- **Secure and Validated**: Includes input validation and IP address checks for enhanced security.

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

3. **Set Permissions**
   - Ensure the script has appropriate permissions so that the web server can execute the php script. 

## Usage

To initiate a call, simply open the following URL in your browser or call it via an HTTP request:

```http
http://YOUR.PBX.IP/freepbx-click-to-call.php?exten=123&number=0612345678
```

- Replace `123` with the extension number you want to ring first.
- Replace `0612345678` with the phone number you wish to call.

The specified extension will ring first. Once answered, FreePBX will automatically dial the provided phone number.

## Example JSON Response

```json
{
  "Success": true,
  "ValidInput": true,
  "Description": "Extension 123 is calling 0612345678.",
  "Technology": "PJSIP",
  "OriginateResponse": "Response: Success\r\nMessage: Originate successfully queued\r\n\r\n"
}
```

## Remote Work Usage

This Click-to-Call script is particularly useful for remote workers who wish to use their work PBX server for initiating calls. By configuring the personal extension to forward incoming calls to an employee's mobile number, remote workers can seamlessly use their work phone number instead of their personal mobile number. When the Click-to-Call link is clicked, the employee's extension rings and forwards the call to their mobile phone. The PBX then connects the extension with the outgoing number specified in the script, ensuring that all calls are billed according to the PBX dial plan.

This setup has been successfully integrated into an Outlook plugin, allowing remote employees to initiate calls directly from telephone numbers listed in emails. When the user clicks a number, the PBX initiates the call, rings the employee's extension (forwarded to their mobile), and connects the call automatically upon answering, eliminating the need for manual dialing.

## Troubleshooting

If the extension does not ring:

- Verify the extension is correctly registered and reachable.
- Check your dialplan and ensure the context (`from-internal`) is correctly configured.
- Inspect firewall and NAT settings to ensure proper connectivity.

Use the Asterisk CLI for detailed debugging:

```bash
asterisk -rvvvvv
```

## Credits

This project has been partly developed in time donated by [Contour - Sheet Metal Supplier](https://www.contour.eu/en/).

Dit project is deels ontwikkeld in de tijd van [Contour - Plaatwerkleverancier](https://www.contour.eu/plaatwerk/).
