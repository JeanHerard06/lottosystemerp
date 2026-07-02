<?php

require "../config/database.php";

$stmt = $pdo->prepare("
INSERT INTO limites(
number_value,
max_amount
)
VALUES(?,?)
");

$stmt->execute([
    $_POST['number_value'],
    $_POST['max_amount']
]);

header("Location: ../views/limites/index.php");