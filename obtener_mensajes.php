<?php
session_start();
if (!isset($_SESSION['usuario'])) exit;

$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$usuario_id = $_SESSION['usuario']['id'];
$receptor_id = $_GET['receptor_id'];

$stmt = $conexion->prepare("SELECT * FROM mensajes 
    WHERE (emisor_id = ? AND receptor_id = ?) 
    OR (emisor_id = ? AND receptor_id = ?)
    ORDER BY fecha ASC");
$stmt->bind_param("iiii", $usuario_id, $receptor_id, $receptor_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$mensajes = [];
while ($fila = $resultado->fetch_assoc()) {
    $mensajes[] = $fila;
}
echo json_encode($mensajes);
?>
