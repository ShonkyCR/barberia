<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
$email = mysqli_real_escape_string($conexion, $_POST['email']);
$telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
$password = $_POST['password'];

// 1. Actualización básica (Nombre, Email y Teléfono) con Sentencias Preparadas
$sql = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ? WHERE id_usuario = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $telefono, $id_usuario);

if (mysqli_stmt_execute($stmt)) {
    // 2. ¿El usuario quiere cambiar la contraseña?
    if (!empty($password)) {
        $passHash = password_hash($password, PASSWORD_DEFAULT);
        $sqlPass = "UPDATE usuarios SET password = ? WHERE id_usuario = ?";
        $stmtPass = mysqli_prepare($conexion, $sqlPass);
        mysqli_stmt_bind_param($stmtPass, "si", $passHash, $id_usuario);
        mysqli_stmt_execute($stmtPass);
        mysqli_stmt_close($stmtPass);
    }

    // Actualizamos el nombre en la sesión
    $_SESSION['nombre_usuario'] = $nombre;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
}
mysqli_stmt_close($stmt);
?>