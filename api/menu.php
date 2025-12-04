<?php
// api/menu.php - OBTENER MENÚ DE RESTAURANTE

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/db_connect.php';

// Obtener ID del restaurante desde GET
$restaurante_id = isset($_GET['restaurante_id']) ? (int)$_GET['restaurante_id'] : 0;

if ($restaurante_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID de restaurante inválido"]);
    exit();
}

try {
    // Consultar menú del restaurante
    $sql = "SELECT * FROM menu WHERE restaurante_id = :restaurante_id AND disponible = 1 ORDER BY categoria, nombre";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':restaurante_id' => $restaurante_id]);
    $items_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay items en la DB, usar datos de prueba según el restaurante
    if (empty($items_db)) {
        $items_db = getMenuDePrueba($restaurante_id);
    }
    
    // Preparar respuesta
    $menu_items = [];
    
    foreach ($items_db as $item) {
        $menu_items[] = [
            "id" => (int)$item["id"],
            "nombre" => $item["nombre"],
            "descripcion" => $item["descripcion"] ?? "",
            "precio" => (float)$item["precio"],
            "imagen" => $item["imagen"] ?? "https://images.unsplash.com/photo-1513104890138-7c749659a591?w=400&auto=format&fit=crop",
            "categoria" => $item["categoria"] ?? "Platos Principales"
        ];
    }
    
    // Retornar éxito
    echo json_encode([
        "success" => true,
        "message" => "Menú obtenido exitosamente",
        "restaurante_id" => $restaurante_id,
        "items" => $menu_items,
        "total" => count($menu_items)
    ]);
    
} catch (PDOException $e) {
    error_log("Menu Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Error al obtener menú"
    ]);
}

function getMenuDePrueba($restaurante_id) {
    // Menús de prueba por tipo de restaurante
    switch($restaurante_id) {
        case 1: // Dragón Roll Sushi
            return [
                [
                    'id' => 101,
                    'nombre' => 'Sushi Dragón Roll',
                    'descripcion' => 'Roll especial con salmón, aguacate y salsa especial',
                    'precio' => 18.99,
                    'imagen' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400',
                    'categoria' => 'Sushi Rolls'
                ],
                [
                    'id' => 102,
                    'nombre' => 'Sashimi Mixto',
                    'descripcion' => 'Selección de sashimi de salmón, atún y lubina',
                    'precio' => 22.50,
                    'imagen' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400',
                    'categoria' => 'Sashimi'
                ]
            ];
        
        case 2: // Ramen Samurái
            return [
                [
                    'id' => 201,
                    'nombre' => 'Ramen Tonkotsu',
                    'descripcion' => 'Caldo de cerdo 12 horas con chashu y huevo marinado',
                    'precio' => 15.99,
                    'imagen' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=400',
                    'categoria' => 'Ramen'
                ]
            ];
            
        default:
            return [
                [
                    'id' => 1,
                    'nombre' => 'Plato Especial',
                    'descripcion' => 'Plato especial del chef',
                    'precio' => 12.99,
                    'imagen' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=400',
                    'categoria' => 'Especiales'
                ]
            ];
    }
}
?>