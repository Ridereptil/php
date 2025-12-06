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
    
    if (isset($data['usuario_id'], $data['restaurant_id'], $data['total'], $data['items'])) {
        $usuario_id = intval($data['usuario_id']);
        $restaurant_id = intval($data['restaurant_id']);
        $total = floatval($data['total']);
        $fecha = isset($data['fecha']) ? $data['fecha'] : date('Y-m-d H:i:s');
        $estado = isset($data['estado']) ? $data['estado'] : 'pendiente';
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // 1. Insertar en tabla pedidos
            $sql_pedido = "INSERT INTO pedidos (usuario_id, restaurant_id, total, fecha, estado) 
                          VALUES (?, ?, ?, ?, ?)";
            $stmt_pedido = $conn->prepare($sql_pedido);
            $stmt_pedido->bind_param("iidss", $usuario_id, $restaurant_id, $total, $fecha, $estado);
            
            if (!$stmt_pedido->execute()) {
                throw new Exception("Error al crear pedido: " . $stmt_pedido->error);
            }
            
            $pedido_id = $stmt_pedido->insert_id;
            
            // 2. Insertar items del pedido
            $items = $data['items'];
            foreach ($items as $item) {
                $item_id = intval($item['item_id']);
                $cantidad = intval($item['cantidad']);
                $precio_unitario = floatval($item['precio_unitario']);
                
                $sql_item = "INSERT INTO pedido_items (pedido_id, item_id, cantidad, precio_unitario) 
                            VALUES (?, ?, ?, ?)";
                $stmt_item = $conn->prepare($sql_item);
                $stmt_item->bind_param("iiid", $pedido_id, $item_id, $cantidad, $precio_unitario);
                
                if (!$stmt_item->execute()) {
                    throw new Exception("Error al insertar item del pedido: " . $stmt_item->error);
                }
                
                $stmt_item->close();
            }
            
            // 3. Vaciar carrito del usuario
            $sql_vaciar_carrito = "DELETE FROM cartbox WHERE usuario_id = ?";
            $stmt_vaciar = $conn->prepare($sql_vaciar_carrito);
            $stmt_vaciar->bind_param("i", $usuario_id);
            
            if (!$stmt_vaciar->execute()) {
                throw new Exception("Error al vaciar carrito: " . $stmt_vaciar->error);
            }
            
            // Commit de la transacción
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'pedido_id' => $pedido_id,
                'total' => $total,
                'items_count' => count($items)
            ]);
            
            $stmt_pedido->close();
            $stmt_vaciar->close();
            
        } catch (Exception $e) {
            // Rollback en caso de error
            $conn->rollback();
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Faltan datos requeridos'
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