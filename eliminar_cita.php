<?php
ob_start();
session_start();
include 'conexion.php';

header('Content-Type: application/json');

if (isset($_POST['id_cita'])) {
    $id = $_POST['id_cita'];

    // 0. Seguridad: Verificar sesión de admin/dev para estar seguros
    if (!isset($_SESSION['id_rol']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 3)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'No autorizado']);
        exit;
    }

    // --- INTEGRACIÓN GOOGLE CALENDAR Y DATOS NOTIFICACIÓN ---
    try {
        $sql_g = "SELECT c.id_evento_google, c.barbero, u.nombre as cliente_nombre, u.telefono as cliente_telefono, c.fecha, c.hora 
                  FROM citas_barberia c 
                  JOIN usuarios u ON c.id_usuario = u.id_usuario
                  WHERE c.id_cita = ?";
        $stmt_g = mysqli_prepare($conexion, $sql_g);
        mysqli_stmt_bind_param($stmt_g, "i", $id);
        mysqli_stmt_execute($stmt_g);
        $res_g = mysqli_stmt_get_result($stmt_g);
        $row_g = mysqli_fetch_assoc($res_g);

        if ($row_g) {
            // Guardamos datos para la notificación antes de borrar
            $datos_notificacion = [
                'cliente' => $row_g['cliente_nombre'],
                'telefono' => $row_g['cliente_telefono'],
                'fecha' => $row_g['fecha'],
                'hora' => $row_g['hora'],
                'barbero' => $row_g['barbero']
            ];

            if (!empty($row_g['id_evento_google'])) {
                include_once 'google_calendar_helper.php';
                $helper = new GoogleCalendarHelper();

                $sql_email = "SELECT email_barbero FROM barberos WHERE nombre = ?";
                $stmt_e = mysqli_prepare($conexion, $sql_email);
                mysqli_stmt_bind_param($stmt_e, "s", $row_g['barbero']);
                mysqli_stmt_execute($stmt_e);
                $res_email = mysqli_stmt_get_result($stmt_e);
                $row_email = mysqli_fetch_assoc($res_email);

                $calendarId = ($row_email && !empty($row_email['email_barbero'])) ? $row_email['email_barbero'] : 'primary';
                $helper->eliminarEvento($calendarId, $row_g['id_evento_google']);
            }
        }
    } catch (Exception $e) {
        error_log("Error al eliminar cita: " . $e->getMessage());
    }
    // ------------------------------------

    $sql = "DELETE FROM citas_barberia WHERE id_cita = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $resp = ['success' => true];
        if (isset($datos_notificacion)) {
            $resp['datos_notificacion'] = $datos_notificacion;
        }
        ob_clean();
        echo json_encode($resp);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($conexion);
?>