<?php
$host     = "sql105.infinityfree.com";
$db       = "if0_42292004_siaf";
$usuario  = "if0_42292004";
$password = "8fIRgNGCUz";

$conexion = new mysqli($host, $usuario, $password, $db);

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $conexion->connect_error]);
    exit();
}

$conexion->set_charset("utf8");
?>