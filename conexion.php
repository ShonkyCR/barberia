<?php
date_default_timezone_set('America/Costa_Rica');
$host = "127.0.0.1";
$user = "root";
$pass = "123456";
$db = "barberia_db";

$conexion = mysqli_connect($host, $user, $pass, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");

// --- AUTO-CREACIÓN DE TABLAS ---
mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    activo TINYINT(1) DEFAULT 1
)");

mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS barberos (
    id_barbero INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo TINYINT(1) DEFAULT 1
)");

// Poblado inicial
$check_s = mysqli_query($conexion, "SELECT 1 FROM servicios LIMIT 1");
if (mysqli_num_rows($check_s) == 0) {
    mysqli_query($conexion, "INSERT INTO servicios (nombre, categoria) VALUES 
        ('Corte Clásico', 'Cabello'), ('Corte Fade', 'Cabello'), ('Corte Infantil', 'Cabello'),
        ('Perfilado Barba', 'Barba'), ('Ritual Barba', 'Barba'),
        ('Combo Real', 'Especiales'), ('Limpieza Facial', 'Especiales')");
}

$check_b = mysqli_query($conexion, "SELECT 1 FROM barberos LIMIT 1");
if (mysqli_num_rows($check_b) == 0) {
    mysqli_query($conexion, "INSERT INTO barberos (nombre) VALUES ('Jafeth'), ('Junior')");
}
?>