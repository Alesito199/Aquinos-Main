<?php

/**
 * Generador de imágenes usando JavaScript (sin extensiones PHP)
 * CNA Upholstery System
 */

require_once '../config/config.php';

// --- Idioma y traducción ---
if (isset($_POST['lang'])) {
    $lang = $_POST['lang'];
    $_SESSION['lang'] = $lang;
    // Redirige usando GET para que la URL quede limpia y evite re-envío del formulario
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($_GET['id']) . "&lang=" . $lang);
    exit;
}
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'es';
$_SESSION['lang'] = $lang;

$t = [
    'es' => [
        'web_title' => 'Generar Imagen',
        'header' => 'Generar Imagen',
        'back' => 'Volver',
        'download_png' => 'Descargar PNG',
        'download_jpg' => 'Descargar JPG',
        'generating' => 'Generando imagen...',
        'billed_to' => 'Facturado a:',
        'item' => 'Item',
        'quantity' => 'Cantidad',
        'unit_price' => 'Precio Unitario',
        'total' => 'Total',
        'subtotal' => 'Subtotal',
        'tax' => TAX_LABEL,
        'notes' => 'Notas:',
        'thank_you' => '¡Gracias!',
        'payment_info' => 'INFORMACIÓN DE PAGO',
        'error' => 'Error generando la imagen. Por favor, intenta de nuevo.',
        'success' => '¡Imagen generada y descargada exitosamente!',
        'date' => 'Fecha',
        'preview_title' => 'Vista Previa',
    ],
    'en' => [
        'web_title' => 'Generate Image',
        'header' => 'Generate Image',
        'back' => 'Back',
        'download_png' => 'Download PNG',
        'download_jpg' => 'Download JPG',
        'generating' => 'Generating image...',
        'billed_to' => 'Billed to:',
        'item' => 'Item',
        'quantity' => 'Quantity',
        'unit_price' => 'Unit Price',
        'total' => 'Total',
        'subtotal' => 'Subtotal',
        'tax' => TAX_LABEL,
        'notes' => 'Notes:',
        'thank_you' => 'Thank you!',
        'payment_info' => 'PAYMENT INFORMATION',
        'error' => 'Error generating image. Please try again.',
        'success' => 'Image generated and downloaded successfully!',
        'date' => 'Date',
        'preview_title' => 'Preview',
    ]
][$lang];
function t($es, $en)
{
    global $lang;
    return $lang === 'en' ? $en : $es;
}
// Verificar que se proporcione ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Documento no encontrado');
}

$document_id = (int)$_GET['id'];

// Obtener documento con items
$document = getDocumentWithItems($document_id);

if (!$document) {
    die('Documento no encontrado');
}

includeHeader($t['web_title'] . ' - ' . ($document['tipo'] === 'invoice' ? ($lang === 'en' ? 'Invoice' : 'Factura') : ($lang === 'en' ? 'Estimate' : 'Presupuesto')));
?>

<div class="min-h-screen py-8 bg-gray-100">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo $t['header']; ?></h1>
                <p class="text-gray-600 mt-2">
                    <?php echo ($document['tipo'] === 'invoice' ? ($lang === 'en' ? 'Invoice' : 'Factura') : ($lang === 'en' ? 'Estimate' : 'Presupuesto')); ?>
                    #<?php echo htmlspecialchars($document['numero_documento']); ?>
                </p>
            </div>
            <div class="hidden md:flex space-x-3 items-center">
                <a href="view-document.php?id=<?php echo $document['id']; ?>&lang=<?php echo $lang; ?>" class="btn-secondary flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i><?php echo $t['back']; ?>
                </a>
                <button onclick="generateImage('png')" class="btn-success flex items-center">
                    <i class="fas fa-download mr-2"></i><?php echo $t['download_png']; ?>
                </button>
                <button onclick="generateImage('jpg')" class="btn-primary flex items-center">
                    <i class="fas fa-download mr-2"></i><?php echo $t['download_jpg']; ?>
                </button>
                <button onclick="printInvoice()" class="btn-primary flex items-center">
                    <i class="fas fa-print mr-2"></i>Imprimir
                </button>
                <form method="post" action="" class="flex items-center">
                    <select name="lang" onchange="this.form.submit()" class="bg-gray-900 text-white px-4 py-2 rounded shadow focus:outline-none">
                        <option value="es" <?php if ($lang === 'es') echo 'selected'; ?>>Español</option>
                        <option value="en" <?php if ($lang === 'en') echo 'selected'; ?>>English</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Loading indicator -->
        <div id="loading" class="hidden text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600"><?php echo $t['generating']; ?></p>
        </div>

        <!-- Vista previa del documento -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Contenedor del documento que se convertirá a imagen -->
            <div id="documentToCapture" class="bg-white" style="width: 800px; margin: 0 auto;">
                <!-- Header -->
                <div style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 30px 35px;">
                    <div style="display: flex; justify-content: space-between;">
                        <div style="flex: 1;">
                            <div style="font-size: 36px; font-weight: bold; margin-bottom: 5px;">
                                <span style="color: #d4a574; font-weight: 900;">CNA</span><br>
                                <span style="font-size: 18px; font-weight: 500;">UPHOLSTERY</span>
                            </div>
                            <div style="width: 70px; height: 4px; background: #d4a574; margin: 18px 0;"></div>
                            <div style="font-size: 13px; line-height: 1.7;">
                                <?php echo COMPANY_OWNER; ?><br>
                                <?php echo COMPANY_PHONE; ?><br>
                                <?php echo COMPANY_EMAIL; ?><br>
                                <?php echo COMPANY_ADDRESS; ?>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <div style="font-size: 32px; font-weight: bold; margin-bottom: 10px;">
                                <?php echo strtoupper($document['tipo'] === 'invoice' ? ($lang === 'en' ? 'Invoice' : 'Factura') : ($lang === 'en' ? 'Estimate' : 'Presupuesto')); ?>
                            </div>
                            <div style="font-size: 15px; margin-bottom: 10px; opacity: 0.9;">
                                <?php if ($document['tipo'] === 'estimate'): ?>
                                    <?php echo ucfirst($document['tipo'] === 'estimate' ? ($lang === 'en' ? 'Estimate' : 'Presupuesto') : ($lang === 'en' ? 'Invoice' : 'Factura')); ?> No.<?php
                                                                                                                                                                                            preg_match('/(\d+)$/', $document['numero_documento'], $matches);
                                                                                                                                                                                            echo isset($matches[1]) ? (int)$matches[1] : '1';
                                                                                                                                                                                            ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($document['numero_documento']); ?>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 15px; opacity: 0.9;">
                                <?php echo $t['date']; ?>: <?php echo date('F j, Y', strtotime($document['fecha'])); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div style="padding: 35px;">
                    <!-- Billing Information -->
                    <div style="margin-bottom: 35px;">
                        <div style="font-size: 16px; font-weight: bold; margin-bottom: 12px; color: #2c3e50;"><?php echo strtoupper($t['billed_to']); ?></div>
                        <div style="font-size: 14px; line-height: 1.6;">
                            <div style="font-size: 18px; font-weight: bold; margin-bottom: 6px;"><?php echo htmlspecialchars($document['cliente_nombre'] ?? ($lang === 'en' ? 'Client not specified' : 'Cliente no especificado')); ?></div>
                            <?php if (!empty($document['telefono'])): ?>
                                <div><?php echo htmlspecialchars($document['telefono']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($document['email'])): ?>
                                <div><?php echo htmlspecialchars($document['email']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($document['direccion'])): ?>
                                <div><?php echo htmlspecialchars($document['direccion']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 35px; font-family: Arial, sans-serif;">
                        <thead>
                            <tr>
                                <th style="background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px 10px; text-align: left; font-weight: bold; font-size: 13px; color: #2c3e50;"><?php echo $t['item']; ?></th>
                                <th style="background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px 10px; text-align: center; font-weight: bold; font-size: 13px; color: #2c3e50; width: 90px;"><?php echo $t['quantity']; ?></th>
                                <th style="background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px 10px; text-align: right; font-weight: bold; font-size: 13px; color: #2c3e50; width: 110px;"><?php echo $t['unit_price']; ?></th>
                                <th style="background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px 10px; text-align: right; font-weight: bold; font-size: 13px; color: #2c3e50; width: 110px;"><?php echo $t['total']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rowIndex = 0;
                            foreach ($document['items'] as $item): ?>
                                <tr style="<?php echo $rowIndex % 2 === 1 ? 'background-color: #f8f9fa;' : ''; ?>">
                                    <td style="border: 1px solid #dee2e6; padding: 12px 10px; font-size: 13px;">
                                        <div style="font-weight: bold;"><?php echo htmlspecialchars($item['descripcion']); ?></div>
                                    </td>
                                    <td style="border: 1px solid #dee2e6; padding: 12px 10px; font-size: 13px; text-align: center;">
                                        <?php echo number_format($item['cantidad'], $item['cantidad'] == (int)$item['cantidad'] ? 0 : 2); ?>
                                    </td>
                                    <td style="border: 1px solid #dee2e6; padding: 12px 10px; font-size: 13px; text-align: right;">
                                        <?php echo formatPrice($item['precio_unitario']); ?>
                                    </td>
                                    <td style="border: 1px solid #dee2e6; padding: 12px 10px; font-size: 13px; text-align: right; font-weight: bold;">
                                        <?php echo formatPrice($item['total']); ?>
                                    </td>
                                </tr>
                            <?php $rowIndex++;
                            endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div style="float: right; width: 320px; margin-bottom: 35px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 14px;"><?php echo $t['subtotal']; ?></td>
                                <td style="padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 14px; text-align: right;"><?php echo formatPrice($document['subtotal']); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 14px;"><?php echo $t['tax']; ?></td>
                                <td style="padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 14px; text-align: right;"><?php echo formatPrice($document['impuestos']); ?></td>
                            </tr>
                            <tr style="border-top: 2px solid #2c3e50; font-weight: bold; font-size: 16px; background-color: #f8f9fa;">
                                <td style="padding: 10px 15px;"><?php echo $t['total']; ?></td>
                                <td style="padding: 10px 15px; text-align: right;"><?php echo formatPrice($document['total']); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div style="clear: both;"></div>

                    <!-- Notes (solo para presupuestos) -->
                    <?php if ($document['tipo'] === 'estimate' && !empty($document['notas'])): ?>
                        <div style="margin-bottom: 35px; clear: both; padding-top: 25px; border-top: 1px solid #eee;">
                            <div style="font-weight: bold; margin-bottom: 12px; color: #2c3e50; font-size: 14px;"><?php echo $t['notes']; ?></div>
                            <div style="line-height: 1.6; font-size: 13px;"><?php echo nl2br(htmlspecialchars($document['notas'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Footer -->
                    <div style="text-align: center; margin-top: 45px; padding-top: 25px; border-top: 2px solid #2c3e50;">
                        <div style="font-size: 28px; font-weight: bold; margin-bottom: 18px; color: #2c3e50;"><?php echo $t['thank_you']; ?></div>
                        <?php if ($document['tipo'] === 'invoice'): ?>
                            <div style="font-weight: bold; margin-bottom: 12px; font-size: 15px;"><?php echo $t['payment_info']; ?></div>
                        <?php endif; ?>
                        <div style="font-size: 13px; line-height: 1.6; color: #666;">
                            <?php echo COMPANY_OWNER; ?><br>
                            <?php echo COMPANY_PHONE; ?><br>
                            <?php echo COMPANY_EMAIL; ?>
                        </div>

                        <?php if ($document['tipo'] === 'estimate'): ?>
                            <div style="margin-top: 35px;">
                                <div style="float: right; text-align: right; width: 40%;">
                                    <div style="font-weight: bold; font-size: 16px; margin-bottom: 4px;"><?php echo COMPANY_NAME; ?></div>
                                    <div style="font-size: 12px;"><?php echo COMPANY_ADDRESS; ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cargar html2canvas desde CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>

    // Imprimir solo la factura
    function printInvoice() {
        window.print();
    }

    // CSS para impresión solo de la factura
    const printStyles = `
    <style>
        @media print {
            body * { display: none !important; }
            #documentToCapture, #documentToCapture * {
                display: block !important;
                visibility: visible !important;
            }
            #documentToCapture {
                position: relative !important;
                left: 0 !important;
                top: 0 !important;
                width: 210mm !important;
                min-width: 0 !important;
                max-width: 210mm !important;
                margin: 0 auto !important;
                background: #fff !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
`;
    document.head.insertAdjacentHTML('beforeend', printStyles);
    async function generateImage(format) {
        const loadingElement = document.getElementById('loading');
        const documentElement = document.getElementById('documentToCapture');

        try {
            // Mostrar loading
            loadingElement.classList.remove('hidden');

            // Configurar opciones para html2canvas
            const options = {
                scale: 2, // Alta resolución
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                width: 800,
                height: documentElement.offsetHeight,
                scrollX: 0,
                scrollY: 0
            };

            // Generar canvas
            const canvas = await html2canvas(documentElement, options);

            // Crear enlace de descarga
            const link = document.createElement('a');
            const numDoc = "<?php echo ($document['tipo'] === 'invoice' ? 'Invoice' : 'Estimate') . '_' . htmlspecialchars($document['numero_documento']); ?>";

            if (format === 'png') {
                link.download = numDoc + ".png";
                link.href = canvas.toDataURL('image/png');
            } else {
                link.download = numDoc + ".jpg";
                link.href = canvas.toDataURL('image/jpeg', 0.9);
            }

            // Simular click para descargar
            link.click();

            // Mensaje de éxito
            alert("<?php echo $t['success']; ?>");

        } catch (error) {
            console.error('Error generando imagen:', error);
            alert("<?php echo $t['error']; ?>");
        } finally {
            // Ocultar loading
            loadingElement.classList.add('hidden');
        }
    }

    // Función para vista previa en nueva ventana
    function previewImage() {
        const documentElement = document.getElementById('documentToCapture');
        const newWindow = window.open('', '_blank');
        newWindow.document.write(`
        <html>
        <head>
            <title><?php echo $t['preview_title']; ?> - <?php echo ($document['tipo'] === 'invoice' ? ($lang === 'en' ? 'Invoice' : 'Factura') : ($lang === 'en' ? 'Estimate' : 'Presupuesto')); ?></title>
            <style>
                body { margin: 0; padding: 20px; background: #f5f5f5; font-family: Arial, sans-serif; }
                .preview { background: white; box-shadow: 0 0 20px rgba(0,0,0,0.1); margin: 0 auto; }
            </style>
        </head>
        <body>
            <div class="preview">
                ${documentElement.outerHTML}
            </div>
        </body>
        </html>
    `);
    }
</script>

<?php includeFooter(); ?>