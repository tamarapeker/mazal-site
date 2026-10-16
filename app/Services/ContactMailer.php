<?php
declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

final class ContactMailer
{
    public function send(array $values, string $storeType): void
    {
        if (!class_exists(PHPMailer::class)) {
            throw new RuntimeException('PHPMailer is missing. Run composer install.');
        }

        $host = trim((string)config('mail.host', ''));
        $encryption = (string)config('mail.encryption', 'tls');
        $username = (string)config('mail.username', '');
        $password = (string)config('mail.password', '');
        $port = (int)config('mail.port', 587);

        if ($host === '' || $port < 1 || $port > 65535) {
            throw new RuntimeException('Configure MAIL_HOST and MAIL_PORT before sending contact emails.');
        }

        if (!in_array($encryption, ['tls', 'ssl', 'none'], true)) {
            throw new RuntimeException('MAIL_ENCRYPTION must be tls, ssl or none.');
        }

        if (($username === '') !== ($password === '')) {
            throw new RuntimeException('Configure both MAIL_USERNAME and MAIL_PASSWORD.');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPAuth = $username !== '';
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = $encryption === 'none' ? '' : $encryption;
        $mail->SMTPAutoTLS = $encryption !== 'none';
        $mail->Timeout = 15;
        $mail->Timelimit = 15;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = PHPMailer::ENCODING_BASE64;

        $mail->setFrom(
            (string)config('mail.from_address', 'info@mazalimportaciones.com.ar'),
            (string)config('mail.from_name', 'Mazal Importaciones'),
        );
        $mail->addAddress('info@mazalimportaciones.com.ar');
        $mail->addReplyTo($values['email'], $values['full_name']);
        $mail->Subject = 'Nueva consulta desde la web de Mazal';
        $mail->Body = implode("\n", [
            'Nueva consulta desde el formulario de contacto de Mazal.',
            '',
            'Nombre y apellido: ' . $values['full_name'],
            'Teléfono: ' . $values['phone'],
            'Email: ' . $values['email'],
            'Provincia: ' . ($values['province'] !== '' ? $values['province'] : 'No especificada'),
            'Tipo de punto de venta: ' . $storeType,
            '',
            'Consulta o aclaración:',
            $values['message'] !== '' ? $values['message'] : 'Sin comentarios adicionales.',
        ]);

        $mail->send();
    }
}
