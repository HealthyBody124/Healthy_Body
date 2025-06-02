<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(to bottom, #d4f4dd, #b3e5fc);
            text-align: center;
            padding-top: 80px;
            margin: 0;
        }

        h1 {
            font-size: 3em;
            color: #2e7d32;
            margin-bottom: 10px;
        }

        .descripcion {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 40px;
        }

        .boton-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        .boton {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 220px;
            padding: 15px;
            font-size: 1.1em;
            font-weight: bold;
            color: white;
            background-color: #66bb6a;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .boton:hover {
            background-color: #43a047;
            transform: scale(1.05);
        }

        .subtitulo {
            font-size: 1.5em;
            color: #388e3c;
            margin: 30px 0 15px;
        }

        .boton i {
            font-size: 1.2em;
        }
    </style>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

    <h1>Bienvenido a Healthy Body</h1>
    <p class="descripcion">Mejora tu salud con el acompañamiento ideal</p>

    <div class="boton-container">
        <a href="login.php" class="boton"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
    </div>

    <div class="subtitulo">Registrarte como:</div>

    <div class="boton-container">
        <a href="registro_paciente.php" class="boton"><i class="fas fa-user"></i> Paciente</a>
        <a href="registro_nutriologo.php" class="boton"><i class="fas fa-apple-alt"></i> Nutriólogo</a>
    </div>

</body>
</html>
