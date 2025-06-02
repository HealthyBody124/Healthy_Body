<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'paciente') {
    header("Location: login.php");
    exit;
}

$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");

$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$nutriologo_id = (int)$_POST['nutriologo_id'];
$paciente_id = $_SESSION['usuario']['id'];

// Obtener datos del nutriólogo
$stmt = $conexion->prepare("SELECT nombre, apellido, precio_consulta FROM nutriologos WHERE id = ?");
$stmt->bind_param("i", $nutriologo_id);
$stmt->execute();
$nutriologo = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar y Pagar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef7ff;
            padding: 30px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #007bff;
            margin-bottom: 15px;
        }
        .info {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .btn-pagar {
            background: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-pagar:hover {
            background: #218838;
        }
    </style>
  
<style>
.card-form {
    background: #f9f9f9;
    max-width: 400px;
    margin: auto;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    font-family: 'Segoe UI', sans-serif;
    margin-top: 20px;
}

.card-form h3 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

.card-form label {
    font-weight: bold;
    display: block;
    margin-top: 10px;
    color: #444;
}

.card-form input {
    width: 100%;
    padding: 12px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 16px;
}

.card-form .row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.card-form .row input {
    flex: 1;
}

.btn-pagar {
    margin-top: 20px;
    width: 100%;
    padding: 12px;
    background: linear-gradient(to right, #4CAF50, #45A049);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-pagar:hover {
    background: linear-gradient(to right, #43a047, #388e3c);
}
</style>

</head>
<body>
<form method="POST" action="procesar_pagos.php" class="card-form">
    <h3>Confirmar y Pagar Cita</h3>

    <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
    <input type="hidden" name="hora" value="<?= htmlspecialchars($hora) ?>">
    <input type="hidden" name="nutriologo_id" value="<?= $nutriologo_id ?>">

    <label for="nombre_tarjeta">Nombre en la tarjeta</label>
    <input type="text" id="nombre_tarjeta" name="nombre_tarjeta" required>

    <label for="numero_tarjeta">Número de tarjeta</label>
    <input type="text" id="numero_tarjeta" name="numero_tarjeta" pattern="\d{16}" maxlength="16" required placeholder="1234 5678 9012 3456">

    <div class="row">
        <div>
            <label for="fecha_vencimiento">Vencimiento (MM/AA)</label>
            <input type="text" id="fecha_vencimiento" name="fecha_vencimiento" pattern="\d{2}/\d{2}" maxlength="5" required placeholder="MM/AA">
        </div>
        <div>
            <label for="cvv">CVV</label>
            <input type="text" id="cvv" name="cvv" pattern="\d{3}" maxlength="3" required placeholder="123">
        </div>
    </div>

    <button type="submit" class="btn-pagar">Pagar y Agendar</button>
</form>

    </div>
</body>
</html>
