<?php
date_default_timezone_set('America/Costa_Rica');

// Reporte de errores visible para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Detección automática del entorno: Local (XAMPP) vs Remoto (InfinityFree)
$is_local = (
    php_sapi_name() === 'cli' ||
    in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1']) ||
    in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']) ||
    strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost:') === 0 ||
    strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1:') === 0
);

if ($is_local) {
    // Entorno local: XAMPP
    $host = "127.0.0.1";
    $user = "root";
    $pass = "123456";
    $db   = "barberia_db";
} else {
    // Entorno remoto: InfinityFree
    $host = "sql308.infinityfree.com";
    $user = "if0_43159004";
    $pass = "Thiana1998";
    $db   = "if0_43159004_barberia_db";
}

try {
    $conexion = mysqli_connect($host, $user, $pass, $db);
} catch (Throwable $e) {
    // Si en local fallara con '123456', intentar con contraseña vacía común de XAMPP
    if ($is_local) {
        try {
            $pass = "";
            $conexion = mysqli_connect($host, $user, $pass, $db);
        } catch (Throwable $e2) {
            $conexion = false;
        }
    }
    
    if (!$conexion) {
        $env = $is_local ? "Local (XAMPP)" : "Remoto (InfinityFree)";
        die("<div style='font-family:sans-serif; padding:20px; border:1px solid #e74c3c; background:#fadbd8; border-radius:8px; max-width:600px; margin:40px auto; color:#78281f;'>
            <h3>Error al conectar con la base de datos [$env]</h3>
            <p><strong>Detalle:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <hr style='border:0; border-top:1px solid #e74c3c; margin:15px 0;'>
            <p style='font-size:14px;'><strong>Configuración actual:</strong><br>
            - Host: <code>$host</code><br>
            - Usuario: <code>$user</code><br>
            - Base de datos: <code>$db</code></p>
        </div>");
    }
}

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");

// --- AUTO-CREACIÓN DE TABLAS (protegida para evitar Error 500) ---
try {
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

    mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS usuarios (
        id_usuario INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        cedula VARCHAR(20) NOT NULL,
        usuario VARCHAR(100),
        email VARCHAR(255),
        telefono VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        estado_usuario VARCHAR(20) DEFAULT 'activo',
        id_rol INT DEFAULT 2,
        UNIQUE KEY uq_cedula (cedula)
    )");

    // Comprobar columna de forma segura sin acceder a information_schema
    $col_check = mysqli_query($conexion, "SHOW COLUMNS FROM barberos LIKE 'cedula'");
    if ($col_check && mysqli_num_rows($col_check) === 0) {
        @mysqli_query($conexion, "ALTER TABLE barberos ADD COLUMN cedula VARCHAR(20) NULL");
    }

    // Poblado inicial
    $check_s = mysqli_query($conexion, "SELECT 1 FROM servicios LIMIT 1");
    if ($check_s && mysqli_num_rows($check_s) == 0) {
        mysqli_query($conexion, "INSERT INTO servicios (nombre, categoria) VALUES 
            ('Corte Clásico', 'Cabello'), ('Corte Fade', 'Cabello'), ('Corte Infantil', 'Cabello'),
            ('Perfilado Barba', 'Barba'), ('Ritual Barba', 'Barba'),
            ('Combo Real', 'Especiales'), ('Limpieza Facial', 'Especiales')");
    }

    $check_b = mysqli_query($conexion, "SELECT 1 FROM barberos LIMIT 1");
    if ($check_b && mysqli_num_rows($check_b) == 0) {
        mysqli_query($conexion, "INSERT INTO barberos (nombre) VALUES ('Jafeth'), ('Junior')");
    }
} catch (Throwable $e) {
    error_log("Aviso de esquema: " . $e->getMessage());
}
?>