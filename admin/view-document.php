<?php

/**
 * Visualizar documento (factura o presupuesto) - Bilingüe según idioma seleccionado
 * CNA Upholstery System
 */

require_once '../config/config.php';

// 1. Configuración de idioma seleccionado (por menú desplegable)
if (isset($_POST['lang']) && in_array($_POST['lang'], ['es', 'en'])) {
    $_SESSION['lang'] = $_POST['lang'];
}
$lang = $_SESSION['lang'] ?? 'es';

// 2. Función de traducción
function t($es, $en)
{
    global $lang;
    return $lang === 'en' ? $en : $es;
}

// Verificar que se proporcione ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php', t('Documento no encontrado', 'Document not found'), 'error');
}

$document_id = (int)$_GET['id'];

// Obtener documento con items
$document = getDocumentWithItems($document_id);

if (!$document) {
    redirect('index.php', t('Documento no encontrado', 'Document not found'), 'error');
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    try {
        $new_status = $_POST['new_status'];
        if (!in_array($new_status, ['pendiente', 'pagado', 'confirmado'])) {
            throw new Exception(t('Estado inválido', 'Invalid status'));
        }

        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE documentos SET estado = ? WHERE id = ?");
        $stmt->execute([$new_status, $document_id]);

        $status_text = DOCUMENT_STATUS[$new_status];
        redirect("view-document.php?id={$document_id}", t("Estado cambiado a: {$status_text}", "Status changed to: {$status_text}"), "success");
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Traducciones para los estados
$status_translate = [
    'pendiente' => t('Pendiente', 'Pending'),
    'pagado'    => t('Pagado', 'Paid'),
    'confirmado' => t('Confirmado', 'Confirmed'),
];

// Traducciones para tipo de documento
$doc_type_translate = [
    'invoice' => ['es' => 'Factura', 'en' => 'Invoice'],
    'estimate' => ['es' => 'Presupuesto', 'en' => 'Estimate'],
];

$page_title = ($document['tipo'] === 'invoice') ? t('Ver Factura', 'View Invoice') : t('Ver Presupuesto', 'View Estimate');
includeHeader($page_title);
?>

<!-- SweetAlert2 y FontAwesome -->

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

    <style>
        @media (max-width: 768px) {
            .responsive-doc-content {
                padding: 1rem !important;
            }

            .responsive-table {
                min-width: 400px !important;
                font-size: 0.95rem !important;
            }

            .responsive-header {
                flex-wrap: wrap !important;
            }

            .responsive-title {
                font-size: 1.2rem !important;
                word-break: break-word !important;
            }

            .responsive-panel {
                margin-top: 2rem !important;
            }
        }

        @media (max-width: 480px) {
            .responsive-table {
                min-width: 320px !important;
                font-size: 0.92rem !important;
            }

            .responsive-title {
                font-size: 1rem !important;
            }

            .responsive-panel {
                margin-top: 1rem !important;
            }
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            background: #f8fafc;
            color: #222;
            border-radius: 0.5rem;
            padding: 0.7rem 1rem;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            border: 1px solid #f0f0f0;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-action:hover,
        .btn-action:focus {
            background: #e0e7ef;
            color: #222;
            box-shadow: 0 2px 10px 0 rgba(80, 80, 120, 0.06);
            border-color: #d1d5db;
            text-decoration: none;
        }

        .btn-action-primary {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.7rem 1rem;
            border: none;
            transition: background 0.2s;
            cursor: pointer;
        }

        .btn-action-primary:hover,
        .btn-action-primary:focus {
            background: #1746a2;
            color: #fff;
            box-shadow: 0 2px 8px 0 rgba(40, 60, 180, 0.10);
        }

        .btn-action-danger {
            background: #fff;
            color: #d32f2f;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.7rem 1rem;
            border: 1px solid #ffeaea;
            transition: background 0.2s, color 0.2s;
            cursor: pointer;
        }

        .btn-action-danger:hover,
        .btn-action-danger:focus {
            background: #ffeaea;
            color: #b91c1c;
            box-shadow: 0 2px 8px 0 rgba(220, 20, 60, 0.05);
        }
    </style>
</head>
<div class="min-h-screen py-8 bg-gray-50">
    <div class="container mx-auto px-2 md:px-4">

        <!-- Header con acciones - RESPONSIVE CON MENÚ HAMBURGUESA -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <?php echo $page_title; ?> #<?php echo htmlspecialchars($document['numero_documento']); ?>
                    </h1>
                    <div class="flex items-center mt-2 space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            <?php echo $document['estado'] === 'pagado' ? 'bg-green-100 text-green-800' : ($document['estado'] === 'confirmado' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                            <?php echo $status_translate[$document['estado']] ?? ucfirst($document['estado']); ?>
                        </span>
                        <span class="text-sm text-gray-500">
                            <?php echo t('Fecha', 'Date'); ?>: <?php echo date('d/m/Y', strtotime($document['fecha'])); ?>
                        </span>
                    </div>
                </div>

                <!-- Desktop Actions -->
                <div class="hidden md:flex space-x-3">
                    <a href="index.php" class="btn-secondary flex items-center">
                        <i class="fas fa-list mr-2"></i><?php echo t('Lista', 'List'); ?>
                    </a>
                    <button onclick="window.print()" class="btn-primary flex items-center">
                        <i class="fas fa-print mr-2"></i><?php echo t('Imprimir', 'Print'); ?>
                    </button>
                    <a href="generate-image.php?id=<?php echo $document['id']; ?>" class="btn-success flex items-center">
                        <i class="fas fa-image mr-2"></i><?php echo t('Descargar Imagen', 'Download Image'); ?>
                    </a>
                    <form method="post" action="">
                        <select name="lang" onchange="this.form.submit()" class="bg-gray-900 text-white px-4 py-2 rounded shadow focus:outline-none">
                            <option value="es" <?php if ($lang === 'es') echo 'selected'; ?>>Español</option>
                            <option value="en" <?php if ($lang === 'en') echo 'selected'; ?>>English</option>
                        </select>
                    </form>
                </div>
                <!-- Hamburger Button for Mobile -->
                <button class="md:hidden text-gray-800 focus:outline-none px-2 py-2 rounded hover:bg-gray-200" onclick="toggleMobileHeaderMenu()" aria-label="Menú">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
            <!-- Mobile Actions -->
            <div id="headerMobileMenu" class="md:hidden mt-4 hidden flex flex-col gap-2">
                <a href="admin/index.php" class="btn-secondary flex items-center">
                    <i class="fas fa-list mr-2"></i><?php echo t('Lista', 'List'); ?>
                </a>
                <button onclick="window.print()" class="btn-primary flex items-center">
                    <i class="fas fa-print mr-2"></i><?php echo t('Imprimir', 'Print'); ?>
                </button>
                <a href="generate-image.php?id=<?php echo $document['id']; ?>" class="btn-success flex items-center">
                    <i class="fas fa-image mr-2"></i><?php echo t('Descargar Imagen', 'Download Image'); ?>
                </a>
                <form method="post" action="">
                    <select name="lang" onchange="this.form.submit()" class="bg-gray-900 text-white px-4 py-2 rounded shadow focus:outline-none">
                        <option value="es" <?php if ($lang === 'es') echo 'selected'; ?>>Español</option>
                        <option value="en" <?php if ($lang === 'en') echo 'selected'; ?>>English</option>
                    </select>
                </form>
            </div>
        </div>

        <?php displaySessionAlert(); ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Documento principal -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-x-auto responsive-doc-content" id="documentContent">
                    <!-- Header del documento -->
                    <div class="bg-white from-gray-900 to-gray-700 text-white px-8 py-6 rounded-t-lg flex flex-row items-start justify-between gap-8">
                        <!-- Empresa -->
                        <div class="flex flex-col min-w-[250px] max-w-[320px]">
                            <h2 class="flex items-center gap-2">
                                <span class="font-extrabold text-4xl tracking-wide text-company-gold">CNA</span>
                                <span class="block text-2xl font-bold text-company-gold">UPHOLSTERY</span>
                            </h2>
                            <div class="w-18 h-1 bg-company-gold my-3"></div>
                            <div class="text-sm space-y-1 text-company-gold">
                                <div><?php echo COMPANY_OWNER; ?></div>
                                <div><?php echo COMPANY_PHONE; ?></div>
                                <div><?php echo COMPANY_EMAIL; ?></div>
                                <div><?php echo COMPANY_ADDRESS; ?></div>
                            </div>
                        </div>
                        <!-- Presupuesto/Factura -->
                        <div class="flex flex-col items-end min-w-[200px] text-right">
                            <h3 class="text-3xl font-bold mb-2 text-company-gold">
                                <?php echo strtoupper($doc_type_translate[$document['tipo']][$lang]); ?>
                            </h3>
                            <span class="text-base font-medium mb-1 text-company-gold">
                                <?php
                                echo $doc_type_translate[$document['tipo']][$lang] . ' No. ';
                                preg_match('/(\d+)$/', $document['numero_documento'], $matches);
                                echo isset($matches[1]) ? (int)$matches[1] : '1';
                                ?>
                            </span>
                            <span class="text-sm mt-1 text-company-gold ">
                                <?php echo t('Fecha', 'Date'); ?>: <?php echo date('F j, Y', strtotime($document['fecha'])); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Información del cliente -->
                    <div class="p-8 responsive-doc-content ">
                        <div class="mb-8 bg-gray-100 p-4">
                            <h4 class="text-lg font-semibold mb-3"><?php echo t('FACTURAR A:', 'BILLED TO:'); ?></h4>
                            <div class="flex flex-wrap gap-y-3 gap-x-8">
                                <div class="flex flex-col min-w-[200px]">
                                    <span class="text-gray-700 font-semibold"><?php echo t('Nombre:', 'Name:'); ?></span>
                                    <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($document['cliente_nombre'] ?? t('Cliente no especificado', 'Client not specified')); ?></span>
                                </div>
                                <div class="flex flex-col min-w-[200px]">
                                    <span class="text-gray-700 font-semibold"><?php echo t('Dirección:', 'Address:'); ?></span>
                                    <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($document['direccion'] ?? ''); ?></span>
                                </div>
                                <div class="flex flex-col min-w-[200px]">
                                    <span class="text-gray-700 font-semibold"><?php echo t('Número de Teléfono:', 'Phone Number:'); ?></span>
                                    <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($document['telefono'] ?? ''); ?></span>
                                </div>
                                <div class="flex flex-col min-w-[200px]">
                                    <span class="text-gray-700 font-semibold"><?php echo t('Correo:', 'Email:'); ?></span>
                                    <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($document['email'] ?? ''); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Items del documento -->
                        <div class="mb-8 overflow-x-auto p-2">
                            <table class="w-full responsive-table">
                                <thead>
                                    <tr class="border-b-2 border-gray-200">
                                        <th class="text-left py-3 font-medium"><?php echo t('Item', 'Item'); ?></th>
                                        <th class="text-center py-3 font-medium w-20"><?php echo t('Cantidad', 'Quantity'); ?></th>
                                        <th class="text-right py-3 font-medium w-24"><?php echo t('Precio Unitario', 'Unit Price'); ?></th>
                                        <th class="text-right py-3 font-medium w-24"><?php echo t('Total', 'Total'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($document['items'] as $item): ?>
                                        <tr class="border-b border-gray-100">
                                            <td class="py-4">
                                                <div class="font-medium break-words"><?php echo htmlspecialchars($item['descripcion']); ?></div>
                                            </td>
                                            <td class="text-center py-4">
                                                <?php echo number_format($item['cantidad'], $item['cantidad'] == (int)$item['cantidad'] ? 0 : 2); ?>
                                            </td>
                                            <td class="text-right py-4">
                                                <?php echo formatPrice($item['precio_unitario']); ?>
                                            </td>
                                            <td class="text-right py-4 font-medium">
                                                <?php echo formatPrice($item['total']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totales -->
                        <div class="flex flex-col items-end mb-8">
                            <div class="w-full sm:w-64">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="font-medium"><?php echo t('Subtotal', 'Subtotal'); ?></span>
                                        <span><?php echo formatPrice($document['subtotal']); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium"><?php echo t('Impuesto', 'Tax'); ?> <?php echo TAX_LABEL; ?></span>
                                        <span><?php echo formatPrice($document['impuestos']); ?></span>
                                    </div>
                                    <div class="border-t pt-2">
                                        <div class="flex justify-between text-xl font-bold">
                                            <span><?php echo t('Total', 'Total'); ?></span>
                                            <span><?php echo formatPrice($document['total']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas (solo para presupuestos) -->
                        <?php if ($document['tipo'] === 'estimate' && !empty($document['notas'])): ?>
                            <div class="border-t pt-6 mb-8">
                                <h4 class="font-semibold mb-2"><?php echo t('Notas:', 'Notes:'); ?></h4>
                                <p class="text-gray-700 break-words"><?php echo nl2br(htmlspecialchars($document['notas'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Footer -->
                        <div class="border-t pt-6">
                            <div class="text-center text-gray-600">
                                <div class="text-2xl font-bold mb-2"><?php echo t('¡Gracias!', 'Thank you!'); ?></div>
                                <?php if ($document['tipo'] === 'invoice'): ?>
                                    <div class="font-semibold"><?php echo t('INFORMACIÓN DE PAGO', 'PAYMENT INFORMATION'); ?></div>
                                <?php endif; ?>
                                <div class="mt-2 space-y-1">
                                    <div><?php echo COMPANY_OWNER; ?></div>
                                    <div><?php echo COMPANY_PHONE; ?></div>
                                    <div><?php echo COMPANY_EMAIL; ?></div>
                                </div>
                                <?php if ($document['tipo'] === 'estimate'): ?>
                                    <div class="mt-4 text-right">
                                        <div class="font-bold text-lg"><?php echo COMPANY_NAME; ?></div>
                                        <div class="text-sm"><?php echo COMPANY_ADDRESS; ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel lateral de acciones -->
            <div class="lg:col-span-1 responsive-panel">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-8">
                    <h3 class="text-lg font-semibold mb-4"><?php echo t('Acciones', 'Actions'); ?></h3>

                    <!-- Cambiar estado para invoice y estimate -->
                    <?php if ($document['tipo'] === 'invoice' || $document['tipo'] === 'estimate'): ?>
                        <div class="mb-6">
                            <h4 class="font-medium mb-2">
                                <?php echo $document['tipo'] === 'invoice' ? t('Estado del Documento', 'Document Status') : t('Estado del Presupuesto', 'Estimate Status'); ?>
                            </h4>
                            <form method="POST" class="space-y-2">
                                <select
                                    name="new_status"
                                    id="estadoSelect"
                                    class="input-field w-full p-4 transition-colors duration-150 rounded"
                                    onchange="updateEstadoSelectBg()">
                                    <option value="pendiente" <?php echo $document['estado'] === 'pendiente' ? 'selected' : ''; ?>><?php echo t('Pendiente', 'Pending'); ?></option>
                                    <?php if ($document['tipo'] === 'invoice'): ?>
                                        <option value="pagado" <?php echo $document['estado'] === 'pagado' ? 'selected' : ''; ?>><?php echo t('Pagado', 'Paid'); ?></option>
                                    <?php elseif ($document['tipo'] === 'estimate'): ?>
                                        <option value="confirmado" <?php echo $document['estado'] === 'confirmado' ? 'selected' : ''; ?>><?php echo t('Confirmado', 'Confirmed'); ?></option>
                                    <?php endif; ?>
                                </select>
                                <button type="submit" name="change_status" class="btn-action-primary w-full">
                                    <?php echo t('Cambiar Estado', 'Change Status'); ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div class="flex flex-col gap-2 mb-6">
                        <a href="edit-document.php?id=<?php echo $document['id']; ?>" class="btn-action group">
                            <i class="fas fa-edit mr-2 group-hover:text-yellow-500 transition-colors"></i>
                            <?php echo t('Editar', 'Edit'); ?>
                        </a>
                        <?php if ($document['tipo'] === 'estimate'): ?>
                            <a href="convert-to-invoice.php?id=<?php echo $document['id']; ?>" class="btn-action group">
                                <i class="fas fa-exchange-alt mr-2 group-hover:text-blue-500 transition-colors"></i>
                                <?php echo t('Convertir a Factura', 'Convert to Invoice'); ?>
                            </a>
                        <?php endif; ?>
                        <button type="button" id="deleteBtn" class="btn-action-danger group w-full">
                            <i class="fas fa-trash mr-2 group-hover:text-red-500 transition-colors"></i>
                            <?php echo t('Eliminar', 'Delete'); ?>
                        </button>
                    </div>

                    <hr class="my-6">

                    <h4 class="font-medium mb-2"><?php echo t('Información', 'Information'); ?></h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <div><?php echo t('Creado', 'Created'); ?>: <?php echo date('d/m/Y H:i', strtotime($document['created_at'])); ?></div>
                        <?php if ($document['updated_at'] !== $document['created_at']): ?>
                            <div><?php echo t('Modificado', 'Modified'); ?>: <?php echo date('d/m/Y H:i', strtotime($document['updated_at'])); ?></div>
                        <?php endif; ?>
                        <div><?php echo t('Items', 'Items'); ?>: <?php echo count($document['items']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    function updateEstadoSelectBg() {
        var select = document.getElementById('estadoSelect');
        var value = select.value;
        select.classList.remove('bg-yellow-200', 'bg-blue-200', 'bg-green-200');

        if (value === 'pendiente') {
            select.classList.add('bg-yellow-200');
        } else if (value === 'pagado') {
            select.classList.add('bg-green-200');
        } else if (value === 'confirmado') {
            select.classList.add('bg-blue-200');
        }
    }

    document.addEventListener('DOMContentLoaded', updateEstadoSelectBg);
    if (typeof html2canvas === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
        document.head.appendChild(script);
    }

    async function generateImageDownload() {
        const loadingBtn = event.target;
        const originalText = loadingBtn.innerHTML;
        try {
            loadingBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><?php echo t("Generando imagen...", "Generating image..."); ?>';
            loadingBtn.disabled = true;
            let attempts = 0;
            while (typeof html2canvas === 'undefined' && attempts < 50) {
                await new Promise(resolve => setTimeout(resolve, 100));
                attempts++;
            }
            if (typeof html2canvas === 'undefined') {
                throw new Error('<?php echo t("Error cargando el generador de imágenes", "Error loading image generator"); ?>');
            }
            const documentElement = document.getElementById('documentContent');
            const options = {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                width: documentElement.offsetWidth,
                height: documentElement.offsetHeight,
                scrollX: 0,
                scrollY: 0,
                removeContainer: true
            };
            loadingBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><?php echo t("Procesando...", "Processing..."); ?>';
            const canvas = await html2canvas(documentElement, options);
            const link = document.createElement('a');
            link.download = '<?php echo $doc_type_translate[$document["tipo"]][$lang] . "_" . htmlspecialchars($document["numero_documento"]); ?>.png';
            link.href = canvas.toDataURL('image/png', 1.0);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showSuccessMessage('<?php echo t("¡Imagen generada y descargada exitosamente!", "Image generated and downloaded successfully!"); ?>');
        } catch (error) {
            console.error('Error generando imagen:', error);
            showErrorMessage('<?php echo t("Error generando la imagen. Por favor, intenta de nuevo.", "Error generating the image. Please try again."); ?>');
        } finally {
            loadingBtn.innerHTML = originalText;
            loadingBtn.disabled = false;
        }
    }

    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50';
        alertDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-green-700 hover:text-green-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50';
        alertDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-700 hover:text-red-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 8000);
    }

    // Estilos para impresión: solo factura, sin hoja en blanco ni escalado duplicado
    const printStyles = `
    <style>
        @media print {
            body * { display: none !important; }
            #documentContent, #documentContent * {
                display: block !important;
                visibility: visible !important;
                overflow: visible !important;
            }
            #documentContent {
                position: relative !important;
                left: 0 !important;
                top: 0 !important;
                width: 100vw !important;
                max-width: 210mm !important;
                min-width: 0 !important;
                margin: 0 auto !important;
                background: #fff !important;
                box-shadow: none !important;
                padding: 0 !important;
                overflow: visible !important;
                transform: none !important;
            }
            .responsive-doc-content {
                padding: 0 !important;
            }
            .no-print { display: none !important; }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
`;
    document.head.insertAdjacentHTML('beforeend', printStyles);

    function toggleMobileHeaderMenu() {
        const menu = document.getElementById('headerMobileMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var deleteBtn = document.getElementById('deleteBtn');
    if(deleteBtn){
        deleteBtn.addEventListener('click', function(e){
            e.preventDefault();
            Swal.fire({
                title: '<?php echo t("¿Estás seguro?", "Are you sure?"); ?>',
                text: '<?php echo t("Esta acción no se puede deshacer. ¿Deseas eliminar este documento?", "This action cannot be undone. Do you want to delete this document?"); ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e53e3e',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<?php echo t("Sí, eliminar", "Yes, delete"); ?>',
                cancelButtonText: '<?php echo t("Cancelar", "Cancel"); ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete-document.php?id=<?php echo $document['id']; ?>';
                }
            });
        });
    }
});
</script>
<?php includeFooter(); ?>