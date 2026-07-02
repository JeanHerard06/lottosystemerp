<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
if (!has_permission($pdo, 'settings.manage')) { die('Accès refusé'); }
$dir = __DIR__ . '/../../storage/backups'; if(!is_dir($dir)) mkdir($dir,0775,true);
$file = $dir . '/backup_' . date('Ymd_His') . '.sql';
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN); $out="";
foreach($tables as $table){
    $create=$pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC); $out .= "\nDROP TABLE IF EXISTS `$table`;\n" . $create['Create Table'] . ";\n\n";
    $rows=$pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row){$cols=array_map(fn($c)=>"`$c`",array_keys($row)); $vals=array_map(fn($v)=>$v===null?'NULL':$pdo->quote($v),array_values($row)); $out.="INSERT INTO `$table` (".implode(',',$cols).") VALUES (".implode(',',$vals).");\n";}
}
file_put_contents($file,$out);
header('Location: /views/settings/backups.php');
