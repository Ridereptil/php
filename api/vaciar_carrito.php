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
        
        $sql = "DELETE FROM carrito WHERE usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Carrito vaciado exitosamente',
                'items_eliminados' => $stmt->affected_rows
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al vaciar carrito'
            ]);
        }
        
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