<?php
include 'conexion.php';

// Crear tabla de servicios
$sql_servicios = "CREATE TABLE IF NOT EXISTS servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    activo TINYINT(1) DEFAULT 1
)";

// Crear tabla de barberos
$sql_barberos = "CREATE TABLE IF NOT EXISTS barberos (
    id_barbero INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo TINYINT(1) DEFAULT 1
)";

if (mysqli_query($conexion, $sql_servicios) && mysqli_query($conexion, $sql_barberos)) {
    echo "Tablas creadas o ya existentes.<br>";
} else {
    die("Error creando tablas: " . mysqli_error($conexion));
}

// Poblado inicial de servicios si está vacía
$res = mysqli_query($conexion, "SELECT COUNT(*) as total FROM servicios");
$row = mysqli_fetch_assoc($res);
if ($row['total'] == 0) {
    $servicios_iniciales = [
        ['Corte Clásico', 'Cabello'],
        ['Corte Fade', 'Cabello'],
        ['Corte Infantil', 'Cabello'],
        ['Perfilado Barba', 'Barba'],
        ['Ritual Barba', 'Barba'],
        ['Combo Real', 'Especiales'],
        ['Limpieza Facial', 'Especiales']
    ];
    foreach ($servicios_iniciales as $s) {
        mysqli_query($conexion, "INSERT INTO servicios (nombre, categoria) VALUES ('$s[0]', '$s[1]')");
    }
    echo "Servicios iniciales insertados.<br>";
}

// Poblado inicial de barberos si está vacía
$res = mysqli_query($conexion, "SELECT COUNT(*) as total FROM barberos");
$row = mysqli_fetch_assoc($res);
if ($row['total'] == 0) {
    $barberos_iniciales = ['Jafeth', 'Junior'];
    foreach ($barberos_iniciales as $b) {
        mysqli_query($conexion, "INSERT INTO barberos (nombre) VALUES ('$b')");
    }
    echo "Barberos iniciales insertados.<br>";
}

echo "Proceso de base de datos completado.";
?>