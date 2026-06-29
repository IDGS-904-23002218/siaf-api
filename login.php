<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "conexion.php";

$datos = json_decode(file_get_contents("php://input"), true);

$correo   = $datos["correo"] ?? "";
$password = $datos["password"] ?? "";

if (empty($correo) || empty($password)) {
    http_response_code(400);
    echo json_encode(["error" => "Correo y password requeridos"]);
    exit();
}

$sql = "SELECT id, nombre, correo, rol, empleado_id 
        FROM usuarios 
        WHERE correo = ? AND password = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $correo, $password);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["error" => "Correo o password incorrectos"]);
    exit();
}

$usuario = $resultado->fetch_assoc();
echo json_encode([
    "ok"          => true,
    "id"          => $usuario["id"],
    "nombre"      => $usuario["nombre"],
    "correo"      => $usuario["correo"],
    "rol"         => $usuario["rol"],
    "empleado_id" => $usuario["empleado_id"]
]);
?>