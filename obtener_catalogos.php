<?php
header('Content-Type: application/json');
include 'conexion.php';
session_start();

// Seguridad: Cualquier usuario logueado puede ver catálogos
if (!isset($_SESSION['id_rol'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$response = [
    'servicios' => [],
    'barberos' => []
];

// Obtener servicios
$res_servicios = mysqli_query($conexion, "SELECT * FROM servicios ORDER BY categoria, nombre");
while ($row = mysqli_fetch_assoc($res_servicios)) {
    $response['servicios'][] = $row;
}

// Obtener barberos con su información de usuario si existe (para el rol)
$sql_barberos = "SELECT b.*, u.id_rol, u.id_usuario 
                 FROM barberos b 
                 LEFT JOIN usuarios u ON b.cedula = u.cedula AND u.id_rol != 3
                 ORDER BY b.nombre";
$res_barberos = mysqli_query($conexion, $sql_barberos);
while ($row = mysqli_fetch_assoc($res_barberos)) {
    $response['barberos'][] = $row;
}

echo json_encode($response);
?>