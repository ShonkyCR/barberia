<?php
ob_start();
include 'conexion.php';
session_start();

// Seguridad: Solo el admin o dev puede hacer esto
if (!isset($_SESSION['id_rol']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 3)) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

header('Content-Type: application/json');

try {
    // Verificamos que lleguen los datos básicos
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        ob_clean();
        echo json_encode(["status" => "error", "message" => "No se recibió el ID de la cita"]);
        exit;
    }

    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    $servicio = mysqli_real_escape_string($conexion, $_POST['servicio']);
    $barbero = mysqli_real_escape_string($conexion, $_POST['barbero']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora = mysqli_real_escape_string($conexion, $_POST['hora']);

    // --- INTEGRACIÓN GOOGLE CALENDAR ---
    // Obtenemos el ID del evento actual antes de actualizar
    $sql_prev = "SELECT id_evento_google, barbero as barbero_anterior FROM citas_barberia WHERE id_cita = ?";
    $stmt_prev = mysqli_prepare($conexion, $sql_prev);
    mysqli_stmt_bind_param($stmt_prev, "i", $id);
    mysqli_stmt_execute($stmt_prev);
    mysqli_stmt_bind_result($stmt_prev, $id_evento_google_prev, $barbero_anterior);
    
    if (mysqli_stmt_fetch($stmt_prev)) {
        // Guardamos los datos y cerramos el stmt para poder hacer más consultas
        mysqli_stmt_close($stmt_prev);

        if (!empty($id_evento_google_prev)) {
            try {
                include_once 'google_calendar_helper.php';
                $helper = new GoogleCalendarHelper();

                // Buscamos el email del barbero (el nuevo)
                $sql_email = "SELECT email_barbero FROM barberos WHERE nombre = ?";
                $stmt_e = mysqli_prepare($conexion, $sql_email);
                mysqli_stmt_bind_param($stmt_e, "s", $barbero);
                mysqli_stmt_execute($stmt_e);
                mysqli_stmt_bind_result($stmt_e, $email_barbero_nuevo);
                
                $calendarId = (mysqli_stmt_fetch($stmt_e) && !empty($email_barbero_nuevo)) ? $email_barbero_nuevo : 'primary';
                mysqli_stmt_close($stmt_e);
                
                $helper->actualizarEvento(
                    $calendarId, 
                    $id_evento_google_prev, 
                    "Cita Barbería: $servicio", 
                    "Cita agendada para un cliente. Servicio: $servicio. Barbero: $barbero.", 
                    $fecha, 
                    $hora
                );
            } catch (Throwable $ge) {
                error_log("Error sincronizando actualización con Google: " . $ge->getMessage());
            }
        }
    } else {
        mysqli_stmt_close($stmt_prev);
    }
    // ------------------------------------

    // 4. Actualización en BD
    $sql = "UPDATE citas_barberia SET servicio = ?, barbero = ?, fecha = ?, hora = ? WHERE id_cita = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $servicio, $barbero, $fecha, $hora, $id);

    if (mysqli_stmt_execute($stmt)) {
        ob_clean();
        echo json_encode(["status" => "success"]);
    } else {
        ob_clean();
        echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
    }
    mysqli_stmt_close($stmt);

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>