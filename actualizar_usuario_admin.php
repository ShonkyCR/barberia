<?php
header('Content-Type: application/json');
include 'conexion.php';
session_start();

// Seguridad: Solo el admin puede hacer esto
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$id = $_POST['id_usuario'];
$pass = $_POST['password'];
$telefono = $_POST['telefono'];

$passHash = password_hash($pass, PASSWORD_DEFAULT);

$sql = "UPDATE usuarios SET password = ?, telefono = ? WHERE id_usuario = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ssi", $passHash, $telefono, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
}
mysqli_stmt_close($stmt);
?>