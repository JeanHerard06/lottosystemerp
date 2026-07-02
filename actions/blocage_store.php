<?php

require "../config/database.php";

$stmt=$pdo->prepare("
INSERT INTO blocages(
number_value,
motif
)
VALUES(?,?)
");

$stmt->execute([
    $_POST['number_value'],
    $_POST['motif']
]);

header("Location:
../views/blocages/index.php");