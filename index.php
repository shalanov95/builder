<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/config_db.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/scr/MyQueryBuilder.php';
use scr\MyQueryBuilder;

$db = new MyQueryBuilder($confMysql);
$table = 'users';
$id=7;

// тестриуем Mysql
//$result = $db->select([])->from($table')->where('age','21','>')->limit(3)->execute();
//$data = ['name'=>'Petr', 'age' => 18, 'work_id' => 1, 'dep_id' => 1];
//$result = $db->insert($table, $data)->execute();
//$data = ['name'=>'Petr', 'age' => 19, 'work_id' => 1, 'dep_id' => 1];
//$result = $db->update($table, $data)->where('id', $id, '=')->execute();
//$result = $db->delete()->from($table)->where('id', $id, '=')->execute();
//$result = $db->select([])->from($table)->execute();
//$result = $db->select(['users.id', 'users.name as user_name', 'users.age', 'work.title', 'dep.name'])
//    ->from($table)
//    ->join($table,'work', 'work_id', 'id')
//    ->join('work','dep', 'id', 'work_id')
//    ->where('age','21','>')
//    ->limit(2)
//    ->execute();
//for ($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);

// тестриуем PosgresSQL

$db2 = new MyQueryBuilder($confPostgresql);
$table = 'users';
$result = $db2->select(['users.id', 'users.name as user_name', 'users.age', 'work.title', 'dep.name'])
    ->from($table)
    ->join($table,'work', 'work_id', 'id', 'LEFT OUTER JOIN')
    ->join('work','dep', 'id', 'work_id', 'LEFT OUTER JOIN')
    ->where('age','20','>')
    ->limit(2,1)
    ->execute();

//$result = $db2->select([])->from('users')->execute();
for ($data = []; $row = pg_fetch_assoc($result); $data[] = $row);

echo('<pre>');
var_dump($data);
echo('</pre>');
