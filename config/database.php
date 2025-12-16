<?php


// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'cna_upholstery');
define('DB_USER', 'root'); 
define('DB_PASS', ''); 

// Configuración de zona horaria
date_default_timezone_set('America/New_York');

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * manejar la conexión a la base de datos
 */
class Database {
    private static $connection = null;
    
    /**
     * Obtener conexión PDO a la base de datos
     * @return PDO|null
     */
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
                
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch (PDOException $e) {
                error_log("Error de conexión a la base de datos: " . $e->getMessage());
                die("Error de conexión a la base de datos. Por favor, verifica la configuración.");
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Cerrar conexión
     */
    public static function closeConnection() {
        self::$connection = null;
    }
    
    /**
     * Probar la conexión a la base de datos
     * @return bool
     */
    public static function testConnection() {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 * Función auxiliar para obtener conexión rápidamente
 * @return PDO|null
 */
function getDB() {
    return Database::getConnection();
}

// Probar conexión automáticamente al incluir este archivo
if (!Database::testConnection()) {
    die("No se pudo conectar a la base de datos. Verifica la configuración en config/database.php");
}
?>