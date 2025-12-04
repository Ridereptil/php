<?php
// config/db_connect.php - CONEXIÓN PARA RAILWAY

// ============================================
// HEADERS PARA CORS (Android/Web)
// ============================================
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS - RAILWAY
// ============================================
function getDBConnection() {
    // 1. DATOS DE RAILWAY (variables de entorno)
    $host = getenv('MYSQLHOST') ?: 'shinkansen.proxy.rlwy.net';
    $port = getenv('MYSQLPORT') ?: '14666';
    $dbname = getenv('MYSQLDATABASE') ?: 'dragonbite';
    $username = getenv('MYSQLUSER') ?: 'root';
    $password = getenv('MYSQLPASSWORD') ?: 'epXevObuJmPjsVklypoDvJMvYFbnbQlO';
    
    // 2. VALORES POR DEFECTO (backup)
    if (empty($host)) $host = 'shinkansen.proxy.rlwy.net';
    if (empty($port)) $port = '14666';
    if (empty($dbname)) $dbname = 'dragonbite';
    if (empty($username)) $username = 'root';
    if (empty($password)) $password = 'epXevObuJmPjsVklypoDvJMvYFbnbQlO';
    
    try {
        // 3. CREAR DSN (Data Source Name)
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        
        // 4. OPCIONES DE PDO
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // Lanzar excepciones en errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Retornar arrays asociativos
            PDO::ATTR_EMULATE_PREPARES => false,                  // Usar prepared statements nativos
            PDO::ATTR_PERSISTENT => false,                        // No conexiones persistentes
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,      // Para SSL si hay problemas
        ];
        
        // 5. CREAR CONEXIÓN
        $pdo = new PDO($dsn, $username, $password, $options);
        
        // 6. CONFIGURACIÓN ADICIONAL
        $pdo->exec("SET time_zone = '-05:00';"); // Zona horaria (ajustar según tu país)
        
        // 7. RETORNAR CONEXIÓN
        return $pdo;
        
    } catch (PDOException $e) {
        // 8. MANEJO DE ERRORES (seguro, sin exponer detalles)
        error_log("RAILWAY DB ERROR [" . date("Y-m-d H:i:s") . "]: " . $e->getMessage());
        
        // 9. RESPUESTA DE ERROR PARA EL CLIENTE
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error de conexión a la base de datos",
            "hint" => "Contacta al administrador",
            "timestamp" => date("Y-m-d H:i:s")
        ]);
        exit();
    }
}

// ============================================
// INICIALIZAR CONEXIÓN GLOBAL
// ============================================
$conn = getDBConnection();

// ============================================
// VERIFICACIÓN DE CONEXIÓN (opcional)
// ============================================
try {
    $conn->query("SELECT 1");
} catch (PDOException $e) {
    error_log("DB Health Check Failed: " . $e->getMessage());
}
?>