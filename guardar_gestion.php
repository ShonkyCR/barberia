<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
include 'conexion.php';

if (ob_get_length())
    ob_clean();

$success = false;
$message = '';

$accion = $_POST['accion'] ?? '';
$tipo = $_POST['tipo'] ?? '';

try {
    if ($tipo === 'servicio') {
        if ($accion === 'agregar') {
            $nombre = trim($_POST['nombre'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');

            if (empty($nombre) || empty($categoria)) {
                throw new \Exception("Nombre y categoría son obligatorios.");
            }

            $stmt = mysqli_prepare($conexion, "INSERT INTO servicios (nombre, categoria) VALUES (?, ?)");
            if (!$stmt)
                throw new \Exception("Error preparando INSERT: " . mysqli_error($conexion));

            mysqli_stmt_bind_param($stmt, "ss", $nombre, $categoria);
            $success = mysqli_stmt_execute($stmt);
            if (!$success)
                throw new \Exception("Error ejecutando INSERT: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);

        } elseif ($accion === 'editar') {
            $id = (int) ($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');

            if ($id <= 0 || empty($nombre) || empty($categoria)) {
                throw new \Exception("Datos incompletos para editar servicio.");
            }

            // 1. Obtener nombre antiguo
            $nombre_antiguo = '';
            $get_old = mysqli_prepare($conexion, "SELECT nombre FROM servicios WHERE id_servicio = ?");
            if ($get_old) {
                mysqli_stmt_bind_param($get_old, "i", $id);
                mysqli_stmt_execute($get_old);
                mysqli_stmt_bind_result($get_old, $nombre_antiguo);
                mysqli_stmt_fetch($get_old);
                mysqli_stmt_close($get_old);
            }

            // 2. Actualizar catálogo
            $stmt = mysqli_prepare($conexion, "UPDATE servicios SET nombre = ?, categoria = ? WHERE id_servicio = ?");
            if (!$stmt)
                throw new \Exception("Error preparando UPDATE: " . mysqli_error($conexion));

            mysqli_stmt_bind_param($stmt, "ssi", $nombre, $categoria, $id);
            $success = mysqli_stmt_execute($stmt);

            if ($success) {
                // 3. Cascada en citas
                if (!empty($nombre_antiguo) && $nombre_antiguo !== $nombre) {
                    $stmt_cascade = mysqli_prepare($conexion, "UPDATE citas_barberia SET servicio = ? WHERE servicio = ?");
                    if ($stmt_cascade) {
                        mysqli_stmt_bind_param($stmt_cascade, "ss", $nombre, $nombre_antiguo);
                        mysqli_stmt_execute($stmt_cascade);
                        mysqli_stmt_close($stmt_cascade);
                    }
                }
            } else {
                throw new \Exception("Error ejecutando UPDATE: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);

        } elseif ($accion === 'estado') {
            $id = (int) ($_POST['id'] ?? 0);
            $estado = (int) ($_POST['estado'] ?? 0);
            $stmt = mysqli_prepare($conexion, "UPDATE servicios SET activo = ? WHERE id_servicio = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ii", $estado, $id);
                $success = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } elseif ($accion === 'eliminar') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = mysqli_prepare($conexion, "DELETE FROM servicios WHERE id_servicio = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                $success = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    } elseif ($tipo === 'barbero') {
        if ($accion === 'agregar') {
            $nombre = trim($_POST['nombre'] ?? '');
            if (empty($nombre))
                throw new \Exception("El nombre del barbero es obligatorio.");

            $stmt = mysqli_prepare($conexion, "INSERT INTO barberos (nombre) VALUES (?)");
            if (!$stmt)
                throw new \Exception("Error preparando INSERT: " . mysqli_error($conexion));

            mysqli_stmt_bind_param($stmt, "s", $nombre);
            $success = mysqli_stmt_execute($stmt);
            if (!$success)
                throw new \Exception("Error ejecutando INSERT: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);

        } elseif ($accion === 'editar') {
            $id = (int) ($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            if ($id <= 0 || empty($nombre))
                throw new \Exception("Datos incompletos para editar barbero.");

            // 1. Obtener nombre antiguo
            $nombre_antiguo = '';
            $get_old = mysqli_prepare($conexion, "SELECT nombre FROM barberos WHERE id_barbero = ?");
            if ($get_old) {
                mysqli_stmt_bind_param($get_old, "i", $id);
                mysqli_stmt_execute($get_old);
                mysqli_stmt_bind_result($get_old, $nombre_antiguo);
                mysqli_stmt_fetch($get_old);
                mysqli_stmt_close($get_old);
            }

            // 2. Actualizar catálogo
            $stmt = mysqli_prepare($conexion, "UPDATE barberos SET nombre = ? WHERE id_barbero = ?");
            if (!$stmt)
                throw new \Exception("Error preparando UPDATE: " . mysqli_error($conexion));

            mysqli_stmt_bind_param($stmt, "si", $nombre, $id);
            $success = mysqli_stmt_execute($stmt);

            if ($success) {
                // 3. Cascada en citas
                if (!empty($nombre_antiguo) && $nombre_antiguo !== $nombre) {
                    $stmt_cascade = mysqli_prepare($conexion, "UPDATE citas_barberia SET barbero = ? WHERE barbero = ?");
                    if ($stmt_cascade) {
                        mysqli_stmt_bind_param($stmt_cascade, "ss", $nombre, $nombre_antiguo);
                        mysqli_stmt_execute($stmt_cascade);
                        mysqli_stmt_close($stmt_cascade);
                    }
                }
            } else {
                throw new \Exception("Error ejecutando UPDATE: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);

        } elseif ($accion === 'estado') {
            $id = (int) ($_POST['id'] ?? 0);
            $estado = (int) ($_POST['estado'] ?? 0);
            $stmt = mysqli_prepare($conexion, "UPDATE barberos SET activo = ? WHERE id_barbero = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ii", $estado, $id);
                $success = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } elseif ($accion === 'eliminar') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = mysqli_prepare($conexion, "DELETE FROM barberos WHERE id_barbero = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                $success = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
} catch (\Exception $e) {
    $success = false;
    $message = $e->getMessage();
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $message ?: 'Error desconocido en la base de datos.'
    ]);
}
?>