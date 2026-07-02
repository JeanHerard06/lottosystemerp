<?php
require_once __DIR__ . '/auth.php'; api_user($pdo);
$stmt = $pdo->query('SELECT id, draw_name, first_number, second_number, third_number, draw_date, status FROM tirages ORDER BY draw_date DESC, id DESC LIMIT 50');
api_response(true, ['data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
