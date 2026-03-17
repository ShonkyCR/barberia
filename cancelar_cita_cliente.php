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
        // Si viene como solicitud de barbero, validamos que él sea el barbero asignado
        if ($es_barbero_solicitud) {
            // Buscamos el nombre oficial del barbero por su cédula para evitar desajustes de texto en la sesión
            $sql_nom = "SELECT nombre FROM barberos WHERE cedula = (SELECT cedula FROM usuarios WHERE id_usuario = ?)";
            $stmt_nom = mysqli_prepare($conexion, $sql_nom);
            mysqli_stmt_bind_param($stmt_nom, "i", $id_usuario);
            mysqli_stmt_execute($stmt_nom);
            $res_nom = mysqli_stmt_get_result($stmt_nom);
            $row_nom = mysqli_fetch_assoc($res_nom);
            
            // Si es DEV y no es barbero, usamos su nombre de sesión como respaldo
            $nombre_barbero_oficial = $row_nom ? $row_nom['nombre'] : $_SESSION['nombre_usuario'];

            $sql_g = "SELECT c.id_evento_google, c.barbero, u.nombre as cliente_nombre, u.telefono as cliente_telefono, c.fecha, c.hora 
                      FROM citas_barberia c 
                      JOIN usuarios u ON c.id_usuario = u.id_usuario
                      WHERE c.id_cita = ? AND c.barbero = ?";
            $stmt_g = mysqli_prepare($conexion, $sql_g);
            mysqli_stmt_bind_param($stmt_g, "is", $id_cita, $nombre_barbero_oficial);
        } else {
            $sql_g = "SELECT id_evento_google, barbero FROM citas_barberia WHERE id_cita = ? AND id_usuario = ?";
            $stmt_g = mysqli_prepare($conexion, $sql_g);
            mysqli_stmt_bind_param($stmt_g, "ii", $id_cita, $id_usuario);
        }
        
        mysqli_stmt_execute($stmt_g);
        $res_g = mysqli_stmt_get_result($stmt_g);
        $row_g = mysqli_fetch_assoc($res_g);

        if ($row_g) {
            // Si es barbero, guardamos datos para WhatsApp
            if ($es_barbero_solicitud) {
                $response['datos_notificacion'] = [
                    'cliente' => $row_g['cliente_nombre'],
                    'telefono' => $row_g['cliente_telefono'],
                    'fecha' => $row_g['fecha'],
                    'hora' => $row_g['hora'],
                    'barbero' => $row_g['barbero']
                ];
            }

            if (!empty($row_g['id_evento_google'])) {
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
        } else {
            // Si no se encontró la cita con esos criterios de seguridad
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para cancelar esta cita']);
            exit;
        }
    } catch (Exception $e) {
        error_log("Error al eliminar: " . $e->getMessage());
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