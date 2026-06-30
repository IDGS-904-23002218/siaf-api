<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "conexion.php";

$datos   = json_decode(file_get_contents("php://input"), true);
$fuga_id = $datos["fuga_id"] ?? 0;

if (empty($fuga_id)) {
    http_response_code(400);
    echo json_encode(["error" => "fuga_id requerido"]);
    exit();
}

// Verificar que la fuga exista antes de reactivarla
$sqlCheck = "SELECT id FROM fugas WHERE id = ? LIMIT 1";
$stmtCheck = $conexion->prepare($sqlCheck);
$stmtCheck->bind_param("i", $fuga_id);
$stmtCheck->execute();
$resultado = $stmtCheck->get_result();

if ($resultado->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Fuga no encontrada"]);
    exit();
}

// Reactivar: vuelve a Activa, resetea fecha_deteccion a ahora
// (para que el cronómetro arranque de cero) y limpia duracion_horas anterior.
$sql = "UPDATE fugas 
        SET estado = 'Activa',
            fecha_deteccion = NOW(),
            duracion_horas = NULL
        WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $fuga_id);

if ($stmt->execute()) {
    echo json_encode(["ok" => true, "mensaje" => "Fuga reactivada"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error al reactivar la fuga"]);
}
?>