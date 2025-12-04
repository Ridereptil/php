<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/db_connect.php';

try {
    $sql = "SELECT * FROM restaurantes WHERE activo = 1 ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $restaurantes_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $restaurantes = [];
    
    foreach ($restaurantes_db as $rest) {
        $restaurantes[] = [
            "id" => (int)$rest["id"],
            "nombre" => $rest["nombre"],
            "descripcion" => $rest["descripcion"] ?? "",
            "categoria" => $rest["categoria"] ?? "General",
            "rating" => (float)$rest["rating"],
            "tiempo_entrega" => $rest["tiempo_entrega"] ?? "30-40 min",
            "precio_envio" => (float)$rest["precio_envio"],
            "imagen_url" => $rest["imagen"] ?? "https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400"
        ];
    }
    
    // DEVOLVER DIRECTAMENTE EL ARRAY
    echo json_encode($restaurantes);
    
} catch (PDOException $e) {
    // En caso de error, devolver array vacío
    echo json_encode([]);
}
?>