<?php
include 'conexion.php';

$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// 1. AUTO-ACTUALIZAR ESTADOS (Igual que en clientes para consistencia)
$sql_update = "UPDATE citas_barberia 
               SET estado = 'Realizada' 
               WHERE estado = 'pendiente' 
               AND (fecha < CURDATE() OR (fecha = CURDATE() AND hora < CURTIME()))";
mysqli_query($conexion, $sql_update);

// Consulta base
$sql = "SELECT c.id_cita, u.nombre AS cliente, c.servicio, c.barbero, c.fecha, c.hora, c.estado 
        FROM citas_barberia c 
        JOIN usuarios u ON c.id_usuario = u.id_usuario";

$stmt = null;

if ($fecha_filtro != '') {
    $sql .= " WHERE c.fecha = ? ORDER BY c.hora ASC";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $fecha_filtro);
} else {
    // Por defecto mostramos citas de hoy en adelante para el admin (o todas?)
    // Vamos a mostrar todas las de hoy en adelante, o las pendientes pasadas que ya se marcaron como Realizada
    $sql .= " WHERE (c.fecha >= CURDATE()) ORDER BY c.fecha ASC, c.hora ASC";
    $stmt = mysqli_prepare($conexion, $sql);
}

if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $id_cita, $cliente, $servicio, $barbero, $fecha, $hora, $estado);

$citas = [];
while (mysqli_stmt_fetch($stmt)) {
    $citas[] = [
        'id_cita' => $id_cita,
        'cliente' => $cliente,
        'servicio' => $servicio,
        'barbero' => $barbero,
        'fecha' => $fecha,
        'hora' => $hora,
        'estado' => $estado
    ];
}

mysqli_stmt_close($stmt);

header('Content-Type: application/json');
echo json_encode($citas);
?>