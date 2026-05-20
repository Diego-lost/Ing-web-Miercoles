<?php

require_once dirname(__DIR__, 2) . '/prestamos/models/PrestamoModel.php';

class ReporteController
{
    private PrestamoModel $model;

    public function __construct()
    {
        $this->model = new PrestamoModel();
    }

    public function handle(string $action): void
    {
        switch ($action) {
            case 'prestadas':
                $this->reportePrestadas();
                break;
            case 'devueltas':
                $this->reporteDevueltas();
                break;
            default:
                jsonError('Acción no encontrada', 404);
        }
    }

    private function reportePrestadas(): void
    {
        $data = $this->model->listarPrestados();
        foreach ($data as &$row) {
            $dias = (int) ($row['dias_prestamo'] ?? 0);
            if ($dias <= 3) {
                $row['alerta'] = ['color' => 'verde', 'dias' => $dias, 'texto' => 'Dentro de plazo (≤3 días)'];
            } elseif ($dias <= 5) {
                $row['alerta'] = ['color' => 'amarillo', 'dias' => $dias, 'texto' => 'Atención (4-5 días)'];
            } else {
                $row['alerta'] = ['color' => 'rojo', 'dias' => $dias, 'texto' => 'Urgente devolución (>5 días)'];
            }
            if ($dias >= 10) {
                $row['alerta']['texto'] = 'Crítico (≥10 días)';
            }
        }
        unset($row);

        jsonResponse(['success' => true, 'data' => $data]);
    }

    private function reporteDevueltas(): void
    {
        jsonResponse(['success' => true, 'data' => $this->model->listarDevueltos()]);
    }
}
