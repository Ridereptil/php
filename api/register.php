<?php
// api/register.php - REGISTRO DE USUARIOS PARA DRAGONBITE

// ============================================
// HEADERS OBLIGATORIOS
// ============================================
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// INCLUIR CONEXIÓN A BASE DE DATOS
// ============================================
// ⚠️ IMPORTANTE: Usa __DIR__ para rutas absolutas
require_once __DIR__ . '/../config/db_connect.php';

// ============================================
// LEER Y VALIDAR JSON DE ENTRADA
// ============================================
$raw_input = file_get_contents("php://input");

// Verificar que se recibió data
if (empty($raw_input)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "No se recibieron datos"
    ]);
    exit();
}

// Decodificar JSON
$data = json_decode($raw_input, true);

// Validar JSON
if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "JSON inválido o mal formado"
    ]);
    exit();
}

// ============================================
// OBTENER Y LIMPIAR DATOS
// ============================================
$nombre = trim($data["nombre"] ?? "");
$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");
$telefono = trim($data["telefono"] ?? "");
$direccion = trim($data["direccion"] ?? "");

// ============================================
// VALIDACIÓN DE DATOS
// ============================================
$errors = [];

if (empty($nombre) || strlen($nombre) < 2) {
    $errors[] = "El nombre debe tener al menos 2 caracteres";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email inválido";
}

if (empty($password) || strlen($password) < 6) {
    $errors[] = "La contraseña debe tener al menos 6 caracteres";
}

if (empty($telefono) || strlen($telefono) < 8) {
    $errors[] = "Teléfono inválido";
}

// Si hay errores, retornarlos
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => implode(", ", $errors)
    ]);
    exit();
}

// ============================================
// PROCESAR REGISTRO EN BASE DE DATOS
// ============================================
try {
    // 1. Verificar si el email ya existe
    $check_email_sql = "SELECT id FROM usuarios WHERE email = :email LIMIT 1";
    $check_stmt = $conn->prepare($check_email_sql);
    $check_stmt->execute([':email' => $email]);
    
    if ($check_stmt->rowCount() > 0) {
        http_response_code(409); // Conflict
        echo json_encode([
            "success" => false,
            "message" => "El email ya está registrado"
        ]);
        exit();
    }

    // 2. Hash de contraseña (SEGURIDAD)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 3. Insertar nuevo usuario
    $insert_sql = "INSERT INTO usuarios (nombre, email, password, telefono, direccion, fecha_registro) 
                   VALUES (:nombre, :email, :password, :telefono, :direccion, NOW())";
    
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->execute([
        ':nombre' => $nombre,
        ':email' => $email,
        ':password' => $hashed_password,
        ':telefono' => $telefono,
        ':direccion' => $direccion
    ]);
    
    // 4. Obtener ID del nuevo usuario
    $user_id = $conn->lastInsertId();
    
    // 5. Respuesta exitosa
    http_response_code(201); // Created
    echo json_encode([
        "success" => true,
        "message" => "¡Usuario registrado exitosamente!",
        "data" => [
            "id" => (int)$user_id,
            "nombre" => $nombre,
            "email" => $email,
            "telefono" => $telefono,
            "direccion" => $direccion
        ]
    ]);
    
} catch (PDOException $e) {
    // Manejo de errores de base de datos
    error_log("Register PDO Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error en el servidor al registrar usuario",
        "error_code" => $e->getCode()
    ]);
    
} catch (Exception $e) {
    // Manejo de otros errores
    error_log("Register General Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor"
    ]);
}

// Cerrar conexión (opcional, PDO lo hace automáticamente)
$conn = null;
?>