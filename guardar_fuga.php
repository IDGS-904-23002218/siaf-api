<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "conexion.php";

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
    echo json_encode(["ok" => true, "id" => $conexion->insert_id, "mensaje" => "Fuga registrada"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error al guardar fuga"]);
}
?>