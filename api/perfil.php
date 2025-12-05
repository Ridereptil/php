<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? 0;

if ($user_id <= 0) {
    echo json_encode(["success" => false, "message" => "ID de usuario inválido"]);
    exit();
}

try {
    $sql = "SELECT id, nombre, email, telefono, direccion FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode([
            "success" => true,
            "user" => [
                "id" => (int)$user['id'],
                "nombre" => $user['nombre'],
                "email" => $user['email'],
                "telefono" => $user['telefono'],
                "direccion" => $user['direccion'] ?? ""
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
    }
    
} catch (PDOException $e) {
    error_log("Perfil Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error en el servidor"]);
}
?>