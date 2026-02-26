<?php
include 'conexion.php';

// Verificamos que recibimos un ID por la URL
if (isset($_POST['id_cita'])) {
    $id = $_POST['id_cita'];

    $sql = "DELETE FROM citas_barberia WHERE id_cita = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($conexion);
?>