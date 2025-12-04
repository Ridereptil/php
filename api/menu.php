<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

require_once __DIR__ . '/../config/db_connect.php';

// Obtener ID del restaurante desde GET
$restaurante_id = $_GET['restaurante_id'] ?? 0;

if (!$restaurante_id) {
    http_response_code(400);
    echo json_encode(["error" => "ID de restaurante requerido"]);
    exit();
}

try {
    $sql = "SELECT id, restaurante_id, nombre, descripcion, precio, categoria, imagen_url 
            FROM platillos 
            WHERE restaurante_id = :restaurante_id AND disponible = 1 
            ORDER BY categoria, nombre";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':restaurante_id' => $restaurante_id]);
    $platillos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($platillos);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error al obtener menú",
        "message" => $e->getMessage()
    ]);
}
?>