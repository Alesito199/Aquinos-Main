<?php


require_once __DIR__ . '/../config/database.php';

/**
 * Generar número de documento único
 * @param string $tipo 'invoice' o 'estimate'
 * @return string
 */
/**
 * Verificar si el usuario está autenticado como administrador con seguridad avanzada
 */
// Agrega esto a config/functions.php

function getBaseURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $path = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    return $protocol . $host . $path;
}
function requireAdmin() {
    require_once __DIR__ . '/../config/security.php';
    
    // Verificar si está logueado
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $lang = $_SESSION['lang'] ?? 'en';
        header("Location: " . getBaseURL() . "login.php?lang=" . $lang);
        exit;
    }
    
    // Verificar expiración de sesión
    if (!isset($_SESSION['login_time']) || (time() - $_SESSION['login_time']) > SESSION_LIFETIME) {
        logSecurityEvent('SESSION_EXPIRED', ['username' => $_SESSION['admin_username'] ?? 'unknown']);
        session_destroy();
        $lang = $_SESSION['lang'] ?? 'en';
        header("Location: " . getBaseURL() . "login.php?lang=" . $lang . "&expired=1");
        exit;
    }
    
    // Verificar consistencia de IP (opcional - comentar si el admin cambia de ubicación)
    /*
    if (isset($_SESSION['login_ip']) && $_SESSION['login_ip'] !== getRealUserIP()) {
        logSecurityEvent('IP_CHANGE_DETECTED', [
            'username' => $_SESSION['admin_username'] ?? 'unknown',
            'original_ip' => $_SESSION['login_ip'],
            'current_ip' => getRealUserIP()
        ]);
        session_destroy();
        header("Location: " . getBaseURL() . "login.php?security=1");
        exit;
    }
    */
    
    // Renovar tiempo de sesión
    $_SESSION['login_time'] = time();
    
    // Headers de seguridad
    performSecurityChecks();
}

/**
 * Crear logout seguro
 */
function secureLogout() {
    session_start();
    
    if (isset($_SESSION['admin_username'])) {
        logSecurityEvent('USER_LOGOUT', ['username' => $_SESSION['admin_username']]);
    }
    
    session_destroy();
    
    // Limpiar cookies
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}
function generateDocumentNumber($tipo = 'estimate')
{
    $pdo = getDB();
    $prefix = $tipo === 'estimate' ? 'EST' : 'INV';
    $yearMonth = date('Ym'); // Por ejemplo: 202508

    // Busca el último correlativo usado este mes para este tipo
    $stmt = $pdo->prepare("
        SELECT numero_documento 
        FROM documentos 
        WHERE tipo = ? AND numero_documento LIKE ? 
        ORDER BY id DESC LIMIT 1
    ");
    $like = "{$prefix}-{$yearMonth}-%";
    $stmt->execute([$tipo, $like]);
    $lastNumber = $stmt->fetchColumn();

    if ($lastNumber) {
        $parts = explode('-', $lastNumber);
        $correlativo = isset($parts[2]) ? (int)$parts[2] + 1 : 1;
    } else {
        $correlativo = 1;
    }

    $number = sprintf('%s-%s-%04d', $prefix, $yearMonth, $correlativo);
    return $number;
}

/**
 * Limpiar y validar entrada de datos
 * @param string $data
 * @return string
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validar email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar teléfono (formato flexible)
 * @param string $phone
 * @return bool
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

/**
 * Formatear precio para mostrar
 * @param float $price
 * @return string
 */
function formatPrice($price) {
    return '$' . number_format((float)$price, 2, '.', ',');
}

/**
 * Calcular impuesto (6.625% como en los ejemplos)
 * @param float $subtotal
 * @return float
 */
function calculateTax($subtotal) {
    return round($subtotal * 0.06625, 2);
}

/**
 * Obtener todos los clientes
 * @return array
 */
function getAllClients() {
    try {
        $pdo = getDB();
        $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nombre ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error obteniendo clientes: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener cliente por ID
 * @param int $id
 * @return array|null
 */
function getClientById($id) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error obteniendo cliente: " . $e->getMessage());
        return null;
    }
}

/**
 * Crear o actualizar cliente
 * @param array $data
 * @param int|null $id
 * @return bool
 */
function saveClient($data, $id = null) {
    try {
        $pdo = getDB();
        
        if ($id) {
            // Actualizar cliente existente
            $stmt = $pdo->prepare("UPDATE clientes SET nombre = ?, telefono = ?, email = ?, direccion = ? WHERE id = ?");
            return $stmt->execute([
                cleanInput($data['nombre']),
                cleanInput($data['telefono']),
                cleanInput($data['email']),
                cleanInput($data['direccion']),
                $id
            ]);
        } else {
            // Crear nuevo cliente
            $stmt = $pdo->prepare("INSERT INTO clientes (nombre, telefono, email, direccion) VALUES (?, ?, ?, ?)");
            return $stmt->execute([
                cleanInput($data['nombre']),
                cleanInput($data['telefono']),
                cleanInput($data['email']),
                cleanInput($data['direccion'])
            ]);
        }
    } catch (Exception $e) {
        error_log("Error guardando cliente: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener documento con items
 * @param int $id
 * @return array|null
 */
function getDocumentWithItems($id) {
    try {
        $pdo = getDB();
        
        // Obtener documento principal
        $stmt = $pdo->prepare("
            SELECT d.*, c.nombre as cliente_nombre, c.telefono, c.email, c.direccion 
            FROM documentos d 
            LEFT JOIN clientes c ON d.id_cliente = c.id 
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        $document = $stmt->fetch();
        
        if (!$document) {
            return null;
        }
        
        // Obtener items del documento
        $stmt = $pdo->prepare("SELECT * FROM items_documento WHERE id_documento = ? ORDER BY id");
        $stmt->execute([$id]);
        $document['items'] = $stmt->fetchAll();
        
        return $document;
        
    } catch (Exception $e) {
        error_log("Error obteniendo documento: " . $e->getMessage());
        return null;
    }
}

/**
 * Generar link de WhatsApp
 * @param string $phone
 * @param string $message
 * @return string
 */
function generateWhatsAppLink($phone, $message) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (!str_starts_with($phone, '1')) {
        $phone = '1' . $phone; // Agregar código de país USA si no existe
    }
    
    $encodedMessage = urlencode($message);
    return "https://wa.me/{$phone}?text={$encodedMessage}";
}

/**
 * Mostrar alerta JavaScript
 * @param string $message
 * @param string $type ('success', 'error', 'warning', 'info')
 */
function showAlert($message, $type = 'info') {
    $alertClass = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700'
    ];
    
    $class = $alertClass[$type] ?? $alertClass['info'];
    
    echo "<div class='alert {$class} px-4 py-3 rounded mb-4 border' role='alert'>
            <span class='block sm:inline'>{$message}</span>
          </div>";
}

/**
 * Redireccionar con mensaje
 * @param string $url
 * @param string $message
 * @param string $type
 */
function redirect($url, $message = '', $type = 'info') {
    if ($message) {
        $_SESSION['alert_message'] = $message;
        $_SESSION['alert_type'] = $type;
    }
    header("Location: {$url}");
    exit();
}

/**
 * Mostrar y limpiar alertas de sesión
 */
function displaySessionAlert() {
    if (isset($_SESSION['alert_message'])) {
        showAlert($_SESSION['alert_message'], $_SESSION['alert_type'] ?? 'info');
        unset($_SESSION['alert_message'], $_SESSION['alert_type']);
    }
}
function logSecurityEvent($event, $data = []) {
    // Guarda eventos en un archivo de logs (puedes cambiar por base de datos si lo prefieres)
    $logfile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logfile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $entry = date('Y-m-d H:i:s') . " | {$event} | " . json_encode($data) . PHP_EOL;
    @file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);
}
/**
 * Verificar si una fecha es válida
 * @param string $date
 * @param string $format
 * @return bool
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
// Agregar al final del archivo config/functions.php

/**
 * Calcular total de una fila
 */
function calculateRowTotal($row) {
    echo "
    <script>
    function calculateRowTotal(row) {
        const cantidad = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const precioUnitario = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const total = cantidad * precioUnitario;
        
        row.querySelector('.row-total').textContent = '$' + total.toFixed(2);
        calculateDocumentTotals();
    }

    function calculateDocumentTotals() {
        let subtotal = 0;
        
        document.querySelectorAll('.item-row').forEach(function(row) {
            const cantidad = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const precioUnitario = parseFloat(row.querySelector('.unit-price-input').value) || 0;
            const total = cantidad * precioUnitario;
            subtotal += total;
        });
        
        const taxRate = " . (TAX_RATE / 100) . ";
        const tax = subtotal * taxRate;
        const total = subtotal + tax;
        
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('tax').textContent = '$' + tax.toFixed(2);
        document.getElementById('total').textContent = '$' + total.toFixed(2);
    }
    </script>
    ";
}
?>