<?php
date_default_timezone_set('America/Costa_Rica');
$host = "sql208.infinityfree.com";
$user = "if0_41731744";
$pass = "Shonkydr19"; // Contraseña de InfinityFree
$db = "if0_41731744_barberia_db";

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
    email_barbero VARCHAR(255),
    activo TINYINT(1) DEFAULT 1
)");

mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS citas_barberia (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    servicio VARCHAR(100),
    barbero VARCHAR(100),
    fecha DATE,
    hora TIME,
    estado VARCHAR(20),
    id_evento_google VARCHAR(255)
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