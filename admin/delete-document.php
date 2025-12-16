<?php

/**
 * Eliminar documento (factura o presupuesto) - Bilingüe
 * CNA Upholstery System
 */

require_once '../config/config.php';

// Traducción
function t($es, $en)
{
    $lang = $_SESSION['lang'] ?? 'es';
    return $lang === 'en' ? $en : $es;
}

// Verificar que se proporcione ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('admin/index.php', t('Documento no encontrado', 'Document not found'), 'error');
}

$document_id = (int)$_GET['id'];

// Verificar si es confirmación
$confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

try {
    $pdo = getDB();

    // Obtener información del documento antes de eliminarlo
    $stmt = $pdo->prepare("
        SELECT 
            d.id,
            d.numero_documento,
            d.tipo,
            d.fecha,
            d.estado,
            d.total,
            c.nombre as cliente_nombre
        FROM documentos d 
        LEFT JOIN clientes c ON d.id_cliente = c.id 
        WHERE d.id = ?
    ");
    $stmt->execute([$document_id]);
    $document = $stmt->fetch();

    if (!$document) {
        redirect('admin/index.php', t('Documento no encontrado', 'Document not found'), 'error');
    }

    // Si no es confirmación, mostrar página de confirmación
    if (!$confirm) {
        includeHeader(t('Eliminar Documento - Confirmación', 'Delete Document - Confirmation'));
?>

        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-md w-full space-y-8">
                <!-- Icono de advertencia -->
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600 text-4xl"></i>
                    </div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        <?php echo t('¿Eliminar Documento?', 'Delete Document?'); ?>
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        <?php echo t('Esta acción no se puede deshacer', 'This action cannot be undone'); ?>
                    </p>
                </div>

                <!-- Información del documento -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700"><?php echo t('Tipo:', 'Type:'); ?></span>
                            <span class="text-gray-900">
                                <i class="fas fa-<?php echo $document['tipo'] === 'invoice' ? 'file-invoice' : 'calculator'; ?> mr-2"></i>
                                <?php
                                $types = ['invoice' => t('Factura', 'Invoice'), 'estimate' => t('Presupuesto', 'Estimate')];
                                echo $types[$document['tipo']] ?? $document['tipo'];
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700"><?php echo t('Número:', 'Number:'); ?></span>
                            <span class="text-gray-900 font-mono">
                                #<?php echo htmlspecialchars($document['numero_documento']); ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700"><?php echo t('Cliente:', 'Client:'); ?></span>
                            <span class="text-gray-900">
                                <?php echo htmlspecialchars($document['cliente_nombre'] ?? t('Sin cliente', 'No client')); ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700"><?php echo t('Fecha:', 'Date:'); ?></span>
                            <span class="text-gray-900">
                                <?php echo date('d/m/Y', strtotime($document['fecha'])); ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700"><?php echo t('Estado:', 'Status:'); ?></span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php echo $document['estado'] === 'pagado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php
                                $status_translate = [
                                    'pendiente' => t('Pendiente', 'Pending'),
                                    'pagado' => t('Pagado', 'Paid'),
                                    'confirmado' => t('Confirmado', 'Confirmed')
                                ];
                                echo $status_translate[$document['estado']] ?? ucfirst($document['estado']);
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between border-t pt-4">
                            <span class="font-bold text-gray-700"><?php echo t('Total:', 'Total:'); ?></span>
                            <span class="text-xl font-bold text-gray-900">
                                <?php echo formatPrice($document['total']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Advertencias -->
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                <?php echo t('Consecuencias de eliminar este documento:', 'Consequences of deleting this document:'); ?>
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><?php echo t('Se eliminará el documento completo', 'The entire document will be deleted'); ?></li>
                                    <li><?php echo t('Se eliminarán todos los items asociados', 'All associated items will be deleted'); ?></li>
                                    <li><?php echo t('Esta acción es irreversible', 'This action is irreversible'); ?></li>
                                    <li><?php echo t('No se eliminará la información del cliente', 'Client information will NOT be deleted'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex space-x-4">
                    <a href="view-document.php?id=<?php echo $document['id']; ?>"
                        class="flex-1 flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        <i class="fas fa-times mr-2"></i>
                        <?php echo t('Cancelar', 'Cancel'); ?>
                    </a>
                    <button type="button" id="delete-confirm-btn"
                        class="flex-1 flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200">
                        <i class="fas fa-trash mr-2"></i>
                        <?php echo t('Sí, Eliminar', 'Yes, Delete'); ?>
                    </button>
                </div>

                <!-- Enlaces alternativos -->
                <div class="text-center">
                    <div class="text-sm text-gray-500">
                        <?php echo t('¿Prefieres otra acción?', 'Prefer another action?'); ?>
                    </div>
                    <div class="mt-2 space-x-4">
                        <a href="edit-document.php?id=<?php echo $document['id']; ?>" class="text-blue-600 hover:text-blue-500">
                            <i class="fas fa-edit mr-1"></i><?php echo t('Editar documento', 'Edit document'); ?>
                        </a>
                        <a href="admin/index.php" class="text-gray-600 hover:text-gray-500">
                            <i class="fas fa-list mr-1"></i><?php echo t('Ver todos los documentos', 'View all documents'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('delete-confirm-btn');
                btn.addEventListener('click', function(e) {
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
                            window.location.href = '?id=<?php echo $document['id']; ?>&confirm=yes';
                        }
                    });
                });
            });
        </script>
<?php
        includeFooter();
        exit;
    }

    // Confirmación recibida, proceder con la eliminación
    $pdo->beginTransaction();
    try {
        // Eliminar items del documento - revisa el nombre de la tabla y campo
        $stmt = $pdo->prepare("DELETE FROM items_documento WHERE id_documento = ?");
        $stmt->execute([$document_id]);
        // Eliminar el documento
        $stmt = $pdo->prepare("DELETE FROM documentos WHERE id = ?");
        $stmt->execute([$document_id]);
        $pdo->commit();
        // Crear mensaje de éxito con información del documento eliminado
        $types = ['invoice' => t('Factura', 'Invoice'), 'estimate' => t('Presupuesto', 'Estimate')];
        $document_type = $types[$document['tipo']] ?? $document['tipo'];
        $success_message = "{$document_type} #{$document['numero_documento']} " . t('eliminado exitosamente', 'successfully deleted');
        redirect('index.php', $success_message, 'success');
    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception("Error eliminando el documento: " . $e->getMessage());
    }
} catch (Exception $e) {
    error_log("Error en delete-document.php: " . $e->getMessage());
    redirect('index.php', t('Error al eliminar el documento: ', 'Error deleting document: ') . $e->getMessage(), 'error');
}
?>