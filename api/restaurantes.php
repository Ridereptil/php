<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT * FROM restaurantes WHERE activo = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $restaurantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transformar datos para la app
    $response = array();
    foreach ($restaurantes as $rest) {
        $response[] = array(
            'id' => $rest['id'],
            'nombre' => $rest['nombre'],
            'descripcion' => $rest['descripcion'],
            'categoria' => $rest['categoria'],
            'rating' => (float) $rest['rating'],
            'tiempo_entrega' => $rest['tiempo_entrega'] ?: '30-40 min',
            'precio_envio' => (float) $rest['precio_envio'],
            'imagen_url' => $rest['imagen'] ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&auto=format&fit=crop'
        );
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>