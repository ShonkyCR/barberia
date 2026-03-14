<?php
include 'conexion.php';

// Verificamos que recibimos un ID por la URL
if (isset($_POST['id_cita'])) {
    $id = $_POST['id_cita'];

    // --- INTEGRACIÓN GOOGLE CALENDAR ---
    try {
        $sql_g = "SELECT id_evento_google, barbero FROM citas_barberia WHERE id_cita = ?";
        $stmt_g = mysqli_prepare($conexion, $sql_g);
        mysqli_stmt_bind_param($stmt_g, "i", $id);
        mysqli_stmt_execute($stmt_g);
        $res_g = mysqli_stmt_get_result($stmt_g);
        $row_g = mysqli_fetch_assoc($res_g);

        if ($row_g && !empty($row_g['id_evento_google'])) {
            include_once 'google_calendar_helper.php';
            $helper = new GoogleCalendarHelper();

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
        error_log("Error al eliminar cita: " . $e->getMessage());
    }
    // ------------------------------------

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