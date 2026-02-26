<?php
session_start();
include 'conexion.php';

// Capturamos datos
$email_ingresado = mysqli_real_escape_string($conexion, $_POST['usuario'] ?? '');
$password_escrita = $_POST['password'] ?? '';

// Consulta con Sentencias Preparadas
$sql = "SELECT id_usuario, nombre, password, id_rol FROM usuarios WHERE email = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $email_ingresado);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($resultado)) {
    // Verificación de contraseña
    if (password_verify($password_escrita, $row['password'])) {

        // Creamos la sesión
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['nombre_usuario'] = $row['nombre'];
        $_SESSION['id_rol'] = (int) $row['id_rol'];

        // REDIRECCIÓN AUTOMÁTICA
        if ($_SESSION['id_rol'] === 1) {
            header("Location: admin.php");
        } else {
            header("Location: clientes.php");
        }
        exit(); // Muy importante para que no se ejecute nada más

    } else {
        echo "<script>alert('Contraseña incorrecta'); window.location.href='login.html';</script>";
    }
} else {
    echo "<script>alert('Usuario no encontrado'); window.location.href='login.html';</script>";
}
?>