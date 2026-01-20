<?php

$localhost = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'task_manager';


$dbConn = mysqli_connect($localhost, $user, $pass, $dbName);


if (!$dbConn) {
    die("Connection failed: " . mysqli_connect_error());
}
















?>