<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    $conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Buscar en pacientes
    $stmt = $conexion->prepare("SELECT * FROM pacientes WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($password, $usuario['contrasena'])) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['rol'] = 'paciente';
            header("Location: inicio_paciente.php");
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        // Buscar en nutriologos
        $stmt = $conexion->prepare("SELECT * FROM nutriologos WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($password, $usuario['contrasena'])) {
                $_SESSION['usuario'] = $usuario;
                $_SESSION['rol'] = 'nutriologo';
                header("Location: inicio_nutriologo.php");
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Correo no registrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #c2e9fb, #a1c4fd);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        form {
            background: white;
            padding: 35px 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            position: relative;
        }

        h2 {
            margin-bottom: 25px;
            text-align: center;
            color: #2e7d32;
            font-weight: 700;
        }

        label {
            display: block;
            margin-top: 15px;
            color: #444;
            font-weight: 600;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 12px;
            font-size: 15px;
            background: #f7f9fc;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #66bb6a;
            outline: none;
            background: #e8f5e9;
        }

        input[type="submit"] {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            background: #66bb6a;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 5px 12px rgba(102,187,106,0.6);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="submit"]:hover {
            background: #43a047;
            box-shadow: 0 6px 15px rgba(67,160,71,0.8);
        }

        .error {
            color: #d32f2f;
            background: #ffebee;
            border: 1px solid #f44336;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            box-shadow: inset 0 0 5px #f44336;
        }

        /* Botón volver */
        .back-button {
            display: inline-block;
            text-decoration: none;
            background: #1976d2;
            color: white;
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 700;
            position: absolute;
            left: 20px;
            top: -55px;
            box-shadow: 0 4px 10px rgba(25,118,210,0.5);
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background: #0d47a1;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <a href="index.php" class="back-button">← Volver</a>
    <h2>Iniciar Sesión</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <label for="correo">Correo electrónico:</label>
    <input type="email" name="correo" id="correo" required autofocus />

    <label for="password">Contraseña:</label>
    <input type="password" name="password" id="password" required />

    <input type="submit" value="Ingresar" />
</form>

</body>
</html>
