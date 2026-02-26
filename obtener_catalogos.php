<?php
header('Content-Type: application/json');
include 'conexion.php';

$response = [
    'servicios' => [],
    'barberos' => []
];

// Obtener servicios
$res_servicios = mysqli_query($conexion, "SELECT * FROM servicios ORDER BY categoria, nombre");
while ($row = mysqli_fetch_assoc($res_servicios)) {
    $response['servicios'][] = $row;
}

// Obtener barberos
$res_barberos = mysqli_query($conexion, "SELECT * FROM barberos ORDER BY nombre");
while ($row = mysqli_fetch_assoc($res_barberos)) {
    $response['barberos'][] = $row;
}

echo json_encode($response);
?>