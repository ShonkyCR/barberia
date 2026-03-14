<?php
session_start();
include 'conexion.php';

// 1. Verificamos sesión
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>
                alert('No hay una sesión activa. Por favor inicia sesión.');
                window.location.href = 'login.html';
            </script>";
    exit;
}

// 2. Solo si se enviaron datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_usuario = $_SESSION['id_usuario'];
    // Esto verifica si el dato existe antes de usarlo para evitar el Warning
    $servicio = isset($_POST['servicio']) ? mysqli_real_escape_string($conexion, $_POST['servicio']) : 'Servicio no especificado';
    $barbero = isset($_POST['barbero']) ? mysqli_real_escape_string($conexion, $_POST['barbero']) : 'No especificado';
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora = mysqli_real_escape_string($conexion, $_POST['hora']);

    // 3. Validar campos vacíos
    if (empty($fecha) || empty($hora)) {
        echo "<script>alert('Error: Fecha y hora son obligatorias.'); window.history.back();</script>";
        exit;
    }

    // 3.5 Validar que la fecha y hora no hayan pasado
    $ahora = date('Y-m-d H:i:s');
    $cita_datetime = $fecha . ' ' . $hora . ':00';
    if ($cita_datetime < $ahora) {
        echo "<script>alert('La fecha u hora elegida ya ha pasado. Por favor elige una correcta.'); window.history.back();</script>";
        exit;
    }

    // 4. Verificar si ya existe la cita con Sentencias Preparadas (ahora por barbero)
    $check_sql = "SELECT id_cita FROM citas_barberia WHERE fecha = ? AND hora = ? AND barbero = ?";
    $stmt_check = mysqli_prepare($conexion, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "sss", $fecha, $hora, $barbero);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($res_check) > 0) {
        echo "<script>alert('Horario ocupado. Elige otro.'); window.history.back();</script>";
    } else {
        // 5. Insertar cita con Sentencias Preparadas
        $sql = "INSERT INTO citas_barberia (id_usuario, servicio, barbero, fecha, hora, estado) 
                VALUES (?, ?, ?, ?, ?, 'pendiente')";
        $stmt_insert = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt_insert, "issss", $id_usuario, $servicio, $barbero, $fecha, $hora);

        if (mysqli_stmt_execute($stmt_insert)) {
            $id_cita_recien_creada = mysqli_insert_id($conexion);

            // --- INTEGRACIÓN GOOGLE CALENDAR ---
            try {
                include_once 'google_calendar_helper.php';
                $helper = new GoogleCalendarHelper();

                // Obtener el email del barbero de la BD
                $sql_b = "SELECT email_barbero FROM barberos WHERE nombre = ?";
                $stmt_b = mysqli_prepare($conexion, $sql_b);
                mysqli_stmt_bind_param($stmt_b, "s", $barbero);
                mysqli_stmt_execute($stmt_b);
                $res_b = mysqli_stmt_get_result($stmt_b);
                $row_b = mysqli_fetch_assoc($res_b);

                $calendarId = ($row_b && !empty($row_b['email_barbero'])) ? $row_b['email_barbero'] : null;

                if ($calendarId) {
                    $summary = "Cita Barbería: $servicio";
                    $description = "Cita agendada para un cliente. Servicio: $servicio. Barbero: $barbero.";
                    $googleEventId = $helper->crearEvento($calendarId, $summary, $description, $fecha, $hora);

                    if ($googleEventId) {
                        $sql_update_google = "UPDATE citas_barberia SET id_evento_google = ? WHERE id_cita = ?";
                        $stmt_ug = mysqli_prepare($conexion, $sql_update_google);
                        mysqli_stmt_bind_param($stmt_ug, "si", $googleEventId, $id_cita_recien_creada);
                        mysqli_stmt_execute($stmt_ug);
                    }
                } else {
                    error_log("No se pudo sincronizar: El barbero '$barbero' no tiene un email configurado.");
                }
            } catch (Exception $e) {
                error_log("Error sincronizando con Google: " . $e->getMessage());
            }
            // ------------------------------------

            echo "<script>
                        alert('¡Cita agendada con éxito!');
                        window.location.href = 'clientes.php';
                    </script>";
        } else {
            echo "Error: " . mysqli_error($conexion);
        }
    }
} else {
    // Si alguien entra al archivo sin usar el formulario
    header("Location: clientes.php");
}

mysqli_close($conexion);
?>