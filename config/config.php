<?php
/**
 * Configuración principal del sistema CNA Upholstery
 * Variables globales, configuraciones y constantes
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';



// URLs base
define('BASE_URL', 'http://localhost/cna-upholstery-system/');
define('ASSETS_URL', BASE_URL . 'assets/');

// Rutas del sistema
define('ROOT_PATH', __DIR__ . '/../');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');

// Configuración de impuestos
define('TAX_RATE', 0.06625); // 6.625% como en los ejemplos
define('TAX_LABEL', 'Tax (6.625%)');

// Configuración de documentos PDF
define('PDF_LOGO_PATH', ASSETS_PATH . 'images/logo.png');
define('PDF_FONT_SIZE', 10);
define('PDF_PAGE_MARGIN', 20);

// Configuración de WhatsApp
define('WHATSAPP_DEFAULT_MESSAGE_INVOICE', 'Hola! Te envío la factura de CNA Upholstery. Puedes verla en: ');
define('WHATSAPP_DEFAULT_MESSAGE_ESTIMATE', 'Hola! Te envío el presupuesto de CNA Upholstery. Puedes verlo en: ');

// Estados de documentos
define('DOCUMENT_STATUS', [
    'pendiente' => 'Pendiente',
    'pagado' => 'Pagado',
    'confirmado' => 'Confirmado'
]);

// Tipos de documentos
define('DOCUMENT_TYPES', [
    'invoice' => 'Factura',
    'estimate' => 'Presupuesto'
]);

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Configuración de fecha
define('DATE_FORMAT', 'Y-m-d');
define('DATE_FORMAT_DISPLAY', 'd/m/Y');
define('DATETIME_FORMAT_DISPLAY', 'd/m/Y H:i');

/**
 * Función para obtener la URL base
 * @param string $path
 * @return string
 */
function getUrl($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Función para obtener ruta de assets
 * @param string $path
 * @return string
 */
function getAssetUrl($path = '') {
    return ASSETS_URL . ltrim($path, '/');
}

/**
 * Función para incluir header HTML
 * @param string $title
 * @param array $additionalCss
 * @param array $additionalJs
 */
function includeHeader($title = 'CNA Upholstery', $additionalCss = [], $additionalJs = []) {
    ?>
    <!DOCTYPE html>
    <html lang="es" class="h-full">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?></title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- TailwindCSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Font Awesome para iconos -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- CSS personalizado -->
        <style>
            body { font-family: 'Inter', sans-serif; }
            .company-logo { 
                background: linear-gradient(135deg, #d4a574 0%, #c4956a 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .btn-primary {
                @apply bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200;
            }
            .btn-secondary {
                @apply bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200;
            }
            .btn-success {
                @apply bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200;
            }
            .btn-danger {
                @apply bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200;
            }
            .btn-warning {
                @apply bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200;
            }
            .input-field {
                @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent;
            }
            .table-row:hover {
                @apply bg-gray-50;
            }
        </style>
        
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
        
        <!-- Configuración TailwindCSS personalizada -->
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            'company-gold': '#d4a574',
                            'company-dark': '#2c3e50'
                        }
                    }
                }
            }
        </script>
        
        <?php foreach ($additionalJs as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    </head>
    <body class="bg-gray-100 h-full">
    <?php
}

/**
 * Función para incluir footer HTML
 */
function includeFooter() {
    ?>
        <!-- Scripts globales -->
        <script>
            // Función para confirmar eliminación
            function confirmDelete(message = '¿Estás seguro de que quieres eliminar este elemento?') {
                return confirm(message);
            }
            
            // Función para formatear precio mientras se escribe
            function formatPriceInput(input) {
                let value = input.value.replace(/[^\d.]/g, '');
                if (value) {
                    let parts = value.split('.');
                    if (parts.length > 2) {
                        parts = [parts[0], parts[1]];
                    }
                    if (parts[1] && parts[1].length > 2) {
                        parts[1] = parts[1].substring(0, 2);
                    }
                    value = parts.join('.');
                }
                input.value = value;
            }
            
            // Función para calcular totales automáticamente
            function calculateRowTotal(row) {
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
                const total = quantity * unitPrice;
                
                row.querySelector('.row-total').textContent = '$' + total.toFixed(2);
                calculateDocumentTotals();
            }
            
            // Función para calcular totales del documento
            function calculateDocumentTotals() {
                let subtotal = 0;
                document.querySelectorAll('.row-total').forEach(function(element) {
                    const value = parseFloat(element.textContent.replace('$', '')) || 0;
                    subtotal += value;
                });
                
                const tax = subtotal * <?php echo TAX_RATE; ?>;
                const total = subtotal + tax;
                
                // Actualizar elementos en pantalla
                const subtotalElement = document.getElementById('subtotal');
                const taxElement = document.getElementById('tax');
                const totalElement = document.getElementById('total');
                
                if (subtotalElement) subtotalElement.textContent = '$' + subtotal.toFixed(2);
                if (taxElement) taxElement.textContent = '$' + tax.toFixed(2);
                if (totalElement) totalElement.textContent = '$' + total.toFixed(2);
                
                // Actualizar campos ocultos si existen
                const subtotalInput = document.getElementById('subtotal_input');
                const taxInput = document.getElementById('tax_input');
                const totalInput = document.getElementById('total_input');
                
                if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
                if (taxInput) taxInput.value = tax.toFixed(2);
                if (totalInput) totalInput.value = total.toFixed(2);
            }
            
            // Auto-ocultar alertas después de 5 segundos
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(function(alert) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.remove();
                        }, 500);
                    });
                }, 5000);
            });
        </script>
    </body>
    </html>
    <?php
}

/**
 * Función para generar breadcrumbs
 * @param array $links
 */
function showBreadcrumbs($links) {
    echo '<nav class="flex mb-6" aria-label="Breadcrumb">';
    echo '<ol class="inline-flex items-center space-x-1 md:space-x-3">';
    
    foreach ($links as $index => $link) {
        $isLast = ($index === count($links) - 1);
        
        echo '<li class="inline-flex items-center">';
        
        if ($index > 0) {
            echo '<svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                  </svg>';
        }
        
        if ($isLast) {
            echo '<span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">' . htmlspecialchars($link['text']) . '</span>';
        } else {
            echo '<a href="' . htmlspecialchars($link['url']) . '" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">' . htmlspecialchars($link['text']) . '</a>';
        }
        
        echo '</li>';
    }
    
    echo '</ol>';
    echo '</nav>';
}

// Definir zona horaria por defecto
date_default_timezone_set('America/New_York');
?>
