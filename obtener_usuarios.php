<?php
include 'conexion.php';
session_start();

// Seguridad: Solo admin o dev
if (!isset($_SESSION['id_rol']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 3)) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}


$sql = "SELECT u.id_usuario, u.nombre, u.cedula, u.email, u.telefono, u.estado_usuario, u.id_rol 
        FROM usuarios u 
        WHERE u.id_rol != 3 
        AND u.cedula NOT IN (SELECT b.cedula FROM barberos b WHERE b.cedula IS NOT NULL)
        ORDER BY u.id_rol ASC, u.nombre ASC";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {
    die(json_encode(["error" => mysqli_error($conexion)]));
}

$usuarios = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $usuarios[] = $row;
}


header('Content-Type: application/json');
echo json_encode($usuarios);
?>