<?php
include 'conexion.php';


$sql = "SELECT id_usuario, nombre, email, telefono, estado_usuario FROM usuarios WHERE id_rol = 2";
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