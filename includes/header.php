<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../app/Helpers/security.php';
send_security_headers(true);
require_once __DIR__ . '/../app/Helpers/csrf.php';
require_once __DIR__ . '/../views/components/ui_components.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#111827">
    <title>MCS Lotto Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/public/assets/css/app.css">
</head>
<body class="bg-gray-100 antialiased overflow-x-hidden">
<a href="#main-content" class="skip-link">Aller au contenu</a>
<div class="flex min-h-screen">
