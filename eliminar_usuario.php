<?php
include 'conexion.php';

if (isset($_POST['id_usuario'])) {
    $id = $_POST['id_usuario'];

    // En lugar de borrar (delete), desactivamos (update estado) para no perder historial
    $sql = "UPDATE usuarios SET estado_usuario = 'inactivo' WHERE id_usuario = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "error" => "ID no proporcionado"]);
}
?>