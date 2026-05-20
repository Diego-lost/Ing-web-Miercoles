<?php

class MailService
{
    public static function enviar(string $to, string $subject, string $body): bool
    {
        $config = require __DIR__ . '/config.php';
        if (!$config['mail']['enabled']) {
            return true;
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $config['mail']['from_name'] . ' <' . $config['mail']['from'] . '>',
        ];

        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    public static function recordatorioPrestamo(
        array $carpeta,
        array $prestamo,
        int $dias
    ): bool {
        $subject = "Recordatorio devolución carpeta {$carpeta['numero_carpeta']} - {$dias} días";
        $body = '<html><body>';
        $body .= '<h2>Recordatorio de devolución</h2>';
        $body .= '<p>La carpeta fiscal <strong>' . htmlspecialchars($carpeta['numero_carpeta']) . '</strong>';
        $body .= ' lleva <strong>' . $dias . ' días</strong> en préstamo.</p>';
        $body .= '<ul>';
        $body .= '<li>Solicitante: ' . htmlspecialchars($prestamo['solicitante']) . '</li>';
        $body .= '<li>Fecha préstamo: ' . htmlspecialchars($prestamo['fecha_prestamo']) . '</li>';
        $body .= '<li>Motivo: ' . htmlspecialchars($prestamo['motivo']) . '</li>';
        $body .= '</ul>';
        $body .= '<p>Por favor proceda con la devolución al Archivo Central.</p>';
        $body .= '</body></html>';

        return self::enviar($carpeta['correo_electronico'], $subject, $body);
    }

    public static function notificacionRegistroCarpeta(array $carpeta): bool
    {
        $config = require __DIR__ . '/config.php';
        $numero = htmlspecialchars($carpeta['numero_carpeta']);
        $subject = "Registro de carpeta {$carpeta['numero_carpeta']} - Archivo Central";

        $body = '<html><body>';
        $body .= '<h2>Carpeta registrada en Archivo Central</h2>';
        $body .= '<p>Su carpeta fiscal <strong>' . $numero . '</strong> fue registrada correctamente.</p>';
        $body .= '<ul>';
        $body .= '<li>Imputado: ' . htmlspecialchars($carpeta['imputado']) . '</li>';
        $body .= '<li>Agraviado: ' . htmlspecialchars($carpeta['agraviado']) . '</li>';
        $body .= '<li>Delito: ' . htmlspecialchars($carpeta['delito']) . '</li>';
        $body .= '<li>Fiscalía: ' . htmlspecialchars($carpeta['fiscalia_nombre'] ?? '') . '</li>';
        $body .= '<li>Despacho: ' . htmlspecialchars($carpeta['despacho_nombre'] ?? '') . '</li>';
        $body .= '<li>Fiscal responsable: ' . htmlspecialchars($carpeta['fiscal_responsable']) . '</li>';
        $body .= '<li>Folios: ' . (int) $carpeta['folios'] . '</li>';
        $body .= '<li>Estado: Archivo Central</li>';
        $body .= '</ul>';
        $body .= '<p>Recibirá recordatorios en este correo si la carpeta entra en préstamo por más de 5 o 10 días.</p>';
        $body .= '<p style="color:#666;font-size:12px;">' . htmlspecialchars($config['app']['name']) . '</p>';
        $body .= '</body></html>';

        return self::enviar($carpeta['correo_electronico'], $subject, $body);
    }
}
