<?php
/**
 * Sistema de autenticación con seguridad avanzada
 * CNA Upholstery System
 */

session_start();
require_once 'config/security.php';
require_once 'includes/functions.php';
// Realizar verificaciones de seguridad
performSecurityChecks();

// Si ya está logueado, redirigir al admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Verificar si la sesión no ha expirado
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_LIFETIME) {
        session_destroy();
        session_start();
        $error_message = 'Session expired. Please login again.';
    } else {
        header('Location: admin/index.php');
        exit;
    }
}

$error_message = '';
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
$_SESSION['lang'] = $lang;

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = getRealUserIP();
    
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => $ip]);
        $error_message = $lang === 'es' ? 'Token de seguridad inválido. Recarga la página.' : 'Invalid security token. Please reload the page.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Verificar datos básicos
        if (empty($username) || empty($password)) {
            $error_message = $lang === 'es' ? 'Usuario y contraseña son requeridos' : 'Username and password are required';
            logSecurityEvent('EMPTY_LOGIN_ATTEMPT', ['ip' => $ip, 'username' => $username]);
        }
        // Verificar credenciales
        elseif ($username === ADMIN_USERNAME && verifySecurePassword($password, ADMIN_PASSWORD_HASH)) {
            // Login exitoso
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['login_time'] = time();
            $_SESSION['login_ip'] = $ip;
            $_SESSION['login_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Limpiar intentos fallidos
            clearFailedLogins($ip);
            
            // Registrar login exitoso
            logSecurityEvent('SUCCESSFUL_LOGIN', ['username' => $username, 'ip' => $ip]);
            
            header('Location: admin/index.php');
            exit;
        } else {
            // Login fallido
            $error_message = $lang === 'es' ? 'Usuario o contraseña incorrectos' : 'Incorrect username or password';
            
            // Registrar intento fallido
            registerFailedLogin($ip);
            logSecurityEvent('FAILED_LOGIN_ATTEMPT', [
                'username' => $username,
                'ip' => $ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
            // Delay para prevenir ataques de fuerza bruta
            sleep(2);
        }
    }
}

// Generar token CSRF
$csrfToken = generateCSRFToken();

// Textos multiidioma (mismo código anterior)
$texts = [
    'en' => [
        'title' => 'Admin Login - CNA Upholstery',
        'welcome' => 'Administrator Access',
        'subtitle' => 'Enter your credentials to access the system',
        'username' => 'Username',
        'password' => 'Password',
        'login' => 'Login',
        'back' => 'Back to Home',
        'footer' => 'CNA Upholstery System - Professional upholstery services',
    ],
    'es' => [
        'title' => 'Login Administrador - CNA Upholstery',
        'welcome' => 'Acceso de Administrador',
        'subtitle' => 'Ingrese sus credenciales para acceder al sistema',
        'username' => 'Usuario',
        'password' => 'Contraseña',
        'login' => 'Iniciar Sesión',
        'back' => 'Volver al Inicio',
        'footer' => 'Sistema CNA Upholstery - Servicios profesionales de tapicería',
    ]
];

$t = $texts[$lang];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'company-gold': '#d4a574',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
    
    <!-- Language Switcher -->
    <div class="absolute top-4 right-4">
        <div class="flex space-x-2">
            <a href="?lang=en" class="px-3 py-1 rounded text-sm <?php echo $lang === 'en' ? 'bg-company-gold text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600'; ?>">EN</a>
            <a href="?lang=es" class="px-3 py-1 rounded text-sm <?php echo $lang === 'es' ? 'bg-company-gold text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600'; ?>">ES</a>
        </div>
    </div>

    <div class="max-w-md w-full space-y-8 p-8">
        <div class="text-center">
            <!-- Logo -->
            <div class="mx-auto h-20 w-20 bg-company-gold rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-shield-alt text-3xl text-gray-900"></i>
            </div>
            
            <h2 class="text-3xl font-extrabold text-white mb-2">
                <?php echo $t['welcome']; ?>
            </h2>
            <p class="text-gray-400 mb-2">
                <?php echo $t['subtitle']; ?>
            </p>
        </div>
        
        <div class="bg-gray-800 rounded-lg shadow-xl p-8">
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-600 text-white p-3 rounded mb-6 text-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6" onsubmit="return validateForm()">
                <!-- Token CSRF oculto -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                        <?php echo $t['username']; ?>
                    </label>
                    <input type="text" name="username" id="username" required maxlength="50"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-company-gold focus:border-transparent"
                           placeholder="<?php echo $t['username']; ?>"
                           autocomplete="username">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        <?php echo $t['password']; ?>
                    </label>
                    <input type="password" name="password" id="password" required maxlength="100"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-company-gold focus:border-transparent"
                           placeholder="<?php echo $t['password']; ?>"
                           autocomplete="current-password">
                </div>
                
                <button type="submit" id="loginBtn"
                        class="w-full bg-company-gold hover:bg-yellow-600 text-gray-900 font-semibold py-2 px-4 rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-company-gold focus:ring-offset-2 focus:ring-offset-gray-800">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    <?php echo $t['login']; ?>
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="index.php?lang=<?php echo $lang; ?>" 
                   class="text-company-gold hover:text-yellow-400 text-sm transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <?php echo $t['back']; ?>
                </a>
            </div>
        </div>
        
        <div class="text-center text-gray-500 text-xs">
            <?php echo $t['footer']; ?>
        </div>
    </div>

    <script>
        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const btn = document.getElementById('loginBtn');
            
            if (username.length === 0 || password.length === 0) {
                alert('<?php echo $lang === "es" ? "Por favor complete todos los campos" : "Please fill in all fields"; ?>');
                return false;
            }
            
            if (username.length > 50 || password.length > 100) {
                alert('<?php echo $lang === "es" ? "Datos demasiado largos" : "Input too long"; ?>');
                return false;
            }
            
            // Disable button to prevent double submission
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><?php echo $lang === "es" ? "Verificando..." : "Verifying..."; ?>';
            
            return true;
        }
        
        // Re-enable button if form submission fails
        window.addEventListener('load', function() {
            const btn = document.getElementById('loginBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i><?php echo $t['login']; ?>';
        });
    </script>
</body>
</html>