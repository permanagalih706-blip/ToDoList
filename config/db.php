<?php
session_start(); // Memulai session untuk login & notifikasi

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_todolist";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>