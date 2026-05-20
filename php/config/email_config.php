<?php
/**
 * Email Configuration for Pizza Crust Delight
 * Supports both Mailjet and Gmail SMTP
 */

// Configuration logic
function getEmailConfig()
{
    return [
        'mailjet' => [
            'api_key' => 'dfeb4ac6d5466a7c1f72c67fdb02fc03',
            'api_secret' => '12c893f7206f230ce10b0c0f7c860c67',
            'sender_email' => 'beabianca.cedula@csucc.edu.ph',
            'sender_name' => 'Pizza Crust Delight'
        ]
    ];
}

/**
 * Send OTP using available email method (Mailjet preferred)
 * @param string $to Recipient email
 * @param string $otp OTP code
 * @return bool Success status
 */
function sendOTPEmail($to, $otp)
{
    $config = getEmailConfig();

    // 1. Try Mailjet (Primary)
    if (sendViaMailjet($to, $otp, $config['mailjet'])) {
        return true;
    }

    error_log("Mailjet failed for OTP to $to");
    return false;
}

/**
 * Send email via Mailjet API
 */
function sendViaMailjet($to, $otp, $config)
{
    $subject = 'Pizza Crust Delight - Password Reset Code';
    $messageText = "Hello,\n\nYou requested a password reset for your Pizza Crust Delight account.\nYour verification code is: {$otp}\nThis code will expire in 15 minutes.\n\nBest regards,\nPizza Crust Delight Team";

    // HTML Template
    $messageHtml = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
        <div style='text-align: center; margin-bottom: 20px;'>
            <h1 style='color: #E63946; margin: 0;'>Pizza Crust Delight</h1>
            <p style='color: #666; margin-top: 5px;'>Online Pizza Ordering</p>
        </div>
        <div style='background-color: #f9f9f9; padding: 20px; border-radius: 5px;'>
            <h2 style='color: #333; margin-top: 0;'>Password Reset</h2>
            <p>Hello,</p>
            <p>You requested a password reset for your Pizza Crust Delight account.</p>
            <div style='background-color: #ffffff; border: 1px solid #ddd; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #333; margin: 20px 0;'>
                {$otp}
            </div>
            <p>This code will expire in 15 minutes.</p>
            <p style='font-size: 12px; color: #888;'>If you didn't request this, please ignore this email.</p>
        </div>
        <div style='text-align: center; margin-top: 20px; font-size: 12px; color: #999;'>
            <p>&copy; " . date('Y') . " Pizza Crust Delight. All rights reserved.</p>
        </div>
    </div>
    ";

    $data = [
        'Messages' => [
            [
                'From' => [
                    'Email' => $config['sender_email'],
                    'Name' => $config['sender_name']
                ],
                'To' => [
                    [
                        'Email' => $to
                    ]
                ],
                'Subject' => $subject,
                'TextPart' => $messageText,
                'HTMLPart' => $messageHtml
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mailjet.com/v3.1/send');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 seconds connection timeout
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds total timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($config['api_key'] . ':' . $config['api_secret'])
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    if ($curl_errno) {
        error_log("Mailjet cURL Error ($curl_errno): $curl_error");
        return false; // Fail immediately if connection issue, or fall through to bad http code handling
    }

    if ($http_code === 200) {
        return true;
    }
    else {
        error_log("Mailjet API Error: HTTP $http_code. Response: $response. Curl Error: $curl_error");
        return false;
    }
}


?>