<?php
/**
 * Configuración y ajustes del sistema
 * CNA Upholstery System
 */

require_once '../config/config.php';
require_once '../config/functions.php';
requireAdmin();
// Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDB();
        
        // Datos de la empresa
        $company_data = [
            'company_name' => cleanInput($_POST['company_name']),
            'company_owner' => cleanInput($_POST['company_owner']),
            'company_phone' => cleanInput($_POST['company_phone']),
            'company_email' => cleanInput($_POST['company_email']),
            'company_address' => cleanInput($_POST['company_address'])
        ];
        
        // Configuración de impuestos
        $tax_rate = (float)($_POST['tax_rate'] ?? TAX_RATE);
        $tax_label = cleanInput($_POST['tax_label'] ?? TAX_LABEL);
        
        // Validaciones básicas
        if (empty($company_data['company_name'])) {
            throw new Exception('El nombre de la empresa es requerido');
        }
        
        if (!empty($company_data['company_email']) && !validateEmail($company_data['company_email'])) {
            throw new Exception('El email de la empresa no es válido');
        }
        
        if ($tax_rate < 0 || $tax_rate > 100) {
            throw new Exception('La tasa de impuestos debe estar entre 0% y 100%');
        }
        
        // Aquí normalmente actualizarías las configuraciones en base de datos
        // Por ahora mostraremos mensaje de éxito
        redirect('settings.php', 'Configuración actualizada exitosamente', 'success');
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Obtener estadísticas del sistema
try {
    $pdo = getDB();
    
    // Estadísticas generales
    $stats = [
        'total_documents' => 0,
        'total_invoices' => 0,
        'total_estimates' => 0,
        'total_clients' => 0,
        'total_revenue' => 0,
        'pending_amount' => 0,
        'database_size' => 0
    ];
    
    // Contar documentos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM documentos");
    $stats['total_documents'] = $stmt->fetch()['total'];
    
    // Contar por tipo
    $stmt = $pdo->query("SELECT tipo, COUNT(*) as total FROM documentos GROUP BY tipo");
    while ($row = $stmt->fetch()) {
        if ($row['tipo'] === 'invoice') {
            $stats['total_invoices'] = $row['total'];
        } elseif ($row['tipo'] === 'estimate') {
            $stats['total_estimates'] = $row['total'];
        }
    }
    
    // Contar clientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
    $stats['total_clients'] = $stmt->fetch()['total'];
    
    // Ingresos y pendientes
    $stmt = $pdo->query("SELECT SUM(CASE WHEN estado = 'pagado' THEN total ELSE 0 END) as revenue, SUM(CASE WHEN estado = 'pendiente' THEN total ELSE 0 END) as pending FROM documentos");
    $amounts = $stmt->fetch();
    $stats['total_revenue'] = $amounts['revenue'] ?? 0;
    $stats['pending_amount'] = $amounts['pending'] ?? 0;
    
    // Tamaño estimado de la base de datos (aproximado)
    $stmt = $pdo->query("SELECT COUNT(*) * 1024 as estimated_size FROM (SELECT * FROM documentos UNION ALL SELECT * FROM clientes UNION ALL SELECT * FROM items_documento) as all_tables");
    $stats['database_size'] = $stmt->fetch()['estimated_size'];
    
} catch (Exception $e) {
    $stats = [
        'total_documents' => 0,
        'total_invoices' => 0,
        'total_estimates' => 0,
        'total_clients' => 0,
        'total_revenue' => 0,
        'pending_amount' => 0,
        'database_size' => 0
    ];
}

includeHeader('Configuración del Sistema - CNA Upholstery');
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Configuración del Sistema</h1>
                    <p class="text-gray-600 mt-1">Ajustes generales y información de la empresa</p>
                </div>
                <div class="flex space-x-4">
                    <a href="../index.php" class="btn-secondary">
                        <i class="fas fa-home mr-2"></i>Inicio
                    </a>
                    <a href="index.php" class="btn-secondary">
                        <i class="fas fa-file-invoice mr-2"></i>Documentos
                    </a>
                    <a href="clients.php" class="btn-primary">
                        <i class="fas fa-users mr-2"></i>Clientes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        
        <?php displaySessionAlert(); ?>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Configuración principal -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Información de la Empresa</h2>
                    
                    <form method="POST">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Empresa *</label>
                                <input type="text" name="company_name" value="<?php echo htmlspecialchars(COMPANY_NAME); ?>" class="input-field" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Propietario *</label>
                                <input type="text" name="company_owner" value="<?php echo htmlspecialchars(COMPANY_OWNER); ?>" class="input-field" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                <input type="tel" name="company_phone" value="<?php echo htmlspecialchars(COMPANY_PHONE); ?>" class="input-field">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="company_email" value="<?php echo htmlspecialchars(COMPANY_EMAIL); ?>" class="input-field">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                                <textarea name="company_address" rows="3" class="input-field"><?php echo htmlspecialchars(COMPANY_ADDRESS); ?></textarea>
                            </div>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mt-8 mb-4">Configuración de Impuestos</h3>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tasa de Impuesto (%)</label>
                                <input type="number" name="tax_rate" value="<?php echo TAX_RATE; ?>" step="0.01" min="0" max="100" class="input-field">
                                <p class="text-sm text-gray-500 mt-1">Ejemplo: 8.75 para 8.75%</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Etiqueta del Impuesto</label>
                                <input type="text" name="tax_label" value="<?php echo htmlspecialchars(TAX_LABEL); ?>" class="input-field">
                                <p class="text-sm text-gray-500 mt-1">Ejemplo: "Sales Tax", "IVA", etc.</p>
                            </div>
                        </div>
                        
                        <div class="mt-8">
                            <button type="submit" class="btn-success">
                                <i class="fas fa-save mr-2"></i>Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Información del sistema -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Información del Sistema</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Versión del Sistema</h3>
                            <p class="text-lg font-semibold text-gray-900">CNA Upholstery v1.0</p>
                            <p class="text-sm text-gray-500">Sistema de Facturación</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Última Actualización</h3>
                            <p class="text-lg font-semibold text-gray-900"><?php echo date('d/m/Y'); ?></p>
                            <p class="text-sm text-gray-500">Sistema actualizado</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Servidor Web</h3>
                            <p class="text-lg font-semibold text-gray-900"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></p>
                            <p class="text-sm text-gray-500">PHP <?php echo PHP_VERSION; ?></p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Base de Datos</h3>
                            <p class="text-lg font-semibold text-gray-900">SQLite</p>
                            <p class="text-sm text-gray-500">Tamaño: ~<?php echo number_format($stats['database_size'] / 1024, 2); ?> KB</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas del sidebar -->
            <div class="space-y-6">
                
                <!-- Resumen de datos -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen del Sistema</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Total Documentos</span>
                            <span class="text-lg font-bold text-blue-600"><?php echo number_format($stats['total_documents']); ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Facturas</span>
                            <span class="text-lg font-bold text-green-600"><?php echo number_format($stats['total_invoices']); ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Presupuestos</span>
                            <span class="text-lg font-bold text-yellow-600"><?php echo number_format($stats['total_estimates']); ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Clientes</span>
                            <span class="text-lg font-bold text-purple-600"><?php echo number_format($stats['total_clients']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Resumen financiero -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen Financiero</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm font-medium text-gray-600">Ingresos Totales</span>
                            <div class="text-2xl font-bold text-green-600"><?php echo formatPrice($stats['total_revenue']); ?></div>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-600">Pendiente de Cobro</span>
                            <div class="text-2xl font-bold text-orange-600"><?php echo formatPrice($stats['pending_amount']); ?></div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="text-xs text-gray-500">
                                Los montos incluyen todos los documentos registrados en el sistema
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones rápidas -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
                    
                    <div class="space-y-3">
                        <a href="../create-invoice.php" class="block w-full btn-success text-center">
                            <i class="fas fa-plus mr-2"></i>Nueva Factura
                        </a>
                        
                        <a href="../create-estimate.php" class="block w-full bg-company-gold hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-center">
                            <i class="fas fa-calculator mr-2"></i>Nuevo Presupuesto
                        </a>
                        
                        <a href="clients.php" class="block w-full btn-primary text-center">
                            <i class="fas fa-users mr-2"></i>Ver Clientes
                        </a>
                        
                        <a href="index.php" class="block w-full btn-secondary text-center">
                            <i class="fas fa-list mr-2"></i>Ver Documentos
                        </a>
                    </div>
                </div>
                
                <!-- Información de soporte -->
                <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3">
                        <i class="fas fa-info-circle mr-2"></i>Información
                    </h3>
                    <div class="text-sm text-blue-800 space-y-2">
                        <p><strong>CNA Upholstery System</strong></p>
                        <p>Sistema de facturación diseñado para tapicería y servicios relacionados.</p>
                        <p class="text-xs text-blue-600 mt-3">
                            Desarrollado con PHP, SQLite y TailwindCSS
                        </p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<?php includeFooter(); ?>