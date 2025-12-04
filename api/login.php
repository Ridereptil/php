<?php
// api/login.php - AUTENTICACIÓN DE USUARIOS

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/db_connect.php';

// Leer JSON de entrada
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "JSON inválido"]);
    exit();
}

$email = trim($input["email"] ?? "");
$password = trim($input["password"] ?? "");

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email y contraseña requeridos"]);
    exit();
}

try {
    // Buscar usuario por email
    $sql = "SELECT id, nombre, email, telefono, password FROM usuarios WHERE email = :email LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        exit();
    }

    // Verificar contraseña
    $password_correct = false;
    
    // 1. Verificar hash (si usaste password_hash)
    if (password_verify($password, $user["password"])) {
        $password_correct = true;
    }
    // 2. Verificar texto plano (backup)
    elseif ($password === $user["password"]) {
        $password_correct = true;
    }

    if (!$password_correct) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
        exit();
    }

    // Éxito - retornar datos del usuario (sin password)
    echo json_encode([
        "success" => true,
        "message" => "Login exitoso",
        "user" => [
            "id" => (int)$user["id"],
            "nombre" => $user["nombre"],
            "email" => $user["email"],
            "telefono" => $user["telefono"]
        ]
    ]);

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error en el servidor"]);
}
?>