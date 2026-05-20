<?php

require_once __DIR__ . '/../models/PrestamoModel.php';

class PrestamoController
{
    private PrestamoModel $model;

    public function __construct()
    {
        $this->model = new PrestamoModel();
    }

    public function handle(string $action): void
    {
        switch ($action) {
            case 'prestar':
                $this->prestar();
                break;
            case 'devolver':
                $this->devolver();
                break;
            case 'desarchivar':
                $this->desarchivar();
                break;
            case 'enviar-recordatorios':
                $this->enviarRecordatorios();
                break;
            default:
                jsonError('Acción no encontrada', 404);
        }
    }

    private function prestar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $numero = trim($input['numero_carpeta'] ?? '');
        $solicitante = trim($input['solicitante'] ?? '');
        $fecha = trim($input['fecha_prestamo'] ?? date('Y-m-d'));
        $motivo = trim($input['motivo'] ?? '');

        if ($numero === '' || $solicitante === '' || $motivo === '') {
            jsonError('Número de carpeta, solicitante y motivo son obligatorios');
        }

        require_once dirname(__DIR__, 2) . '/carpetas/models/CarpetaModel.php';
        $carpetaModel = new CarpetaModel();
        $carpeta = $carpetaModel->obtenerPorNumero($numero);

        if (!$carpeta) {
            jsonError('Carpeta no encontrada', 404);
        }
        if ($carpeta['estado'] === 'PRESTADA') {
            jsonError('La carpeta ya está prestada');
        }
        if ($carpeta['estado'] === 'DESARCHIVADA') {
            jsonError('La carpeta está desarchivada y no puede prestarse');
        }

        $id = $this->model->registrarPrestamo(
            (int) $carpeta['id'],
            $solicitante,
            $fecha,
            $motivo
        );

        jsonResponse([
            'success' => true,
            'prestamo_id' => $id,
            'message' => 'Préstamo registrado. Estado: Prestada',
        ], 201);
    }

    private function devolver(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $numero = trim($input['numero_carpeta'] ?? '');

        if ($numero === '') {
            jsonError('Número de carpeta es obligatorio');
        }

        require_once dirname(__DIR__, 2) . '/carpetas/models/CarpetaModel.php';
        $carpetaModel = new CarpetaModel();
        $carpeta = $carpetaModel->obtenerPorNumero($numero);

        if (!$carpeta) {
            jsonError('Carpeta no encontrada', 404);
        }
        if ($carpeta['estado'] === 'DESARCHIVADA') {
            jsonError('Carpeta desarchivada: no puede devolverse al Archivo Central');
        }
        if ($carpeta['estado'] !== 'PRESTADA') {
            jsonError('La carpeta no está en estado prestada');
        }

        try {
            $this->model->registrarDevolucion((int) $carpeta['id']);
        } catch (RuntimeException $e) {
            jsonError($e->getMessage());
        }

        jsonResponse([
            'success' => true,
            'message' => 'Carpeta devuelta. Estado: Archivo Central',
        ]);
    }

    private function desarchivar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $numero = trim($input['numero_carpeta'] ?? '');
        $solicitante = trim($input['solicitante'] ?? '');
        $fecha = trim($input['fecha_desarchivo'] ?? date('Y-m-d'));
        $motivo = trim($input['motivo'] ?? '');

        if ($numero === '' || $solicitante === '' || $motivo === '') {
            jsonError('Número de carpeta, solicitante y motivo son obligatorios');
        }

        require_once dirname(__DIR__, 2) . '/carpetas/models/CarpetaModel.php';
        $carpetaModel = new CarpetaModel();
        $carpeta = $carpetaModel->obtenerPorNumero($numero);

        if (!$carpeta) {
            jsonError('Carpeta no encontrada', 404);
        }
        if ($carpeta['estado'] === 'DESARCHIVADA') {
            jsonError('La carpeta ya está desarchivada');
        }
        if ($carpeta['estado'] === 'PRESTADA') {
            jsonError('Debe registrar la devolución antes de desarchivar');
        }

        try {
            $id = $this->model->registrarDesarchivo(
                (int) $carpeta['id'],
                $solicitante,
                $fecha,
                $motivo
            );
        } catch (RuntimeException $e) {
            jsonError($e->getMessage());
        }

        jsonResponse([
            'success' => true,
            'id' => $id,
            'message' => 'Desarchivamiento registrado. Estado: Desarchivada',
        ], 201);
    }

    private function enviarRecordatorios(): void
    {
        $enviados = 0;
        foreach ($this->model->prestamosParaRecordatorio() as $prestamo) {
            $dias = (int) $prestamo['dias_prestamo'];
            if ($this->model->yaSeEnvioRecordatorio((int) $prestamo['id'], $dias)) {
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
                $this->model->marcarRecordatorioEnviado((int) $prestamo['id'], $dias);
                $enviados++;
            }
        }

        jsonResponse([
            'success' => true,
            'enviados' => $enviados,
            'message' => "Recordatorios procesados: {$enviados}",
        ]);
    }
}
