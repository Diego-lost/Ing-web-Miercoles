<?php

require_once dirname(__DIR__, 2) . '/shared/bootstrap.php';
require_once __DIR__ . '/controllers/CarpetaController.php';

$action = $_GET['action'] ?? 'consultar';
(new CarpetaController())->handle($action);
