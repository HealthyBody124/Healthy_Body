<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Paciente</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(to right, #e8f5e9, #c8e6c9);
            padding: 40px;
            margin: 0;
        }

        .form-container {
            max-width: 520px;
            margin: auto;
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 25px;
        }

        .form-container .icon {
            text-align: center;
            font-size: 2.5em;
            color: #43a047;
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 1em;
            transition: border 0.3s ease;
        }

        input:focus {
            border-color: #66bb6a;
            outline: none;
        }

        .btn-container {
            margin-top: 30px;
            text-align: center;
        }

        .btn, .btn-secondary {
            display: inline-block;
            padding: 12px 30px;
            font-size: 1.1em;
            border-radius: 10px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .btn {
            background-color: #66bb6a;
            color: white;
            border: none;
        }

        .btn:hover {
            background-color: #43a047;
            transform: scale(1.05);
        }

        .btn-secondary {
            background-color: #b0bec5;
            color: white;
            margin-left: 10px;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #78909c;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<div class="form-container">
    <div class="icon">
        <i class="fas fa-carrot"></i>
    </div>
    <h2>Registro de Paciente</h2>
    <form action="guardar_paciente.php" method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required>

        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" required>

        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" name="fecha_nacimiento" required>

        <label for="telefono">Número Telefónico:</label>
        <input type="tel" name="telefono" required pattern="[0-9]{10}">

        <label for="correo">Correo Electrónico:</label>
        <input type="email" name="correo" required>

        <label for="contraseña">Contraseña:</label>
        <input type="password" name="contraseña" required>

        <label for="confirmar_contraseña">Confirmar Contraseña:</label>
        <input type="password" name="confirmar_contraseña" required>

        <div class="btn-container">
            <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Registrar</button>
            <a href="index.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Regresar</a>
        </div>
    </form>
</div>

</body>
</html>
