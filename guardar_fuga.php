<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "conexion.php";
require_once "vendor/autoload.php";

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

$datos = json_decode(file_get_contents("php://input"), true);

$seccion    = $datos["seccion"]    ?? "";
$volumen    = $datos["volumen"]    ?? 0;
$severidad  = $datos["severidad"]  ?? "Baja";

// Validación básica
if (empty($seccion)) {
    http_response_code(400);
    echo json_encode(["error" => "Sección requerida"]);
    exit();
}

// Evitar duplicar fugas: si ya hay una fuga Activa en esa sección, no crear otra
$sqlCheck = "SELECT id FROM fugas WHERE seccion = ? AND estado = 'Activa' LIMIT 1";
$stmtCheck = $conexion->prepare($sqlCheck);
$stmtCheck->bind_param("s", $seccion);
$stmtCheck->execute();
$resultado = $stmtCheck->get_result();

if ($resultado->num_rows > 0) {
    $fugaExistente = $resultado->fetch_assoc();
    echo json_encode([
        "ok" => true,
        "id" => $fugaExistente["id"],
        "mensaje" => "Ya existe una fuga activa en esta sección"
    ]);
    exit();
}

// Si no hay fuga activa, crear una nueva
$sql = "INSERT INTO fugas (seccion, volumen_perdido, fecha_deteccion, severidad, estado)
        VALUES (?, ?, NOW(), ?, 'Activa')";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sds", $seccion, $volumen, $severidad);

if ($stmt->execute()) {
    $fugaId = $conexion->insert_id;

    // Publicar a MQTT para que la app (y cualquier otro cliente suscrito)
    // reciba la notificación en tiempo real, sin importar quién haya
    // creado la fuga (ESP32, Postman, etc.)
    try {
        $mqtt = new MqttClient(
            "e47da3fe341c415c80da279011aa214f.s1.eu.hivemq.cloud",
            8883,
            "siaf_backend_" . uniqid() // client id único para evitar choques
        );

        $settings = (new ConnectionSettings())
            ->setUsername("Carlos")
            ->setPassword("CarlosLR01")
            ->setUseTls(true)
            ->setConnectTimeout(5);

        $mqtt->connect($settings, true);
        $mqtt->publish("siaf/fuga", json_encode([
            "fuga_id"   => $fugaId,
            "seccion"   => $seccion,
            "severidad" => $severidad
        ]), 1);
        $mqtt->disconnect();
    } catch (\Throwable $e) {
        // No bloqueamos la respuesta HTTP si MQTT falla;
        // la fuga ya quedó guardada en la base de datos.
        error_log("Error publicando a MQTT: " . $e->getMessage());
    }

    echo json_encode(["ok" => true, "id" => $fugaId, "mensaje" => "Fuga registrada"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error al guardar fuga"]);
}
?>