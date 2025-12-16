<?php
/**
 * Configuración de seguridad avanzada
 * CNA Upholstery System
 */

// Datos del administrador
const ADMIN_USERNAME = 'cnaupholstery0@gmail.com';
const ADMIN_PASSWORD_HASH = '$argon2id$v=19$m=65536,t=4,p=3$eVRRZ1dMa2ptWk8ydjlVTA$bDANM4fo0EkM9jsEAEVL6cKOAi5tT0aFuGDV1vE48Ac'; // Hash para: Claudio@quino!@!Yegros

// Configuración de seguridad
const MAX_LOGIN_ATTEMPTS = 3;           // Máximo 3 intentos de login
const LOCKOUT_TIME = 1800;              // 30 minutos de bloqueo
const SESSION_LIFETIME = 7200;          // 2 horas de sesión activa
const CSRF_TOKEN_LIFETIME = 3600;       // 1 hora para tokens CSRF

// IPs permitidas
const ALLOWED_IPS = [
];

// User Agent esperado 
const EXPECTED_USER_AGENTS = [
    'Mozilla',
    'Chrome',
    'Firefox',
    'Safari',
    'Edge'
];

/**
 * Generar hash seguro para contraseña
 * Usar esto para generar nuevos hashes de contraseña
 */
function generateSecureHash($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // 64 MB
        'time_cost' => 4,       // 4 iteraciones
        'threads' => 3          // 3 hilos
    ]);
}

/**
 * Verificar contraseña con hash seguro
 */
function verifySecurePassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Verificar si la IP está permitida
 */
function isIPAllowed($ip) {
    if (empty(ALLOWED_IPS)) {
        return true; // Si no hay restricciones, permitir todas
    }
    return in_array($ip, ALLOWED_IPS);
}

/**
 * Obtener IP real del cliente
 */
function getRealUserIP() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Verificar User Agent válido
 */
function isValidUserAgent($userAgent) {
    if (empty($userAgent)) return false;
    
    foreach (EXPECTED_USER_AGENTS as $expected) {
        if (strpos($userAgent, $expected) !== false) {
            return true;
        }
    }
    return false;
}



/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    if ((time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verificar si IP está bloqueada por intentos fallidos
 */
function isIPBlocked($ip) {
    $blockFile = __DIR__ . '/../logs/blocked_ips.json';
    
    if (!file_exists($blockFile)) {
        return false;
    }
    
    $blockedIPs = json_decode(file_get_contents($blockFile), true) ?: [];
    
    if (!isset($blockedIPs[$ip])) {
        return false;
    }
    
    $blockData = $blockedIPs[$ip];
    
    // Si ha pasado el tiempo de bloqueo, desbloquear
    if ((time() - $blockData['blocked_at']) > LOCKOUT_TIME) {
        unset($blockedIPs[$ip]);
        file_put_contents($blockFile, json_encode($blockedIPs), LOCK_EX);
        return false;
    }
    
    return true;
}

/**
 * Registrar intento de login fallido
 */
function registerFailedLogin($ip) {
    $blockFile = __DIR__ . '/../logs/blocked_ips.json';
    $blockedIPs = [];
    
    if (file_exists($blockFile)) {
        $blockedIPs = json_decode(file_get_contents($blockFile), true) ?: [];
    }
    
    if (!isset($blockedIPs[$ip])) {
        $blockedIPs[$ip] = ['attempts' => 0, 'blocked_at' => 0];
    }
    
    $blockedIPs[$ip]['attempts']++;
    
    // Si excede el máximo de intentos, bloquear IP
    if ($blockedIPs[$ip]['attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $blockedIPs[$ip]['blocked_at'] = time();
        logSecurityEvent('IP_BLOCKED', ['ip' => $ip, 'attempts' => $blockedIPs[$ip]['attempts']]);
    }
    
    $logDir = dirname($blockFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    @file_put_contents($blockFile, json_encode($blockedIPs), LOCK_EX);
}

/**
 * Limpiar intentos fallidos de IP (después de login exitoso)
 */
function clearFailedLogins($ip) {
    $blockFile = __DIR__ . '/../logs/blocked_ips.json';
    
    if (file_exists($blockFile)) {
        $blockedIPs = json_decode(file_get_contents($blockFile), true) ?: [];
        unset($blockedIPs[$ip]);
        @file_put_contents($blockFile, json_encode($blockedIPs), LOCK_EX);
    }
}

/**
 * Verificaciones de seguridad completas
 */
function performSecurityChecks() {
    $ip = getRealUserIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Verificar IP permitida
    if (!isIPAllowed($ip)) {
        logSecurityEvent('BLOCKED_IP_ACCESS', ['ip' => $ip]);
        http_response_code(403);
        die('Access denied from this IP address.');
    }
    
    // Verificar si IP está bloqueada
    if (isIPBlocked($ip)) {
        logSecurityEvent('BLOCKED_IP_ATTEMPT', ['ip' => $ip]);
        http_response_code(429);
        die('Too many failed login attempts. Please try again later.');
    }
    
    // Verificar User Agent válido
    if (!isValidUserAgent($userAgent)) {
        logSecurityEvent('SUSPICIOUS_USER_AGENT', ['user_agent' => $userAgent]);
        // No bloquear, solo registrar
    }
    
    // Headers de seguridad
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://wa.me https://cdn.jsdelivr.net; img-src 'self' https://drive.google.com https://www.dropbox.com https://dl.dropboxusercontent.com https://images.unsplash.com https://i.imgur.com data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;");
}


?>