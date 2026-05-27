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
        // Usamos LEFT JOIN para no fallar si por alguna razón el usuario no aparece
        $sql_g = "SELECT c.id_evento_google, c.barbero, u.nombre as cliente_nombre, u.telefono as cliente_telefono, c.fecha, c.hora 
                  FROM citas_barberia c 
                  LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
                  WHERE c.id_cita = ?";
        $stmt_g = mysqli_prepare($conexion, $sql_g);
        mysqli_stmt_bind_param($stmt_g, "i", $id);
        mysqli_stmt_execute($stmt_g);
        mysqli_stmt_bind_result($stmt_g, $id_evento_google, $barbero_res, $cliente_nombre, $cliente_telefono, $fecha_res, $hora_res);
        
        if (mysqli_stmt_fetch($stmt_g)) {
            // Guardamos datos para la notificación SIEMPRE que se encuentre la cita
            $datos_notificacion = [
                'cliente' => $cliente_nombre ?: "Cliente",
                'telefono' => $cliente_telefono ?: "",
                'fecha' => $fecha_res,
                'hora' => $hora_res,
                'barbero' => $barbero_res
            ];
            
            // Log para debug interno (se puede ver en el log de errores de XAMPP)
            error_log("Cita encontrada para eliminación ID $id. Teléfono: " . ($cliente_telefono ?: 'VACÍO'));
            
            mysqli_stmt_close($stmt_g);

            // Integración Google Calendar
            if (!empty($id_evento_google)) {
                try {
                    include_once 'google_calendar_helper.php';
                    $helper = new GoogleCalendarHelper();

                    $sql_email = "SELECT email_barbero FROM barberos WHERE nombre = ?";
                    $stmt_e = mysqli_prepare($conexion, $sql_email);
                    mysqli_stmt_bind_param($stmt_e, "s", $barbero_res);
                    mysqli_stmt_execute($stmt_e);
                    mysqli_stmt_bind_result($stmt_e, $email_barbero);
                    
                    $calendarId = (mysqli_stmt_fetch($stmt_e) && !empty($email_barbero)) ? $email_barbero : 'primary';
                    mysqli_stmt_close($stmt_e);

                    $helper->eliminarEvento($calendarId, $id_evento_google);
                } catch (Throwable $ge) {
                    error_log("Error específico de Google Calendar al eliminar: " . $ge->getMessage());
                }
            }
        } else {
            error_log("No se encontró la cita ID $id en el fetch previo a eliminación.");
            mysqli_stmt_close($stmt_g);
        }
    } catch (Throwable $e) {
        error_log("Error general al procesar datos previos a eliminación: " . $e->getMessage());
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