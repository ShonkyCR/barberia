<?php
include 'conexion.php';
session_start();

// Seguridad: Solo admin o dev
if (!isset($_SESSION['id_rol']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 3)) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

if (isset($_POST['id_usuario'])) {
    $id = $_POST['id_usuario'];

    // Eliminación física para limpiar la tabla por completo
    $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
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