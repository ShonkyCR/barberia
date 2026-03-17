<?php
header('Content-Type: application/json');
session_start();
include 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([]);
    exit;
}

$id_rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre_usuario'];

// 1. Verificar si el usuario tiene rol de barbero (4) o dev (3)
if ($id_rol != 4 && $id_rol != 3) {
    echo json_encode(['no_es_barbero' => true]);
    exit;
}

// Para filtrar la agenda, seguimos necesitando el nombre tal cual está en la tabla barberos
// Lo buscamos por cédula (que es el vínculo más seguro)
$sql_b = "SELECT nombre FROM barberos WHERE cedula = (SELECT cedula FROM usuarios WHERE id_usuario = ?)";
$stmt_b = mysqli_prepare($conexion, $sql_b);
mysqli_stmt_bind_param($stmt_b, "i", $id_usuario);
mysqli_stmt_execute($stmt_b);
$res_b = mysqli_stmt_get_result($stmt_b);
$row_b = mysqli_fetch_assoc($res_b);

if (!$row_b) {
    echo json_encode(['no_es_barbero' => true]); // Si no está en la tabla barberos, no mostramos agenda
    exit;
}

$nombre_barbero_db = $row_b['nombre'];

// 2. Obtener las citas pendientes de este barbero (de hoy en adelante)
$sql = "SELECT c.id_cita, u.nombre AS cliente, u.telefono, c.servicio, c.fecha, c.hora, c.estado 
        FROM citas_barberia c 
        JOIN usuarios u ON c.id_usuario = u.id_usuario 
        WHERE c.barbero = ? 
        AND c.fecha >= CURDATE()
        ORDER BY c.fecha ASC, c.hora ASC";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $nombre_barbero_db);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$agenda = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $agenda[] = $row;
}

mysqli_stmt_close($stmt);
echo json_encode($agenda);
?>
