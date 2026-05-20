<?php

require_once dirname(__DIR__, 2) . '/shared/bootstrap.php';
require_once dirname(__DIR__) . '/carpetas/models/CarpetaModel.php';
require_once __DIR__ . '/models/PrestamoModel.php';
require_once __DIR__ . '/controllers/PrestamoController.php';

$action = $_GET['action'] ?? '';
if ($action === '') {
    jsonError('Acción requerida', 400);
}
(new PrestamoController())->handle($action);
