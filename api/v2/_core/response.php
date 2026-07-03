<?php
function api_json($data = [], int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_success($data = [], string $message = 'OK'): void {
    api_json(['success' => true, 'message' => $message, 'data' => $data]);
}

function api_error(string $message, int $status = 400, array $errors = []): void {
    api_json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
}
