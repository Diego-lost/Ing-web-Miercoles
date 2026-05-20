<?php

require_once __DIR__ . '/../models/CarpetaModel.php';

class CarpetaController
{
    private CarpetaModel $model;

    public function __construct()
    {
        $this->model = new CarpetaModel();
    }

    public function handle(string $action): void
    {
        switch ($action) {
            case 'crear':
                $this->crear();
                break;
            case 'consultar':
                $this->consultar();
                break;
            case 'detalle':
                $this->detalle();
                break;
            case 'historial':
                $this->historial();
                break;
            default:
                jsonError('Acción no encontrada', 404);
        }
    }

    private function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $required = [
            'numero_carpeta', 'imputado', 'agraviado', 'delito',
            'fiscalia_id', 'despacho_id', 'fiscal_responsable',
            'folios', 'estado_correo', 'correo_electronico',
        ];
        foreach ($required as $field) {
            if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
                jsonError("El campo {$field} es obligatorio");
            }
        }

        if ($this->model->existeNumero(trim($input['numero_carpeta']))) {
            jsonError('Ya existe una carpeta con ese número');
        }

        $correo = trim($input['correo_electronico']);
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            jsonError('El correo electrónico no es válido');
        }

        $id = $this->model->crear([
            'numero_carpeta' => trim($input['numero_carpeta']),
            'imputado' => trim($input['imputado']),
            'agraviado' => trim($input['agraviado']),
            'delito' => trim($input['delito']),
            'fiscalia_id' => (int) $input['fiscalia_id'],
            'despacho_id' => (int) $input['despacho_id'],
            'fiscal_responsable' => trim($input['fiscal_responsable']),
            'folios' => (int) $input['folios'],
            'estado_correo' => trim($input['estado_correo']),
            'correo_electronico' => $correo,
        ]);

        $carpeta = $this->model->obtenerPorId($id);
        $correoEnviado = $carpeta
            ? MailService::notificacionRegistroCarpeta($carpeta)
            : false;

        $message = 'Carpeta registrada en Archivo Central';
        if ($correoEnviado) {
            $message .= '. Se envió una notificación a ' . $correo;
        } else {
            $message .= '. No se pudo enviar la notificación por correo (revise SMTP en el servidor)';
        }

        jsonResponse([
            'success' => true,
            'id' => $id,
            'message' => $message,
            'correo_enviado' => $correoEnviado,
            'data' => $carpeta,
        ], 201);
    }

    private function consultar(): void
    {
        $filtros = [
            'numero_carpeta' => $_GET['numero_carpeta'] ?? '',
            'imputado' => $_GET['imputado'] ?? '',
            'fiscalia_id' => $_GET['fiscalia_id'] ?? '',
            'despacho_id' => $_GET['despacho_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'delito' => $_GET['delito'] ?? '',
        ];

        $data = $this->model->buscar($filtros);
        foreach ($data as &$row) {
            $row['alerta_prestamo'] = $this->calcularAlerta($row);
            $row['estado_label'] = $this->estadoLabel($row['estado']);
        }
        unset($row);

        jsonResponse(['success' => true, 'data' => $data]);
    }

    private function detalle(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            jsonError('ID inválido');
        }
        $carpeta = $this->model->obtenerPorId($id);
        if (!$carpeta) {
            jsonError('Carpeta no encontrada', 404);
        }
        $carpeta['alerta_prestamo'] = $this->calcularAlerta($carpeta);
        $carpeta['estado_label'] = $this->estadoLabel($carpeta['estado']);
        jsonResponse(['success' => true, 'data' => $carpeta]);
    }

    private function historial(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            jsonError('ID inválido');
        }
        jsonResponse([
            'success' => true,
            'data' => HistorialService::porCarpeta($id),
        ]);
    }

    private function calcularAlerta(array $row): ?array
    {
        if ($row['estado'] !== 'PRESTADA' || empty($row['dias_prestamo'])) {
            return null;
        }
        $dias = (int) $row['dias_prestamo'];
        if ($dias <= 3) {
            return ['dias' => $dias, 'color' => 'verde', 'nivel' => 'normal'];
        }
        if ($dias <= 5) {
            return ['dias' => $dias, 'color' => 'amarillo', 'nivel' => 'atencion'];
        }
        return ['dias' => $dias, 'color' => 'rojo', 'nivel' => 'urgente'];
    }

    private function estadoLabel(string $estado): string
    {
        return match ($estado) {
            'ARCHIVO_CENTRAL' => 'Archivo Central',
            'PRESTADA' => 'Prestada',
            'DESARCHIVADA' => 'Desarchivada',
            default => $estado,
        };
    }
}
