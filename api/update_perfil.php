<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

$user_id = $input['user_id'] ?? 0;
$nombre = trim($input['nombre'] ?? '');
$telefono = trim($input['telefono'] ?? '');
$direccion = trim($input['direccion'] ?? '');
$password_actual = $input['password_actual'] ?? '';
$password_nueva = $input['password_nueva'] ?? '';

if ($user_id <= 0 || empty($nombre)) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit();
}

try {
    // Primero obtener usuario actual para verificar contraseña si se cambia
    $sql = "SELECT password FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        exit();
    }
    
    // Si quiere cambiar contraseña
    if (!empty($password_actual) && !empty($password_nueva)) {
        // Verificar contraseña actual
        if (!password_verify($password_actual, $user['password'])) {
            echo json_encode(["success" => false, "message" => "Contraseña actual incorrecta"]);
            exit();
        }
        
        // Actualizar con nueva contraseña
        $sql = "UPDATE usuarios SET nombre = :nombre, telefono = :telefono, 
                direccion = :direccion, password = :password WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $hashed_password = password_hash($password_nueva, PASSWORD_DEFAULT);
        $stmt->execute([
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':direccion' => $direccion,
            ':password' => $hashed_password,
            ':id' => $user_id
        ]);
    } else {
        // Actualizar sin cambiar contraseña
        $sql = "UPDATE usuarios SET nombre = :nombre, telefono = :telefono, 
                direccion = :direccion WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':direccion' => $direccion,
            ':id' => $user_id
        ]);
    }
    
    echo json_encode(["success" => true, "message" => "Perfil actualizado exitosamente"]);
    
} catch (PDOException $e) {
    error_log("Actualizar Perfil Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error al actualizar perfil"]);
}
?>