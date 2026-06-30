<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "conexion.php";

$datos = json_decode(file_get_contents("php://input"), true);

$seccion         = $datos["seccion"]         ?? "";
$flujo_lpm       = $datos["flujo_lpm"]       ?? 0;
$presion_bar     = $datos["presion_bar"]     ?? 0;
$estado_valvula  = $datos["estado_valvula"]  ?? "Activa";

// Validación básica
if (empty($seccion)) {
    http_response_code(400);
    echo json_encode(["error" => "Sección requerida"]);
    exit();
}

$sql = "INSERT INTO lecturas (timestamp, seccion, flujo_lpm, presion_bar, estado_valvula)
        VALUES (NOW(), ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sdds", $seccion, $flujo_lpm, $presion_bar, $estado_valvula);

if ($stmt->execute()) {
    echo json_encode(["ok" => true, "mensaje" => "Lectura guardada", "id" => $conexion->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error al guardar lectura"]);
}
?>