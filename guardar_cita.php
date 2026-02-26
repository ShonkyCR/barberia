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