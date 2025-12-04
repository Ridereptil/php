<?php
// test.php - Para verificar que funciona
header("Content-Type: application/json");

try {
    // Intenta conectar a MySQL (usando variables de Railway)
    $host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
    $port = getenv('MYSQLPORT') ?: '3306';
    
    $response = [
        "status" => "online",
        "service" => "PHP Backend",
        "mysql_host" => $host,
        "mysql_port" => $port,
        "timestamp" => date("Y-m-d H:i:s"),
        "message" => "✅ Backend funcionando en Railway"
    ];
    
    // Intentar conexión MySQL
    if ($socket = @fsockopen($host, $port, $errno, $errstr, 2)) {
        $response["mysql"] = "connected";
        fclose($socket);
    } else {
        $response["mysql"] = "disconnected";
        $response["mysql_error"] = "$errstr ($errno)";
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>