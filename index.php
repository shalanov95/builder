<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/config_db.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/scr/MyQueryBuilder.php';
use scr\MyQueryBuilder;

$db = new MyQueryBuilder($confMysql);
$table = 'user';
$id=7;

//$result = $db->select([])->from('user')->where('age','21','>')->limit(3)->execute();

//$data = ['name'=>'Petr', 'age' => 18, 'work_id' => 1, 'dep_id' => 1];
//$result = $db->insert($table, $data)->execute();

//$data = ['name'=>'Petr', 'age' => 19, 'work_id' => 1, 'dep_id' => 1];
//$result = $db->update($table, $data)->where('id', $id, '=')->execute();

//$result = $db->delete()->from($table)->where('id', $id, '=')->execute();

$result = $db->select([])->from('user')->execute();
for ($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);
echo('<pre>');
var_dump($data);
echo('</pre>');
