<?php
// config/db_connect.php - CONEXIÓN OPTIMIZADA PARA RAILWAY

// ============================================
// HACK PARA EVITAR BLOQUEOS + CORS
// ============================================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, User-Agent, Accept, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

// Headers adicionales para evitar bloqueos
header("X-Requested-With: XMLHttpRequest");
header("User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Pequeño delay opcional (solo si es necesario)
// usleep(500000); // 0.5 segundos

// ============================================
// CONFIGURACIÓN RAILWAY - TUS DATOS
// ============================================
function getDBConnection() {
    // DATOS DE TU RAILWAY (los mismos que usaste en MySQL Workbench)
    $host = "shinkansen.proxy.rlwy.net";
    $port = "14666";  // PUERTO ESPECÍFICO de Railway
    $dbname = "dragonbite";  // Tu base de datos
    $username = "root";  // Usuario
    $password = "epXevObuJmPjsVklypoDvJMvYFbnbQlO";  // Contraseña
    
    try {
        // CONEXIÓN CON PUERTO (IMPORTANTE para Railway)
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,  // Mejor sin persistente en Railway
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Para SSL si hay problemas
        ];
        
        // Crear conexión
        $pdo = new PDO($dsn, $username, $password, $options);
        
        // Configurar zona horaria (ajusta según tu país)
        $pdo->exec("SET time_zone = '-05:00';");  // Colombia/Perú/México
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log seguro sin exponer datos sensibles
        error_log("RAILWAY DB ERROR [".date("Y-m-d H:i:s")."]: " . $e->getMessage());
        
        // Respuesta segura para el cliente
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error de conexión con el servidor",
            "hint" => "Contacta al administrador"
        ]);
        exit();
    }
}

// Retornar conexión por defecto
return getDBConnection();
?>