<?php

require_once dirname(__DIR__, 2) . '/shared/bootstrap.php';
require_once __DIR__ . '/controllers/CatalogoController.php';

$action = $_GET['action'] ?? 'fiscalias';
(new CatalogoController())->handle($action);
