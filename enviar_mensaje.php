<?php
session_start();
if (!isset($_SESSION['usuario'])) exit;

$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$emisor_id = $_SESSION['usuario']['id'];
$receptor_id = $_POST['receptor_id'];
$mensaje = $_POST['mensaje'];

$stmt = $conexion->prepare("INSERT INTO mensajes (emisor_id, receptor_id, mensaje) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $emisor_id, $receptor_id, $mensaje);
$stmt->execute();
?>
