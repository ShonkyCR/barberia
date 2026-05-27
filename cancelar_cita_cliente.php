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
    $id_rol = $_SESSION['id_rol'];
    $es_barbero_solicitud = isset($_POST['es_barbero']) && $_POST['es_barbero'] == '1';

    // Validación extra: Si dice ser barbero, comprobamos que tenga el rol
    if ($es_barbero_solicitud && $id_rol != 4 && $id_rol != 3) {
        echo json_encode(['success' => false, 'error' => 'No tienes permisos de barbero']);
        exit;
    }

    // --- INTEGRACIÓN GOOGLE CALENDAR ---
    try {
        // Obtener ID del evento y barbero antes de borrar la cita
        if ($es_barbero_solicitud) {
            // Buscamos el nombre oficial del barbero por su cédula para evitar desajustes de texto en la sesión
            $sql_nom = "SELECT nombre FROM barberos WHERE cedula = (SELECT cedula FROM usuarios WHERE id_usuario = ?)";
            $stmt_nom = mysqli_prepare($conexion, $sql_nom);
            mysqli_stmt_bind_param($stmt_nom, "i", $id_usuario);
            mysqli_stmt_execute($stmt_nom);
            mysqli_stmt_bind_result($stmt_nom, $nombre_barbero_oficial_res);
            $nombre_barbero_oficial = (mysqli_stmt_fetch($stmt_nom)) ? $nombre_barbero_oficial_res : $_SESSION['nombre_usuario'];
            mysqli_stmt_close($stmt_nom);

            $sql_g = "SELECT c.id_evento_google, c.barbero, u.nombre as cliente_nombre, u.telefono as cliente_telefono, c.fecha, c.hora 
                      FROM citas_barberia c 
                      LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
                      WHERE c.id_cita = ? AND c.barbero = ?";
            $stmt_g = mysqli_prepare($conexion, $sql_g);
            mysqli_stmt_bind_param($stmt_g, "is", $id_cita, $nombre_barbero_oficial);
            mysqli_stmt_execute($stmt_g);
            mysqli_stmt_bind_result($stmt_g, $id_evento_google, $barbero_res, $cliente_nombre, $cliente_telefono, $fecha_res, $hora_res);
        } else {
            $sql_g = "SELECT id_evento_google, barbero FROM citas_barberia WHERE id_cita = ? AND id_usuario = ?";
            $stmt_g = mysqli_prepare($conexion, $sql_g);
            mysqli_stmt_bind_param($stmt_g, "ii", $id_cita, $id_usuario);
            mysqli_stmt_execute($stmt_g);
            mysqli_stmt_bind_result($stmt_g, $id_evento_google, $barbero_res);
        }
        
        if (mysqli_stmt_fetch($stmt_g)) {
            // Si es barbero, guardamos datos para WhatsApp
            if ($es_barbero_solicitud) {
                $response['datos_notificacion'] = [
                    'cliente' => $cliente_nombre,
                    'telefono' => $cliente_telefono,
                    'fecha' => $fecha_res,
                    'hora' => $hora_res,
                    'barbero' => $barbero_res
                ];
            }
            
            mysqli_stmt_close($stmt_g);

            // Integración Google Calendar: En su propio try para no afectar la respuesta principal
            if (!empty($id_evento_google)) {
                try {
                    include_once 'google_calendar_helper.php';
                    $helper = new GoogleCalendarHelper();

                    // Obtener email del barbero
                    $sql_email = "SELECT email_barbero FROM barberos WHERE nombre = ?";
                    $stmt_e = mysqli_prepare($conexion, $sql_email);
                    mysqli_stmt_bind_param($stmt_e, "s", $barbero_res);
                    mysqli_stmt_execute($stmt_e);
                    mysqli_stmt_bind_result($stmt_e, $email_barbero);
                    
                    $calendarId = (mysqli_stmt_fetch($stmt_e) && !empty($email_barbero)) ? $email_barbero : 'primary';
                    mysqli_stmt_close($stmt_e);
                    
                    $helper->eliminarEvento($calendarId, $id_evento_google);
                } catch (Throwable $ge) {
                    error_log("Error específico de Google Calendar en cancelación: " . $ge->getMessage());
                }
            }
        } else {
            mysqli_stmt_close($stmt_g);
            // Si no se encontró la cita con esos criterios de seguridad
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para cancelar esta cita']);
            exit;
        }
    } catch (Throwable $e) {
        error_log("Error general al procesar datos previos a cancelación: " . $e->getMessage());
    }
    // ------------------------------------

    // 4. Ejecutamos la actualización con Sentencias Preparadas
    if ($es_barbero_solicitud) {
        $sql = "DELETE FROM citas_barberia WHERE id_cita = ? AND barbero = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "is", $id_cita, $nombre_barbero_oficial);
    } else {
        $sql = "DELETE FROM citas_barberia WHERE id_cita = ? AND id_usuario = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id_cita, $id_usuario);
    }

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