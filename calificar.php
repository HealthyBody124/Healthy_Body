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

$nutriologo_id = $_POST['nutriologo_id'];
$paciente_id = $_SESSION['usuario']['id'];
$estrellas = $_POST['estrellas'];

// Verifica si ya existe una calificación
$stmt = $conexion->prepare("SELECT id FROM calificaciones WHERE nutriologo_id = ? AND paciente_id = ?");
$stmt->bind_param("ii", $nutriologo_id, $paciente_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Actualizar calificación existente
    $stmt = $conexion->prepare("UPDATE calificaciones SET estrellas = ? WHERE nutriologo_id = ? AND paciente_id = ?");
    $stmt->bind_param("iii", $estrellas, $nutriologo_id, $paciente_id);
} else {
    // Insertar nueva calificación
    $stmt = $conexion->prepare("INSERT INTO calificaciones (nutriologo_id, paciente_id, estrellas) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $nutriologo_id, $paciente_id, $estrellas);
}

if ($stmt->execute()) {
    header("Location: inicio_paciente.php?calificacion=ok");
} else {
    echo "Error al guardar calificación.";
}
?>
