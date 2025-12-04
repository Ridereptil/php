<?php
// register.php - Manteniendo tu lógica anti-firewall
require_once "config/db_connect.php";

// =======================================
// HACK ANTI-FIREWALL (conservado)
// =======================================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, User-Agent, Accept, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

header("X-Requested-With: XMLHttpRequest");
header("User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
header("Accept: application/json");

// Delay opcional (si necesitas mantenerlo)
// sleep(1); // ⚠ Comenta esto en producción si no es necesario

// ===============================
// LEE CUERPO MANUAL
// ===============================
$raw = file_get_contents("php://input");

if (!$raw || trim($raw) === '') {
    echo json_encode(["success" => false, "message" => "No se recibió contenido"]);
    exit;
}

// Intentar decodificar JSON
$input = json_decode($raw, true);

if (!is_array($input) || json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "JSON inválido o mal formado"]);
    exit;
}

// ==========================
// Obtener y validar variables
// ==========================
$nombre = trim($input["nombre"] ?? "");
$email = trim($input["email"] ?? "");
$password = trim($input["password"] ?? "");
$telefono = trim($input["telefono"] ?? "");
$direccion = trim($input["direccion"] ?? "");

// Validación mejorada
$errors = [];
if (strlen($nombre) < 2) $errors[] = "Nombre muy corto";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email inválido";
if (strlen($password) < 6) $errors[] = "Contraseña mínimo 6 caracteres";
if (strlen($telefono) < 8) $errors[] = "Teléfono inválido";

if (!empty($errors)) {
    echo json_encode(["success" => false, "message" => implode(", ", $errors)]);
    exit;
}

try {
    $db = getDBConnection();

    // Verificar si email ya existe
    $checkStmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $checkStmt->execute([$email]);

    if ($checkStmt->rowCount() > 0) {
        echo json_encode(["success" => false, "message" => "El email ya está registrado"]);
        exit;
    }

    // Insertar nuevo usuario con hash seguro
    $insertStmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, telefono, direccion, fecha_registro)
                                VALUES (?, ?, ?, ?, ?, NOW())");

    // Hash de contraseña (RECOMENDADO para seguridad)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $insertStmt->execute([
        $nombre,
        $email,
        $hashed_password,  // Guardado como hash
        $telefono,
        $direccion
    ]);

    $id = $db->lastInsertId();

    // Respuesta exitosa
    echo json_encode([
        "success" => true,
        "message" => "¡Registro exitoso! Bienvenido/a",
        "data" => [
            "id" => (int)$id,
            "nombre" => $nombre,
            "email" => $email,
            "telefono" => $telefono,
            "direccion" => $direccion
        ]
    ]);

} catch (PDOException $e) {
    error_log("Register Error: " . $e->getMessage());
    
    // Mensaje amigable sin exponer detalles técnicos
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo json_encode(["success" => false, "message" => "El email ya existe en el sistema"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al registrar. Intenta nuevamente."]);
    }
    
} catch (Exception $e) {
    error_log("General Register Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error interno del servidor"]);
}
?>