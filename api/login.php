<?php
// login.php - Manteniendo tu lógica original
require_once __DIR__ . "/config/db_connect.php";

$pdo = getDBConnection();

// Leer JSON del cuerpo
$raw_input = file_get_contents("php://input");

if (empty($raw_input)) {
    echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
    exit();
}

$data = json_decode($raw_input, true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "JSON inválido"]);
    exit();
}

$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Email y contraseña son requeridos"]);
    exit();
}

try {
    // Consulta preparada (segura contra SQL injection)
    $stmt = $pdo->prepare("SELECT id, nombre, email, telefono, password FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        exit();
    }

    // VERIFICACIÓN DE CONTRASEÑA (compatible con ambos sistemas)
    $password_correct = false;
    
    // Opción 1: Verificar hash (si usaste password_hash)
    if (password_verify($password, $user["password"])) {
        $password_correct = true;
    }
    // Opción 2: Verificar texto plano (si guardaste en texto)
    elseif ($password === $user["password"]) {
        $password_correct = true;
    }
    // Opción 3: Hash MD5 (legacy)
    elseif (md5($password) === $user["password"]) {
        $password_correct = true;
    }

    if (!$password_correct) {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
        exit();
    }

    // ÉXITO - Respuesta JSON
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

} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    echo json_encode([
        "success" => false, 
        "message" => "Error en el servidor. Intenta más tarde."
    ]);
}
?>