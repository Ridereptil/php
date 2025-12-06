<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['usuario_id'], $data['platillo_id'], $data['restaurante_id'], $data['cantidad'])) {
        $usuario_id = intval($data['usuario_id']);
        $platillo_id = intval($data['platillo_id']);
        $restaurante_id = intval($data['restaurante_id']);
        $cantidad = intval($data['cantidad']);
        
        // Verificar si ya existe en el carrito
        $sql_check = "SELECT id, cantidad FROM carrito 
                     WHERE usuario_id = ? AND platillo_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $usuario_id, $platillo_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            // Actualizar cantidad existente
            $row = $result_check->fetch_assoc();
            $nueva_cantidad = $row['cantidad'] + $cantidad;
            
            $sql_update = "UPDATE carrito SET cantidad = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ii", $nueva_cantidad, $row['id']);
            
            if ($stmt_update->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cantidad actualizada en carrito',
                    'cantidad' => $nueva_cantidad
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar carrito'
                ]);
            }
            
            $stmt_update->close();
        } else {
            // Insertar nuevo item
            $sql_insert = "INSERT INTO carrito (usuario_id, platillo_id, restaurante_id, cantidad) 
                          VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("iiii", $usuario_id, $platillo_id, $restaurante_id, $cantidad);
            
            if ($stmt_insert->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Agregado al carrito',
                    'cantidad' => $cantidad,
                    'carrito_id' => $stmt_insert->insert_id
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al agregar al carrito'
                ]);
            }
            
            $stmt_insert->close();
        }
        
        $stmt_check->close();
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