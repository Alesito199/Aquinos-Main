<?php
/**
 * Convertir presupuesto a factura
 * CNA Upholstery System
 */

require_once '../config/config.php';

// Verificar que se proporcione ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('admin/index.php', 'Presupuesto no encontrado', 'error');
}

$estimate_id = (int)$_GET['id'];

try {
    $pdo = getDB();
    
    // Obtener el presupuesto con sus items
    $estimate = getDocumentWithItems($estimate_id);
    
    if (!$estimate) {
        redirect('admin/index.php', 'Presupuesto no encontrado', 'error');
    }
    
    // Verificar que sea un presupuesto
    if ($estimate['tipo'] !== 'estimate') {
        redirect("view-document.php?id={$estimate_id}", 'El documento seleccionado no es un presupuesto', 'error');
    }
    
    // Verificar confirmación
    $confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';
    
    if (!$confirm) {
        // Mostrar página de confirmación
        includeHeader('Convertir Presupuesto a Factura - Confirmación');
        ?>
        
        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-lg w-full space-y-8">
                
                <!-- Icono de conversión -->
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-blue-100">
                        <i class="fas fa-exchange-alt text-blue-600 text-4xl"></i>
                    </div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Convertir a Factura
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Se creará una nueva factura basada en este presupuesto
                    </p>
                </div>

                <!-- Información del presupuesto -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Presupuesto Original</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Número:</span>
                            <span class="text-gray-900 font-mono">
                                #<?php echo htmlspecialchars($estimate['numero_documento']); ?>
                            </span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Cliente:</span>
                            <span class="text-gray-900">
                                <?php echo htmlspecialchars($estimate['cliente_nombre'] ?? 'Sin cliente'); ?>
                            </span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Fecha:</span>
                            <span class="text-gray-900">
                                <?php echo date('d/m/Y', strtotime($estimate['fecha'])); ?>
                            </span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Items:</span>
                            <span class="text-gray-900">
                                <?php echo count($estimate['items']); ?> items
                            </span>
                        </div>
                        
                        <div class="flex justify-between border-t pt-3">
                            <span class="font-bold text-gray-700">Total:</span>
                            <span class="text-xl font-bold text-gray-900">
                                <?php echo formatPrice($estimate['total']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Información sobre la conversión -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Lo que sucederá al convertir:
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Se creará una nueva factura con número único</li>
                                    <li>Se copiarán todos los items y datos del cliente</li>
                                    <li>La fecha será la de hoy (<?php echo date('d/m/Y'); ?>)</li>
                                    <li>El estado será "Pendiente"</li>
                                    <li>El presupuesto original se mantendrá sin cambios</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex space-x-4">
                    <a href="view-document.php?id=<?php echo $estimate['id']; ?>" 
                       class="flex-1 flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    
                    <a href="?id=<?php echo $estimate['id']; ?>&confirm=yes" 
                       class="flex-1 flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200">
                        <i class="fas fa-exchange-alt mr-2"></i>
                        Sí, Convertir
                    </a>
                </div>

            </div>
        </div>

        <?php
        includeFooter();
        exit;
    }
    
    // Proceder con la conversión
    $pdo->beginTransaction();
    
    try {
        // Generar número de factura
        $invoice_number = generateDocumentNumber('invoice');
        
        // Crear la nueva factura
        $stmt = $pdo->prepare("
            INSERT INTO documentos (numero_documento, tipo, fecha, estado, subtotal, impuestos, total, id_cliente, notas) 
            VALUES (?, 'invoice', ?, 'pendiente', ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $invoice_number, 
            date('Y-m-d'), 
            $estimate['subtotal'], 
            $estimate['impuestos'], 
            $estimate['total'], 
            $estimate['id_cliente'],
            'Factura creada a partir del presupuesto #' . $estimate['numero_documento']
        ]);
        
        $new_invoice_id = $pdo->lastInsertId();
        
        // Copiar todos los items
        $stmt = $pdo->prepare("INSERT INTO items_documento (id_documento, descripcion, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($estimate['items'] as $item) {
            $stmt->execute([
                $new_invoice_id, 
                $item['descripcion'], 
                $item['cantidad'], 
                $item['precio_unitario'], 
                $item['total']
            ]);
        }
        
        $pdo->commit();
        
        // Redireccionar a la nueva factura
        redirect("view-document.php?id={$new_invoice_id}", "Presupuesto convertido exitosamente a factura #{$invoice_number}", "success");
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception("Error creando la factura: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error en convert-to-invoice.php: " . $e->getMessage());
    redirect('admin/index.php', 'Error al convertir presupuesto: ' . $e->getMessage(), 'error');
}
?>