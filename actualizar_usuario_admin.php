<?php
header('Content-Type: application/json');
include 'conexion.php';
session_start();

// Seguridad: Solo el admin o dev puede hacer esto
if (!isset($_SESSION['id_rol']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 3)) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$id = $_POST['id_usuario'];
$nombre = $_POST['nombre'];
$cedula = $_POST['cedula'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$pass = $_POST['password'] ?? '';

if (!empty($pass)) {
    // Si se envió una nueva contraseña, la hasheamos y actualizamos todo
    $passHash = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "UPDATE usuarios SET nombre = ?, cedula = ?, email = ?, telefono = ?, password = ? WHERE id_usuario = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "sssssi", $nombre, $cedula, $email, $telefono, $passHash, $id);
} else {
    // Si la contraseña está vacía, no la tocamos
    $sql = "UPDATE usuarios SET nombre = ?, cedula = ?, email = ?, telefono = ? WHERE id_usuario = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $cedula, $email, $telefono, $id);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
}
mysqli_stmt_close($stmt);
?>