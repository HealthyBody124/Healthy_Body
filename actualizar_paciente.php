<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'paciente') {
    header("Location: login.php");
    exit;
}

$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$usuario_id = (int)$_SESSION['usuario']['id'];
$stmt = $conexion->prepare("SELECT nombre, apellido, telefono FROM pacientes WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    $stmt = $conexion->prepare("UPDATE pacientes SET nombre=?, apellido=?, telefono=? WHERE id=?");
    $stmt->bind_param("sssi", $nombre, $apellido, $telefono, $usuario_id);


    if ($stmt->execute()) {
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['apellido'] = $apellido;
        $_SESSION['usuario']['telefono'] = $telefono;
        $mensaje = "<p class='mensaje exito'>Datos actualizados correctamente.</p>";
    } else {
        $mensaje = "<p class='mensaje error'>Error al actualizar los datos.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Datos del Paciente</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 40px;
        }

        .contenedor {
            max-width: 500px;
            background-color: #fff;
            margin: 0 auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #28a745;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: #fff;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        .cancelar {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #555;
            text-decoration: none;
        }

        .cancelar:hover {
            text-decoration: underline;
            color: #000;
        }

        .mensaje {
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
        }

        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2>Actualizar Información del Paciente</h2>
        <?= $mensaje ?>
        <form method="POST">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

            <label>Apellido:</label>
            <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>

            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" required>

            <button type="submit">Guardar Cambios</button>
            <a class="cancelar" href="inicio_paciente.php">← Cancelar y volver</a>
        </form>
    </div>
</body>
</html>
