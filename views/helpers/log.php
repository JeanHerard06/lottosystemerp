<?php

function saveLog($pdo,$user_id,$action,$description){

    $stmt=$pdo->prepare("
INSERT INTO audit_logs(
user_id,
action_type,
description
)
VALUES(?,?,?)
");

    $stmt->execute([
        $user_id,
        $action,
        $description
    ]);

}