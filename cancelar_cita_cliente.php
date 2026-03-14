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

    // --- INTEGRACIÓN GOOGLE CALENDAR ---
    try {
        // Obtener ID del evento y barbero antes de borrar la cita
        $sql_g = "SELECT id_evento_google, barbero FROM citas_barberia WHERE id_cita = ? AND id_usuario = ?";
        $stmt_g = mysqli_prepare($conexion, $sql_g);
        mysqli_stmt_bind_param($stmt_g, "ii", $id_cita, $id_usuario);
        mysqli_stmt_execute($stmt_g);
        $res_g = mysqli_stmt_get_result($stmt_g);
        $row_g = mysqli_fetch_assoc($res_g);

        if ($row_g && !empty($row_g['id_evento_google'])) {
            include_once 'google_calendar_helper.php';
            $helper = new GoogleCalendarHelper();

            // Obtener email del barbero
            $sql_email = "SELECT email_barbero FROM barberos WHERE nombre = ?";
            $stmt_e = mysqli_prepare($conexion, $sql_email);
            mysqli_stmt_bind_param($stmt_e, "s", $row_g['barbero']);
            mysqli_stmt_execute($stmt_e);
            $res_email = mysqli_stmt_get_result($stmt_e);
            $row_email = mysqli_fetch_assoc($res_email);

            $calendarId = ($row_email && !empty($row_email['email_barbero'])) ? $row_email['email_barbero'] : 'primary';

            $result = $helper->eliminarEvento($calendarId, $row_g['id_evento_google']);
        }
    } catch (Exception $e) {
        error_log("Error al eliminar: " . $e->getMessage());
    }
    // ------------------------------------

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