<?php

/**
 * Ejecutar diariamente (Programador de tareas Windows o cron):
 * php "c:\xampp\htdocs\Practica Ing.web\cron\recordatorios.php"
 */

require_once dirname(__DIR__) . '/shared/bootstrap.php';
require_once dirname(__DIR__) . '/services/carpetas/models/CarpetaModel.php';
require_once dirname(__DIR__) . '/services/prestamos/models/PrestamoModel.php';

$model = new PrestamoModel();
$enviados = 0;

foreach ($model->prestamosParaRecordatorio() as $prestamo) {
    $dias = (int) $prestamo['dias_prestamo'];
    if ($model->yaSeEnvioRecordatorio((int) $prestamo['id'], $dias)) {
        continue;
    }

    $ok = MailService::recordatorioPrestamo(
        [
            'numero_carpeta' => $prestamo['numero_carpeta'],
            'correo_electronico' => $prestamo['correo_electronico'],
        ],
        $prestamo,
        $dias
    );

    if ($ok) {
        $model->marcarRecordatorioEnviado((int) $prestamo['id'], $dias);
        $enviados++;
        echo "Recordatorio enviado: carpeta {$prestamo['numero_carpeta']} ({$dias} días)\n";
    }
}

echo "Total enviados: {$enviados}\n";
