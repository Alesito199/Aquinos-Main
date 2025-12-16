<?php
/**
 * Gestión de clientes
 * CNA Upholstery System
 */

require_once '../config/config.php';
require_once '../config/functions.php';
requireAdmin();
// Obtener parámetros de filtro
$search = cleanInput($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

// Construir query base
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(nombre LIKE ? OR telefono LIKE ? OR email LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    $pdo = getDB();
    
    // Contar total de clientes
    $count_sql = "SELECT COUNT(*) as total FROM clientes {$where_clause}";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_clients = $stmt->fetch()['total'];
    
    // Calcular paginación
    $total_pages = ceil($total_clients / ITEMS_PER_PAGE);
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    
    // Obtener clientes con estadísticas
    $sql = "
        SELECT 
            c.id,
            c.nombre,
            c.telefono,
            c.email,
            c.direccion,
            c.created_at,
            COUNT(d.id) as total_documentos,
            SUM(CASE WHEN d.tipo = 'invoice' THEN 1 ELSE 0 END) as total_facturas,
            SUM(CASE WHEN d.tipo = 'estimate' THEN 1 ELSE 0 END) as total_presupuestos,
            SUM(CASE WHEN d.estado = 'pagado' THEN d.total ELSE 0 END) as total_pagado,
            SUM(CASE WHEN d.estado = 'pendiente' THEN d.total ELSE 0 END) as total_pendiente,
            MAX(d.fecha) as ultima_actividad
        FROM clientes c 
        LEFT JOIN documentos d ON c.id = d.id_cliente 
        {$where_clause}
        GROUP BY c.id, c.nombre, c.telefono, c.email, c.direccion, c.created_at
        ORDER BY c.created_at DESC 
        LIMIT " . ITEMS_PER_PAGE . " OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clients = $stmt->fetchAll();
    
    // Estadísticas generales
    $stats_sql = "
        SELECT 
            COUNT(*) as total_clientes,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as nuevos_mes,
            COUNT(CASE WHEN email IS NOT NULL AND email != '' THEN 1 END) as con_email,
            COUNT(CASE WHEN telefono IS NOT NULL AND telefono != '' THEN 1 END) as con_telefono
        FROM clientes
    ";
    $stmt = $pdo->query($stats_sql);
    $stats = $stmt->fetch();
    
} catch (Exception $e) {
    $error_message = "Error al cargar clientes: " . $e->getMessage();
    $clients = [];
    $total_clients = 0;
    $total_pages = 0;
    $stats = [
        'total_clientes' => 0,
        'nuevos_mes' => 0,
        'con_email' => 0,
        'con_telefono' => 0
    ];
}

includeHeader('Gestión de Clientes - CNA Upholstery');
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gestión de Clientes</h1>
                    <p class="text-gray-600 mt-1">Administrar información de clientes</p>
                </div>
                <div class="flex space-x-4">
                    <a href="../index.php" class="btn-secondary">
                        <i class="fas fa-home mr-2"></i>Inicio
                    </a>
                    <a href="index.php" class="btn-secondary">
                        <i class="fas fa-file-invoice mr-2"></i>Documentos
                    </a>
                    <button onclick="showAddClientModal()" class="btn-success">
                        <i class="fas fa-plus mr-2"></i>Nuevo Cliente
                    </button>
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

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Clientes</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_clientes']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-user-plus text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Nuevos (30 días)</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['nuevos_mes']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-envelope text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Con Email</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['con_email']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-phone text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Con Teléfono</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['con_telefono']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Cliente</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nombre, teléfono o email..." class="input-field w-64">
                    </div>
                    
                    <div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </div>
                    
                    <?php if (!empty($search)): ?>
                    <div>
                        <a href="clients.php" class="btn-secondary">
                            <i class="fas fa-times mr-2"></i>Limpiar
                        </a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Lista de clientes -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-900">
                    Clientes (<?php echo number_format($total_clients); ?>)
                </h3>
            </div>

            <?php if (empty($clients)): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-user-friends text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-500 mb-2">No hay clientes</h3>
                    <p class="text-gray-400 mb-6">
                        <?php if (!empty($search)): ?>
                            No se encontraron clientes que coincidan con tu búsqueda.
                        <?php else: ?>
                            Comienza agregando tu primer cliente.
                        <?php endif; ?>
                    </p>
                    <button onclick="showAddClientModal()" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>Agregar Cliente
                    </button>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estadísticas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Actividad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($clients as $client): ?>
                            <tr class="table-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($client['nombre']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Cliente desde <?php echo date('d/m/Y', strtotime($client['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php if (!empty($client['telefono'])): ?>
                                            <div><i class="fas fa-phone text-gray-400 mr-2"></i><?php echo htmlspecialchars($client['telefono']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($client['email'])): ?>
                                            <div><i class="fas fa-envelope text-gray-400 mr-2"></i><?php echo htmlspecialchars($client['email']); ?></div>
                                        <?php endif; ?>
                                        <?php if (empty($client['telefono']) && empty($client['email'])): ?>
                                            <span class="text-gray-400">Sin contacto</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div><?php echo $client['total_documentos']; ?> documentos</div>
                                    <div class="text-xs">
                                        <?php echo $client['total_facturas']; ?> facturas | 
                                        <?php echo $client['total_presupuestos']; ?> presupuestos
                                    </div>
                                    <?php if ($client['total_pagado'] > 0 || $client['total_pendiente'] > 0): ?>
                                    <div class="text-xs mt-1">
                                        <span class="text-green-600">Pagado: <?php echo formatPrice($client['total_pagado']); ?></span><br>
                                        <span class="text-orange-600">Pendiente: <?php echo formatPrice($client['total_pendiente']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($client['ultima_actividad']): ?>
                                        <?php echo date('d/m/Y', strtotime($client['ultima_actividad'])); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">Sin actividad</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="viewClient(<?php echo $client['id']; ?>)" 
                                               class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editClient(<?php echo $client['id']; ?>)" 
                                               class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="../create-invoice.php?client_id=<?php echo $client['id']; ?>" 
                                           class="text-green-600 hover:text-green-900" title="Nueva factura">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                        <a href="../create-estimate.php?client_id=<?php echo $client['id']; ?>" 
                                           class="text-purple-600 hover:text-purple-900" title="Nuevo presupuesto">
                                            <i class="fas fa-calculator"></i>
                                        </a>
                                        <?php if ($client['total_documentos'] == 0): ?>
                                        <button onclick="confirmDeleteClient(<?php echo $client['id']; ?>, '<?php echo htmlspecialchars($client['nombre']); ?>')" 
                                               class="text-red-600 hover:text-red-900" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 bg-gray-50 border-t flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrando <?php echo (($page - 1) * ITEMS_PER_PAGE) + 1; ?> - 
                        <?php echo min($page * ITEMS_PER_PAGE, $total_clients); ?> de 
                        <?php echo number_format($total_clients); ?> clientes
                    </div>
                    
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                               class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                <i class="fas fa-chevron-left mr-1"></i>Anterior
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
                                Siguiente<i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar cliente -->
<div id="clientModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Nuevo Cliente</h3>
            
            <form id="clientForm" onsubmit="saveClient(event)">
                <input type="hidden" id="clientId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                    <input type="text" id="clientName" class="input-field" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                    <input type="tel" id="clientPhone" class="input-field">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="clientEmail" class="input-field">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                    <textarea id="clientAddress" rows="3" class="input-field"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeClientModal()" class="btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-success">
                        Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddClientModal() {
    document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
    document.getElementById('clientForm').reset();
    document.getElementById('clientId').value = '';
    document.getElementById('clientModal').classList.remove('hidden');
    document.getElementById('clientName').focus();
}

function closeClientModal() {
    document.getElementById('clientModal').classList.add('hidden');
}

function editClient(clientId) {
    // Esta función se implementaría para cargar datos del cliente
    alert('Función de editar cliente - ID: ' + clientId);
}

function viewClient(clientId) {
    // Esta función se implementaría para ver detalles del cliente
    alert('Ver detalles del cliente - ID: ' + clientId);
}

function saveClient(event) {
    event.preventDefault();
    
    const formData = {
        id: document.getElementById('clientId').value,
        name: document.getElementById('clientName').value.trim(),
        phone: document.getElementById('clientPhone').value.trim(),
        email: document.getElementById('clientEmail').value.trim(),
        address: document.getElementById('clientAddress').value.trim()
    };
    
    if (!formData.name) {
        alert('El nombre del cliente es requerido');
        return;
    }
    
    // Aquí enviarías los datos al servidor
    console.log('Datos del cliente:', formData);
    
    // Por ahora, solo mostrar mensaje y cerrar modal
    alert('Cliente guardado exitosamente');
    closeClientModal();
    window.location.reload();
}

function confirmDeleteClient(clientId, clientName) {
    if (confirm(`¿Estás seguro de eliminar el cliente "${clientName}"?\n\nEsta acción no se puede deshacer.`)) {
        // Aquí enviarías la petición de eliminación
        alert('Cliente eliminado - ID: ' + clientId);
        window.location.reload();
    }
}

// Cerrar modal al hacer clic fuera de él
document.getElementById('clientModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeClientModal();
    }
});
</script>

<?php includeFooter(); ?>