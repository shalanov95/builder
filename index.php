<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/config_db.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/scr/MyQueryBuilder.php';
use scr\MyQueryBuilder;

$db = new MyQueryBuilder($confMysql);
$result = $db->select([])->from('user')->where('age','21','>')->limit(3)->execute();

for ($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);
echo('<pre>');
var_dump($data);
echo('</pre>');
