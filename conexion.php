<?php
error_reporting(0);
ini_set('display_errors', 0);

$host     = getenv("MYSQLHOST");
$db       = getenv("MYSQLDATABASE");
$usuario  = getenv("MYSQLUSER");
$password = getenv("MYSQLPASSWORD");
$puerto   = (int)(getenv("MYSQLPORT") ?: 3306);

$conexion = new mysqli($host, $usuario, $password, $db, $puerto);
if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $conexion->connect_error]);
    exit();
}
$conexion->set_charset("utf8");
?>
