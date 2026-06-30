<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "conexion.php";

// GET — obtener intervenciones
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $sql = "SELECT i.id, i.hora_llegada, i.accion, i.notas,
                   f.seccion, f.severidad, f.estado,
                   u.nombre as ingeniero
            FROM intervenciones i
            JOIN fugas f ON i.fuga_id = f.id
            JOIN usuarios u ON i.usuario_id = u.id
            ORDER BY i.hora_llegada DESC";

    $resultado = $conexion->query($sql);
    $intervenciones = [];
    while ($fila = $resultado->fetch_assoc()) {
        $intervenciones[] = $fila;
    }
    echo json_encode($intervenciones);
}

// POST — guardar nueva intervención
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $datos      = json_decode(file_get_contents("php://input"), true);
    $fuga_id    = $datos["fuga_id"]    ?? 0;
    $usuario_id = $datos["usuario_id"] ?? 0;
    $accion     = $datos["accion"]     ?? "";
    $notas      = $datos["notas"]      ?? "";
    $hora_llegada = date("Y-m-d H:i:s");

    $sql = "INSERT INTO intervenciones 
                (fuga_id, usuario_id, hora_llegada, accion, notas)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iisss", $fuga_id, $usuario_id, $hora_llegada, $accion, $notas);

    if ($stmt->execute()) {
        // Calcular duración real en horas con decimales (no truncar a horas enteras),
        // así una fuga resuelta en 20 minutos muestra 0.33 h en vez de 0.
        $conexion->query("
            UPDATE fugas 
            SET estado = 'Resuelta',
                duracion_horas = ROUND(TIMESTAMPDIFF(SECOND, fecha_deteccion, NOW()) / 3600, 2)
            WHERE id = $fuga_id
        ");
        echo json_encode(["ok" => true, "mensaje" => "Intervención registrada"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al guardar"]);
    }
}
?>