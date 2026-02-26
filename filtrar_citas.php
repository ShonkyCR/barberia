<?php
include 'conexion.php';

header('Content-Type: application/json');

if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];

    $sql = "SELECT c.id_cita, u.nombre AS cliente, c.servicio, c.barbero, c.fecha, 
            c.hora, c.estado 
            FROM citas_barberia c 
            JOIN usuarios u ON c.id_usuario = u.id_usuario 
            WHERE c.fecha = ?";

    $stmt = mysqli_prepare($conexion, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $fecha);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        $citas = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $citas[] = $fila;
        }

        echo json_encode($citas);
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Error en la consulta: " . mysqli_error($conexion)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se proporcionó una fecha"]);
}
?>