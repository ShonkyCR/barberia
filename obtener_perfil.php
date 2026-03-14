<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT nombre, cedula, email, telefono FROM usuarios WHERE id_usuario = '$id_usuario'";
$res = mysqli_query($conexion, $sql);
$user = mysqli_fetch_assoc($res);

echo json_encode($user);
?>