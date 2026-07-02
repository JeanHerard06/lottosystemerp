<?php
session_start(); require "../../config/database.php"; require_once "../../app/Helpers/security.php"; require_once "../../app/Helpers/csrf.php"; require_once "../../includes/auth.php"; require_once "../../app/Helpers/permissions.php";
require_permission($pdo,'roles.manage'); require_post(); verify_csrf();
$name=input_string('name',100); $slug=input_string('slug',100); if (in_array($slug, protected_role_slugs(), true)) { http_response_code(403); die('Le rôle super_admin est protégé.'); } $permIds=filter_permission_ids($pdo, $_POST['permission_ids']??[]);
$pdo->beginTransaction(); $stmt=$pdo->prepare("INSERT INTO roles(name,slug) VALUES(?,?)"); $stmt->execute([$name,$slug]); $roleId=(int)$pdo->lastInsertId(); $stmt=$pdo->prepare("INSERT INTO role_permissions(role_id,permission_id) VALUES(?,?)"); foreach($permIds as $pid){$stmt->execute([$roleId,$pid]);} $pdo->commit(); redirect('../../views/roles/index.php');
