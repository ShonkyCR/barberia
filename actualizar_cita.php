<?php
// Reportar cualquier error de PHP para que no muera en silencio
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include 'conexion.php';

try {
    // Verificamos que lleguen los datos básicos
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(["status" => "error", "message" => "No se recibió el ID de la cita"]);
        exit;
    }

    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    $servicio = mysqli_real_escape_string($conexion, $_POST['servicio']);
    $barbero = mysqli_real_escape_string($conexion, $_POST['barbero']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora = mysqli_real_escape_string($conexion, $_POST['hora']);

    // 4. Actualización con Sentencias Preparadas
    $sql = "UPDATE citas_barberia SET servicio = ?, barbero = ?, fecha = ?, hora = ? WHERE id_cita = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $servicio, $barbero, $fecha, $hora, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
    }
    mysqli_stmt_close($stmt); // Close the statement

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>