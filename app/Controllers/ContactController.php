<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Services\ContactMailer;
use Throwable;

final class ContactController
{
    private const STORE_TYPES = [
        'ferreteria' => 'Ferretería al público',
        'distribuidor' => 'Distribuidor',
        'mayorista' => 'Mayorista',
    ];

    public function show(): void
    {
        View::render('pages/contact', [
            'title' => 'Contacto',
            'errors' => pull_flash('contact_errors', []),
            'success' => pull_flash('contact_success'),
            'old' => pull_flash('contact_old', []),
            'storeTypes' => self::STORE_TYPES,
        ]);
    }

    public function send(): void
    {
        $values = [];
        foreach (['full_name', 'phone', 'email', 'province', 'store_type', 'message'] as $field) {
            $values[$field] = $this->input($field);
        }

        $token = $_POST['_token'] ?? null;
        if (!verify_csrf(is_string($token) ? $token : null)) {
            $this->fail(['La sesión del formulario venció. Volvé a intentarlo.'], $values);
            return;
        }

        // This field is hidden from visitors and should only be filled by bots.
        if ($this->input('website') !== '') {
            $this->succeed();
            return;
        }

        $errors = [];
        $fields = [
            'full_name' => ['Nombre y apellido', 2, 150],
            'phone' => ['Teléfono', 1, 50],
            'email' => ['Email', 1, 254],
            'province' => ['Provincia', 0, 100],
        ];

        foreach ($fields as $field => [$label, $minLength, $maxLength]) {
            if ($values[$field] === '' && $minLength > 0) {
                $errors[] = 'Completá ' . $label . '.';
            } elseif (preg_match('/\A[^\x00-\x1F\x7F]{' . $minLength . ',' . $maxLength . '}\z/u', $values[$field]) !== 1) {
                $length = $minLength > 0 ? 'entre ' . $minLength . ' y ' . $maxLength : 'hasta ' . $maxLength;
                $errors[] = 'Revisá ' . $label . ': ingresá ' . $length . ' caracteres válidos.';
            }
        }

        if ($values['phone'] !== '') {
            $phoneDigits = preg_replace('/[^0-9]/', '', $values['phone']);
            if (preg_match('/\A\+?[0-9 ().-]+\z/', $values['phone']) !== 1
                || strlen($phoneDigits) < 7 || strlen($phoneDigits) > 15) {
                $errors[] = 'Ingresá un teléfono válido de entre 7 y 15 dígitos. Podés usar +, espacios, guiones y paréntesis.';
            }
        }

        if ($values['email'] !== '' && filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Ingresá un email válido.';
        }

        if ($values['store_type'] !== '' && !isset(self::STORE_TYPES[$values['store_type']])) {
            $errors[] = 'Seleccioná un tipo de punto de venta válido.';
        }

        // Allow line breaks and tabs in the optional message.
        if (preg_match('/\A[^\x00-\x08\x0B\x0C\x0E-\x1F\x7F]{0,2000}\z/u', $values['message']) !== 1) {
            $errors[] = 'La consulta o aclaración debe tener hasta 2000 caracteres y no contener caracteres inválidos.';
        }

        if ($errors !== []) {
            $this->fail($errors, $values);
            return;
        }

        if (time() - (int)($_SESSION['contact_last_sent_at'] ?? 0) < 60) {
            $this->fail(['Ya recibimos tu consulta. Esperá un minuto antes de enviar otra.'], $values);
            return;
        }

        try {
            (new ContactMailer())->send($values, self::STORE_TYPES[$values['store_type']] ?? 'No especificado');
        } catch (Throwable $exception) {
            error_log('[contact] Email delivery failed: ' . $exception->getMessage());
            $this->fail([
                'No pudimos enviar tu consulta. Intentá nuevamente o escribinos a info@mazalimportaciones.com.ar.',
            ], $values);
            return;
        }

        $_SESSION['contact_last_sent_at'] = time();
        $this->succeed();
    }

    private function input(string $field): string
    {
        $value = $_POST[$field] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    private function fail(array $errors, array $values): void
    {
        flash('contact_errors', $errors);
        flash('contact_old', $values);
        redirect('/contact');
    }

    private function succeed(): void
    {
        flash('contact_success', '¡Gracias! Tu consulta fue enviada. Nos vamos a comunicar con vos.');
        redirect('/contact');
    }
}
