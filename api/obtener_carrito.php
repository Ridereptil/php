<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['usuario_id'])) {
        $usuario_id = intval($data['usuario_id']);
        
        // Obtener items del carrito con detalles de los platillos
        $sql = "SELECT 
                c.id as carrito_id,
                c.platillo_id,
                c.cantidad,
                c.agregado_en,
                p.nombre,
                p.descripcion,
                p.precio,
                p.imagen_url,
                p.categoria,
                r.nombre as restaurante_nombre,
                r.id as restaurante_id
            FROM carrito c
            JOIN platillos p ON c.platillo_id = p.id
            JOIN restaurantes r ON p.restaurante_id = r.id
            WHERE c.usuario_id = ?
            ORDER BY c.agregado_en DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        $total = 0;
        
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'carrito_id' => $row['carrito_id'],
                'platillo_id' => $row['platillo_id'],
                'nombre' => $row['nombre'],
                'descripcion' => $row['descripcion'],
                'precio' => floatval($row['precio']),
                'cantidad' => $row['cantidad'],
                'subtotal' => floatval($row['precio']) * $row['cantidad'],
                'imagen_url' => $row['imagen_url'],
                'categoria' => $row['categoria'],
                'restaurante_id' => $row['restaurante_id'],
                'restaurante_nombre' => $row['restaurante_nombre'],
                'agregado_en' => $row['agregado_en']
            ];
            
            $total += floatval($row['precio']) * $row['cantidad'];
        }
        
        echo json_encode([
            'success' => true,
            'items' => $items,
            'total_items' => count($items),
            'total_precio' => $total,
            'usuario_id' => $usuario_id
        ]);
        
        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Falta usuario_id'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}

$conn->close();
?>