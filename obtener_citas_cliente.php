<?php
session_start();
include 'conexion.php';

// Verificamos que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

$id_logueado = $_SESSION['id_usuario'];

// 1. AUTO-ACTUALIZAR ESTADOS: Cambiar 'pendiente' a 'Realizada' si ya pasó la fecha/hora
$sql_update = "UPDATE citas_barberia 
               SET estado = 'Realizada' 
               WHERE estado = 'pendiente' 
               AND (fecha < CURDATE() OR (fecha = CURDATE() AND hora < CURTIME()))";
mysqli_query($conexion, $sql_update);

// 2. Traer citas del ÚLTIMO MES hasta el futuro
// Filtramos: fecha >= primer día del mes anterior (o simplemente 30 días atrás)
$sql = "SELECT id_cita, servicio, barbero, fecha, hora, estado 
        FROM citas_barberia 
        WHERE id_usuario = ? 
        AND fecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        ORDER BY fecha DESC, hora DESC";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_logueado);
mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result($stmt, $id_cita, $servicio, $barbero, $fecha, $hora, $estado);

$citas = [];
while (mysqli_stmt_fetch($stmt)) {
    $citas[] = [
        'id_cita' => $id_cita,
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