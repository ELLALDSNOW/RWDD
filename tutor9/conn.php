<?php






$localhost = 'localhost';
$user = 'root';
$pass='';
$dbName = 'myaddressbook';

$dbConn = mysqli_connect($localhost, $user, $pass, $dbName);
if(mysqli_connect_errno()){
    die('<script>alert("Database connection failed");</script>');
}



echo "<script>alert('Successfully COnnected');</script>";















?>