<?php
session_start();
include 'conexion.php';

// Si se accede directamente por GET (o tras redirección de seguridad sin POST), volver al login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit();
}

// Capturamos y sanitizamos datos
$cedula_ingresada = trim($_POST['usuario'] ?? '');
$password_escrita = $_POST['password'] ?? '';

if (empty($cedula_ingresada) || empty($password_escrita)) {
    echo "<script>alert('Por favor complete todos los campos'); window.location.href='login.html';</script>";
    exit();
}

try {
    // Consulta con Sentencias Preparadas
    $sql = "SELECT id_usuario, nombre, password, id_rol FROM usuarios WHERE cedula = ?";
    $stmt = mysqli_prepare($conexion, $sql);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param($stmt, "s", $cedula_ingresada);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($resultado && ($row = mysqli_fetch_assoc($resultado))) {
        // Verificación de contraseña
        if (password_verify($password_escrita, $row['password'])) {

            // Creamos la sesión
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre_usuario'] = $row['nombre'];
            $_SESSION['id_rol'] = (int) $row['id_rol'];

            // REDIRECCIÓN AUTOMÁTICA SEGÚN ROL
            if ($_SESSION['id_rol'] === 1 || $_SESSION['id_rol'] === 3) {
                header("Location: admin.php");
            } elseif ($_SESSION['id_rol'] === 4) {
                header("Location: barberos.php");
            } else {
                header("Location: clientes.php");
            }
            exit(); 

        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='login.html';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Usuario o cédula no encontrada'); window.location.href='login.html';</script>";
        exit();
    }
} catch (Throwable $e) {
    die("<div style='font-family:sans-serif; padding:20px; border:1px solid #e74c3c; background:#fadbd8; border-radius:8px; max-width:600px; margin:40px auto; color:#78281f;'>
        <h3>Error al procesar el inicio de sesión</h3>
        <p><strong>Detalle:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <p><a href='login.html' style='color:#78281f; font-weight:bold;'>&larr; Volver a intentar</a></p>
    </div>");
}
?>