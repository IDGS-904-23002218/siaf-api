<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "conexion.php";

$sql = "SELECT id, seccion, volumen_perdido, fecha_deteccion, 
               duracion_horas, severidad, estado 
        FROM fugas 
        ORDER BY fecha_deteccion DESC";

$resultado = $conexion->query($sql);

$fugas = [];
while ($fila = $resultado->fetch_assoc()) {
    $fugas[] = $fila;
}

echo json_encode($fugas);
?>