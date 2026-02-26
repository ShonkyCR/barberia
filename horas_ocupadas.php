<?php
include 'conexion.php';

$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$barbero = isset($_GET['barbero']) ? mysqli_real_escape_string($conexion, $_GET['barbero']) : '';

if ($fecha != '') {

    $sql = "SELECT hora FROM citas_barberia 
            WHERE fecha = '$fecha' 
            AND barbero = '$barbero' 
            AND LOWER(estado) != 'cancelado'";

    $resultado = mysqli_query($conexion, $sql);
    $horas = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $horas[] = $fila['hora'];
    }

    header('Content-Type: application/json');
    echo json_encode($horas);
}
?>