<?php

/**
 * Panel de administración principal
 * Lista de documentos (facturas y presupuestos)
 * CNA Upholstery System
 */

// --- Configuración y Traducción ---
require_once '../config/config.php';
require_once '../includes/functions.php';
requireAdmin();

$lang = $_SESSION['lang'] ?? $_GET['lang'] ?? 'es';

// Traducciones
$t = [
    'es' => [
        'web_title' => 'Panel de Administración - CNA Upholstery',
        'admin_panel' => 'Panel de Administración',
        'subtitle' => 'Gestión de facturas y presupuestos',
        'new_estimate' => 'Nuevo Presupuesto',
        'new_invoice' => 'Nueva Factura',
        'logout' => 'Cerrar Sesión',
        'invoices_30' => 'Facturas (30 días)',
        'estimates_30' => 'Presupuestos (30 días)',
        'total_paid' => 'Total Pagado',
        'total_pending' => 'Total Pendiente',
        'search' => 'Buscar',
        'search_placeholder' => 'Número, cliente, teléfono...',
        'type' => 'Tipo',
        'all' => 'Todos',
        'invoice' => 'Facturas',
        'estimate' => 'Presupuestos',
        'status' => 'Estado',
        'pending' => 'Pendiente',
        'paid' => 'Pagado',
        'filter' => 'Filtrar',
        'clear' => 'Limpiar',
        'documents' => 'Documentos',
        'no_documents' => 'No hay documentos',
        'no_documents_search' => 'No se encontraron documentos que coincidan con tu búsqueda.',
        'no_documents_create' => 'Comienza creando tu primera factura o presupuesto.',
        'view' => 'Ver',
        'edit' => 'Editar',
        'download_img' => 'Descargar Imagen',
        'delete' => 'Eliminar',
        'actions' => 'Acciones',
        'client' => 'Cliente',
        'date' => 'Fecha',
        'document' => 'Documento',
        'total' => 'Total',
        'showing' => 'Mostrando',
        'of' => 'de',
        'previous' => 'Anterior',
        'next' => 'Siguiente',
        'upload_project' => 'Cargar Proyectos',
        'document_types' => [
            'invoice' => 'Factura',
            'estimate' => 'Presupuesto'
        ],
        'document_status' => [
            'pendiente' => 'Pendiente',
            'pagado' => 'Pagado',
            'confirmado' => 'Confirmado'
        ],
        'confirm_delete' => '¿Estás seguro de eliminar',
        'cannot_undo' => 'Esta acción no se puede deshacer.',
    ],
    'en' => [
        'web_title' => 'Admin Panel - CNA Upholstery',
        'admin_panel' => 'Admin Panel',
        'subtitle' => 'Invoices and Estimates Management',
        'new_estimate' => 'New Estimate',
        'new_invoice' => 'New Invoice',
        'logout' => 'Logout',
        'invoices_30' => 'Invoices (30 days)',
        'estimates_30' => 'Estimates (30 days)',
        'total_paid' => 'Total Paid',
        'total_pending' => 'Total Pending',
        'search' => 'Search',
        'search_placeholder' => 'Number, client, phone...',
        'type' => 'Type',
        'all' => 'All',
        'invoice' => 'Invoices',
        'estimate' => 'Estimates',
        'status' => 'Status',
        'pending' => 'Pending',
        'paid' => 'Paid',
        'filter' => 'Filter',
        'clear' => 'Clear',
        'documents' => 'Documents',
        'no_documents' => 'No documents',
        'no_documents_search' => 'No documents match your search.',
        'no_documents_create' => 'Start by creating your first invoice or estimate.',
        'view' => 'View',
        'edit' => 'Edit',
        'download_img' => 'Download Image',
        'delete' => 'Delete',
        'actions' => 'Actions',
        'client' => 'Client',
        'date' => 'Date',
        'document' => 'Document',
        'total' => 'Total',
        'showing' => 'Showing',
        'of' => 'of',
        'previous' => 'Previous',
        'next' => 'Next',
        'upload_project' => 'Upload Projects',
        'document_types' => [
            'invoice' => 'Invoice',
            'estimate' => 'Estimate'
        ],
        'document_status' => [
            'pendiente' => 'Pending',
            'pagado' => 'Paid',
            'confirmado' => 'Confirmed'
        ],
        'confirm_delete' => 'Are you sure you want to delete',
        'cannot_undo' => 'This action cannot be undone.',
    ]
][$lang];

// --- Filtros y búsqueda ---
$tipo = $_GET['tipo'] ?? 'all';
$estado = $_GET['estado'] ?? 'all';
$search = cleanInput($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

// --- Query y documentos ---
$where_conditions = [];
$params = [];

if ($tipo !== 'all') {
    $where_conditions[] = "d.tipo = ?";
    $params[] = $tipo;
}
if ($estado !== 'all') {
    $where_conditions[] = "d.estado = ?";
    $params[] = $estado;
}
if (!empty($search)) {
    $where_conditions[] = "(d.numero_documento LIKE ? OR c.nombre LIKE ? OR c.telefono LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Contar total de documentos y obtener datos
try {
    $pdo = getDB();
    $count_sql = "
        SELECT COUNT(*) as total
        FROM documentos d 
        LEFT JOIN clientes c ON d.id_cliente = c.id 
        {$where_clause}
    ";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_documents = $stmt->fetch()['total'];
    $total_pages = ceil($total_documents / ITEMS_PER_PAGE);
    $offset = ($page - 1) * ITEMS_PER_PAGE;

    $sql = "
        SELECT 
            d.id,
            d.numero_documento,
            d.tipo,
            d.fecha,
            d.estado,
            d.subtotal,
            d.impuestos,
            d.total,
            d.created_at,
            d.updated_at,
            c.nombre as cliente_nombre,
            c.telefono,
            c.email
        FROM documentos d 
        LEFT JOIN clientes c ON d.id_cliente = c.id 
        {$where_clause}
        ORDER BY d.created_at DESC 
        LIMIT " . ITEMS_PER_PAGE . " OFFSET {$offset}
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();

    $stats_sql = "
        SELECT 
            COUNT(CASE WHEN tipo = 'invoice' THEN 1 END) as total_invoices,
            COUNT(CASE WHEN tipo = 'estimate' THEN 1 END) as total_estimates,
            SUM(CASE WHEN estado = 'pagado' THEN total ELSE 0 END) as total_paid,
            SUM(CASE WHEN estado = 'pendiente' THEN total ELSE 0 END) as total_pending
        FROM documentos d
        WHERE d.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ";
    $stmt = $pdo->query($stats_sql);
    $stats = $stmt->fetch();
} catch (Exception $e) {
    $error_message = "Error loading documents: " . $e->getMessage();
    $documents = [];
    $total_documents = 0;
    $total_pages = 0;
    $stats = [
        'total_invoices' => 0,
        'total_estimates' => 0,
        'total_paid' => 0,
        'total_pending' => 0
    ];
}

// --- HEADER (Cabecera gradient, botones iguales, bilingüe) ---
includeHeader($t['web_title']);
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header Mejorado -->
    <nav class="bg-gradient-to-r from-gray-900 to-gray-800 px-4 py-6 flex flex-col md:flex-row items-center justify-between shadow-lg">
        <div class="flex items-center gap-3 mb-4 md:mb-0">
            <span class="text-2xl font-bold text-company-gold">CNA</span>
            <span class="text-white font-bold text-xl">UPHOLSTERY</span>
            <span class="hidden md:inline text-white text-lg font-light ml-4"><?php echo $t['admin_panel']; ?></span>
        </div>
        <div class="flex gap-2 w-full md:w-auto justify-center md:justify-end">
            <a href="create-estimate.php" class="min-w-[160px] text-center bg-company-gold hover:bg-yellow-600 text-gray-900 font-semibold px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow">
                <i class="fas fa-file-invoice-dollar"></i>
                <?php echo $t['new_estimate']; ?>
            </a>
            <a href="create-invoice.php" class="min-w-[160px] text-center bg-white hover:bg-gray-200 text-gray-900 font-semibold px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow">
                <i class="fas fa-file-alt"></i>
                <?php echo $t['new_invoice']; ?>
            </a>
            <a href="upload-project.php" class="min-w-[160px] text-center bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow">
                <i class="fas fa-cloud-upload-alt"></i>
                <?php echo $t['upload_project']; ?>
            </a>
            <a href="../logout.php" class="min-w-[160px] text-center bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow">
                <i class="fas fa-sign-out-alt"></i>
                <?php echo $t['logout']; ?>
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <?php displaySessionAlert(); ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600"><?php echo $t['invoices_30']; ?></p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_invoices'] ?? 0; ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-calculator text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600"><?php echo $t['estimates_30']; ?></p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_estimates'] ?? 0; ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600"><?php echo $t['total_paid']; ?></p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo formatPrice($stats['total_paid'] ?? 0); ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600"><?php echo $t['total_pending']; ?></p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo formatPrice($stats['total_pending'] ?? 0); ?></p>
                </div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['search']; ?></label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="<?php echo $t['search_placeholder']; ?>" class="input-field w-64">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['type']; ?></label>
                        <select name="tipo" class="input-field">
                            <option value="all" <?php echo $tipo === 'all' ? 'selected' : ''; ?>><?php echo $t['all']; ?></option>
                            <option value="invoice" <?php echo $tipo === 'invoice' ? 'selected' : ''; ?>><?php echo $t['invoice']; ?></option>
                            <option value="estimate" <?php echo $tipo === 'estimate' ? 'selected' : ''; ?>><?php echo $t['estimate']; ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['status']; ?></label>
                        <select name="estado" class="input-field">
                            <option value="all" <?php echo $estado === 'all' ? 'selected' : ''; ?>><?php echo $t['all']; ?></option>
                            <option value="pendiente" <?php echo $estado === 'pendiente' ? 'selected' : ''; ?>><?php echo $t['pending']; ?></option>
                            <option value="pagado" <?php echo $estado === 'pagado' ? 'selected' : ''; ?>><?php echo $t['paid']; ?></option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn-primary min-w-[120px]">
                            <i class="fas fa-search mr-2"></i><?php echo $t['filter']; ?>
                        </button>
                    </div>
                    <?php if (!empty($search) || $tipo !== 'all' || $estado !== 'all'): ?>
                        <div>
                            <a href="admin-dashboard.php?lang=<?php echo $lang; ?>" class="btn-secondary min-w-[120px]">
                                <i class="fas fa-times mr-2"></i><?php echo $t['clear']; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Lista de documentos -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?php echo $t['documents']; ?> (<?php echo number_format($total_documents); ?>)
                </h3>
            </div>

            <?php if (empty($documents)): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-500 mb-2"><?php echo $t['no_documents']; ?></h3>
                    <p class="text-gray-400 mb-6">
                        <?php if (!empty($search) || $tipo !== 'all' || $estado !== 'all'): ?>
                            <?php echo $t['no_documents_search']; ?>
                        <?php else: ?>
                            <?php echo $t['no_documents_create']; ?>
                        <?php endif; ?>
                    </p>
                    <div class="space-x-4">
                        <a href="../create-invoice.php" class="btn-primary min-w-[140px]">
                            <i class="fas fa-plus mr-2"></i><?php echo $t['new_invoice']; ?>
                        </a>
                        <a href="../create-estimate.php" class="bg-company-gold hover:bg-yellow-600 text-gray-900 font-semibold py-2 px-4 rounded-lg min-w-[140px] transition duration-200">
                            <i class="fas fa-calculator mr-2"></i><?php echo $t['new_estimate']; ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo $t['document']; ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo $t['client']; ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo $t['date']; ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo $t['status']; ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo $t['total']; ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($documents as $doc): ?>
                                <tr class="table-row">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <?php if ($doc['tipo'] === 'invoice'): ?>
                                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-file-invoice text-blue-600"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-calculator text-yellow-600"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    #<?php echo htmlspecialchars($doc['numero_documento']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo $t['document_types'][$doc['tipo']]; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($doc['cliente_nombre'] ?? 'Sin cliente'); ?>
                                        </div>
                                        <?php if (!empty($doc['telefono'])): ?>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($doc['telefono']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($doc['fecha'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        // Badge color según estado
                                        if ($doc['estado'] === 'pagado') {
                                            $badge_class = 'bg-green-100 text-green-800';
                                        } elseif ($doc['estado'] === 'confirmado') {
                                            $badge_class = 'bg-blue-100 text-blue-800';
                                        } else {
                                            $badge_class = 'bg-yellow-100 text-yellow-800';
                                        }

                                        // Texto del estado (sin warning)
                                        $estado_texto = isset($t['document_status'][$doc['estado']]) ? $t['document_status'][$doc['estado']] : ucfirst($doc['estado']);
                                        ?>

                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $badge_class; ?>">
                                            <?php echo $estado_texto; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo formatPrice($doc['total']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="view-document.php?id=<?php echo $doc['id']; ?>"
                                                class="text-blue-600 hover:text-blue-900" title="<?php echo $t['view']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit-document.php?id=<?php echo $doc['id']; ?>"
                                                class="text-yellow-600 hover:text-yellow-900" title="<?php echo $t['edit']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="generate-image.php?id=<?php echo $doc['id']; ?>" target="_blank"
                                                class="text-green-600 hover:text-green-900" title="<?php echo $t['download_img']; ?>">
                                                <i class="fas fa-image"></i>
                                            </a>
                                            <button type="button"
                                                class="delete-btn text-red-600 hover:text-red-900"
                                                data-id="<?php echo $doc['id']; ?>"
                                                data-number="<?php echo htmlspecialchars($doc['numero_documento']); ?>"
                                                data-type="<?php echo $t['document_types'][$doc['tipo']]; ?>"
                                                title="<?php echo $t['delete']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 bg-gray-50 border-t flex flex-col md:flex-row items-center justify-between">
                        <div class="text-sm text-gray-700 mb-2 md:mb-0">
                            <?php echo $t['showing']; ?> <?php echo (($page - 1) * ITEMS_PER_PAGE) + 1; ?> -
                            <?php echo min($page * ITEMS_PER_PAGE, $total_documents); ?> <?php echo $t['of']; ?>
                            <?php echo number_format($total_documents); ?> <?php echo $t['documents']; ?>
                        </div>
                        <div class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                                    class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    <i class="fas fa-chevron-left mr-1"></i><?php echo $t['previous']; ?>
                                </a>
                            <?php endif; ?>
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                    class="px-3 py-2 text-sm border rounded-md <?php echo $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-300 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                                    class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    <?php echo $t['next']; ?><i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var lang = '<?php echo $lang; ?>';
        var confirmText = lang === 'en' ? '<?php echo $t['confirm_delete']; ?>' : '<?php echo $t['confirm_delete']; ?>';
        var undoText = lang === 'en' ? '<?php echo $t['cannot_undo']; ?>' : '<?php echo $t['cannot_undo']; ?>';
        var confirmBtnText = lang === 'en' ? 'Yes, delete' : 'Sí, eliminar';
        var cancelBtnText = lang === 'en' ? 'Cancel' : 'Cancelar';

        document.querySelectorAll('.delete-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = btn.getAttribute('data-id');
                var number = btn.getAttribute('data-number');
                var type = btn.getAttribute('data-type');
                Swal.fire({
                    title: `${confirmText} ${type} #${number}?`,
                    text: undoText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: cancelBtnText
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'delete-document.php?id=' + id;
                    }
                });
            });
        });
    });
</script>

<?php includeFooter(); ?>