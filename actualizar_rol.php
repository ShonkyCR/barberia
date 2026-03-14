<?php
header('Content-Type: application/json');
include 'conexion.php';
session_start();

if (!isset($_SESSION['id_rol']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 3)) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

$id_usuario = $_POST['id_usuario'] ?? '';
$nuevo_rol = $_POST['id_rol'] ?? '';

if (empty($id_usuario) || empty($nuevo_rol)) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

// Evitar que el admin se quite el rol a sí mismo por accidente si es el único
if ($id_usuario == $_SESSION['id_usuario'] && $nuevo_rol != 1) {
    echo json_encode(["success" => false, "message" => "No puedes quitarte el rol de administrador a ti mismo."]);
    exit;
}

$sql = "UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ii", $nuevo_rol, $id_usuario);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => mysqli_error($conexion)]);
}
mysqli_stmt_close($stmt);
?>