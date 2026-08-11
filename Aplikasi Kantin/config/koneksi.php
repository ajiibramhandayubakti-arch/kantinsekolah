<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_kantin";

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connnect_error());
}
?>