<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "conexion.php";

$sql = "SELECT seccion, pred_24h, pred_48h, pred_72h, riesgo, fecha_calculo
        FROM predicciones_riesgo
        ORDER BY 
            CASE riesgo 
                WHEN 'ALTO' THEN 1 
                WHEN 'MEDIO' THEN 2 
                ELSE 3 
            END";

$resultado = $conexion->query($sql);

$predicciones = [];
while ($fila = $resultado->fetch_assoc()) {
    $predicciones[] = $fila;
}

echo json_encode($predicciones);
?>