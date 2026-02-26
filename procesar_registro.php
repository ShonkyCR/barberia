<?php
include 'conexion.php';

$usuario = mysqli_real_escape_string($conexion, $_POST['usuario'] ?? '');
$email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
$telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($usuario) || empty($password)) {
    echo "<script>alert('Usuario y clave son obligatorios'); window.history.back();</script>";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('El correo electrónico no es válido.'); window.history.back();</script>";
    exit;
}

$passHash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, usuario, email, telefono, password, estado_usuario) 
        VALUES (?, ?, ?, ?, ?, 'activo')";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "sssss", $usuario, $usuario, $email, $telefono, $passHash);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('¡Usuario creado! Inicia sesión ahora.'); window.location.href='login.html';</script>";
} else {
    echo "Error: " . mysqli_error($conexion);
}
mysqli_stmt_close($stmt);
?>