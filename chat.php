<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    exit('Acceso no autorizado.');
}

$usuario = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// ------------------- ENVIAR MENSAJE -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = trim($_POST['mensaje']);
    $destinatario_id = intval($_POST['destinatario_id']);

    if ($mensaje === '' || !$destinatario_id) {
        exit;
    }

    $stmt = $conexion->prepare("INSERT INTO mensajes (remitente_id, destinatario_id, mensaje, tipo_remitente, fecha_envio)
                                VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiss", $usuario['id'], $destinatario_id, $mensaje, $rol);
    $stmt->execute();
    $stmt->close();
    exit;
}

// ------------------- OBTENER MENSAJES -------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['otro_id'])) {
    $otro_id = intval($_GET['otro_id']);
    $mi_id = $usuario['id'];
    $mi_rol = $rol;
    $otro_rol = $mi_rol === 'nutriologo' ? 'paciente' : 'nutriologo';

    $stmt = $conexion->prepare("
        SELECT * FROM mensajes 
        WHERE 
            (remitente_id = ? AND destinatario_id = ? AND tipo_remitente = ?)
         OR (remitente_id = ? AND destinatario_id = ? AND tipo_remitente = ?)
        ORDER BY fecha_envio ASC
    ");
    $stmt->bind_param("iisiis", $mi_id, $otro_id, $mi_rol, $otro_id, $mi_id, $otro_rol);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($fila = $result->fetch_assoc()) {
        $clase = ($fila['remitente_id'] == $mi_id && $fila['tipo_remitente'] == $mi_rol) ? 'msg-tuyo' : 'msg-otro';
        echo "<div class='$clase'>" . htmlspecialchars($fila['mensaje']) . "</div>";
    }

    $stmt->close();
}

$conexion->close();
