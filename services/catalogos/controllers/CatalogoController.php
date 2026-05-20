<?php

require_once __DIR__ . '/../models/CatalogoModel.php';

class CatalogoController
{
    private CatalogoModel $model;

    public function __construct()
    {
        $this->model = new CatalogoModel();
    }

    public function handle(string $action): void
    {
        switch ($action) {
            case 'fiscalias':
                jsonResponse(['success' => true, 'data' => $this->model->listarFiscalias()]);
            case 'despachos':
                $fiscaliaId = isset($_GET['fiscalia_id']) ? (int) $_GET['fiscalia_id'] : null;
                jsonResponse(['success' => true, 'data' => $this->model->listarDespachos($fiscaliaId)]);
            case 'crear-fiscalia':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    jsonError('Método no permitido', 405);
                }
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $codigo = trim($input['codigo'] ?? '');
                $nombre = trim($input['nombre'] ?? '');
                if ($codigo === '' || $nombre === '') {
                    jsonError('Código y nombre son obligatorios');
                }
                $id = $this->model->crearFiscalia($codigo, $nombre);
                jsonResponse(['success' => true, 'id' => $id], 201);
            case 'crear-despacho':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    jsonError('Método no permitido', 405);
                }
                $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $fiscaliaId = (int) ($input['fiscalia_id'] ?? 0);
                $codigo = trim($input['codigo'] ?? '');
                $nombre = trim($input['nombre'] ?? '');
                if ($fiscaliaId <= 0 || $codigo === '' || $nombre === '') {
                    jsonError('Fiscalía, código y nombre son obligatorios');
                }
                $id = $this->model->crearDespacho($fiscaliaId, $codigo, $nombre);
                jsonResponse(['success' => true, 'id' => $id], 201);
            default:
                jsonError('Acción no encontrada', 404);
        }
    }
}
