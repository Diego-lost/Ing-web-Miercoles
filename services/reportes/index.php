<?php

require_once dirname(__DIR__, 2) . '/shared/bootstrap.php';
require_once dirname(__DIR__) . '/carpetas/models/CarpetaModel.php';
require_once dirname(__DIR__) . '/prestamos/models/PrestamoModel.php';
require_once __DIR__ . '/controllers/ReporteController.php';

$action = $_GET['action'] ?? 'prestadas';
(new ReporteController())->handle($action);
