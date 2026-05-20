<?php

$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['app']['timezone']);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/HistorialService.php';
require_once __DIR__ . '/MailService.php';

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $message, int $code = 400): void
{
    jsonResponse(['success' => false, 'message' => $message], $code);
}
