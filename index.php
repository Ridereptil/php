<?php
// index.php - Página principal
header("Content-Type: application/json");

echo json_encode([
    "api" => "DragonBite Backend",
    "version" => "1.0",
    "status" => "online",
    "database" => "MySQL en Railway",
    "endpoints" => [
        "POST /api/login.php" => "Autenticación",
        "POST /api/register.php" => "Registro",
        "GET /test.php" => "Prueba conexión"
    ],
    "timestamp" => date("Y-m-d H:i:s"),
    "environment" => "Railway"
]);
?>