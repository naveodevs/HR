<?php

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {

    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Invalid JSON request.');
    }

    $mail = new PHPMailer(true);
    
    $mail->Timeout = 20;
$mail->SMTPKeepAlive = false;

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = 'hr.winnerhc@gmail.com';
    $mail->Password = 'uqqp krqj yvwv uytw';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(
        'hr.winnerhc@gmail.com',
        'Winner HC HR'
    );

    $recipients = $data['recipients'] ?? [];

    foreach ($recipients as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($email);
        }
    }

    if (count($mail->getToAddresses()) === 0) {
        throw new Exception('No valid recipient email address.');
    }

    $holder = htmlspecialchars($data['holder'] ?? '');
    $document = htmlspecialchars($data['document'] ?? '');
    $reference = htmlspecialchars($data['reference'] ?? '—');
    $expiry = htmlspecialchars($data['expiry'] ?? '');

    $mail->isHTML(true);
    $mail->Subject = 'Document Expiry Reminder - ' . $document;

    $mail->Body = "
        <h2>Document Expiry Reminder</h2>

        <p>Dear Team,</p>

        <p>The following document requires attention:</p>

        <table border='1' cellpadding='10' cellspacing='0'>
            <tr>
                <td><b>Holder</b></td>
                <td>{$holder}</td>
            </tr>

            <tr>
                <td><b>Document</b></td>
                <td>{$document}</td>
            </tr>

            <tr>
                <td><b>Reference</b></td>
                <td>{$reference}</td>
            </tr>

            <tr>
                <td><b>Expiry Date</b></td>
                <td>{$expiry}</td>
            </tr>
        </table>

        <p>Kindly take the necessary action.</p>

        <p>
            Regards,<br>
            <b>HR Department</b><br>
            Winner Holistic Consultants
        </p>
    ";

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Email sent successfully.'
    ]);

} catch (\Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);

}