<?php
// 1. Limpiamos cualquier salida previa para que no rompa el JSON
ob_clean();
header('Content-Type: application/json');

session_start();
include 'conexion.php';

$response = ['success' => false];

try {
    // 2. Verificamos sesión
    if (!isset($_SESSION['id_usuario'])) {
        $response['error'] = 'Sesión expirada';
        echo json_encode($response);
        exit;
    }

    // 3. Verificamos que llegue el ID
    if (!isset($_POST['id_cita'])) {
        $response['error'] = 'No se recibió el ID de la cita';
        echo json_encode($response);
        exit;
    }

    $id_cita = mysqli_real_escape_string($conexion, $_POST['id_cita']);
    $id_usuario = $_SESSION['id_usuario'];

    // 4. Ejecutamos la actualización con Sentencias Preparadas
    $sql = "DELETE FROM citas_barberia WHERE id_cita = ? AND id_usuario = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_cita, $id_usuario);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $response['success'] = true;
        } else {
            $response['error'] = 'No se encontró la cita o no tienes permiso';
        }
    } else {
        $response['error'] = mysqli_error($conexion);
    }
    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// 5. Enviamos la respuesta final
echo json_encode($response);
?>