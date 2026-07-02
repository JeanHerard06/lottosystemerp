<?php
require "config/database.php";

$password = password_hash("admin123", PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, username, password, role)
VALUES ('Admin Principal', 'admin', '$password', 'admin')";
$ps = $pdo->prepare($sql);
$ps->execute();