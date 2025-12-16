<?php
/**
 * Editar documento (factura o presupuesto)
 * CNA Upholstery System
 */

require_once '../config/config.php';

// Verificar que se proporcione ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('admin/index.php', 'Documento no encontrado', 'error');
}

$document_id = (int)$_GET['id'];

// Obtener documento con items
$document = getDocumentWithItems($document_id);

if (!$document) {
    redirect('admin/index.php', 'Documento no encontrado', 'error');
}

// Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDB();
        $pdo->beginTransaction();

        // Limpiar y validar datos del cliente
        $cliente_data = [
            'nombre' => cleanInput($_POST['cliente_nombre']),
            'telefono' => cleanInput($_POST['cliente_telefono']),
            'email' => cleanInput($_POST['cliente_email']),
            'direccion' => cleanInput($_POST['cliente_direccion'])
        ];

        // Validar datos requeridos
        if (empty($cliente_data['nombre'])) {
            throw new Exception('El nombre del cliente es requerido');
        }

        // Validar email si se proporciona
        if (!empty($cliente_data['email']) && !validateEmail($cliente_data['email'])) {
            throw new Exception('El email no es válido');
        }

        // Actualizar cliente
        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET nombre = ?, telefono = ?, email = ?, direccion = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $cliente_data['nombre'], 
            $cliente_data['telefono'], 
            $cliente_data['email'], 
            $cliente_data['direccion'],
            $document['id_cliente']
        ]);

        // Calcular totales
        $subtotal = 0;
        $items = [];

        if (!empty($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                if (!empty($item['descripcion']) && !empty($item['cantidad']) && !empty($item['precio_unitario'])) {
                    $cantidad = (float)$item['cantidad'];
                    $precio_unitario = (float)$item['precio_unitario'];
                    $total_item = $cantidad * $precio_unitario;

                    $items[] = [
                        'descripcion' => cleanInput($item['descripcion']),
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precio_unitario,
                        'total' => $total_item
                    ];

                    $subtotal += $total_item;
                }
            }
        }

        if (empty($items)) {
            throw new Exception('Debe agregar al menos un item al documento');
        }

        $impuestos = calculateTax($subtotal);
        $total = $subtotal + $impuestos;

        // Actualizar documento
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : $document['fecha'];
        $notas = cleanInput($_POST['notas'] ?? '');

        $stmt = $pdo->prepare("
            UPDATE documentos 
            SET fecha = ?, subtotal = ?, impuestos = ?, total = ?, notas = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$fecha, $subtotal, $impuestos, $total, $notas, $document_id]);

        // Eliminar items existentes
        $stmt = $pdo->prepare("DELETE FROM items_documento WHERE id_documento = ?");
        $stmt->execute([$document_id]);

        // Insertar nuevos items
        $stmt = $pdo->prepare("INSERT INTO items_documento (id_documento, descripcion, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $stmt->execute([$document_id, $item['descripcion'], $item['cantidad'], $item['precio_unitario'], $item['total']]);
        }

        $pdo->commit();

        // Redireccionar a ver el documento
        redirect("view-document.php?id={$document_id}", "Documento actualizado exitosamente", "success");

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = $e->getMessage();
    }
}

// Obtener clientes para el selector
$clientes = getAllClients();

$page_title = 'Editar ' . ($document['tipo'] === 'invoice' ? 'Factura' : 'Presupuesto');
includeHeader($page_title);
?>

<!-- SweetAlert2 y FontAwesome -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>

<style>
@media (max-width: 768px) {
    .responsive-note { flex-direction: column !important; align-items: stretch !important; }
    .responsive-note label { margin-bottom: .5rem !important; }
    .responsive-note textarea { min-height: 5rem !important; }
    .responsive-totals { width: 100% !important; }
    .responsive-form { padding: 1rem !important; }
}
</style>

<div class="min-h-screen py-8 bg-gray-50">
    <div class="container mx-auto px-2 md:px-4">

        <!-- Header (NO SE MODIFICA) -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php echo $page_title; ?> #<?php echo htmlspecialchars($document['numero_documento']); ?>
                </h1>
                <p class="text-gray-600 mt-2">Modificar información del documento</p>
            </div>
            <div class="flex space-x-4">
                <a href="view-document.php?id=<?php echo $document['id']; ?>" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-list mr-2"></i>Lista
                </a>
            </div>
        </div>

        <?php displaySessionAlert(); ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="bg-white rounded-lg shadow-lg p-6 responsive-form">
            <form method="POST" id="editForm">

                <!-- Información del documento -->
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del Documento</label>
                        <input type="date" name="fecha" value="<?php echo htmlspecialchars($document['fecha']); ?>" class="input-field bg-gray-200 p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Número de Documento</label>
                        <input type="text" value="<?php echo htmlspecialchars($document['numero_documento']); ?>" class="input-field bg-gray-200 p-2" readonly>
                        <p class="text-sm text-gray-500 mt-1">El número no se puede modificar</p>
                    </div>
                </div>

                <!-- Información del cliente -->
                <div class="border-t pt-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Cliente</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Cliente</label>
                        <select id="cliente_selector" class="input-field" onchange="handleClientChange()">
                            <option value="current">Cliente Actual</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>" 
                                        data-telefono="<?php echo htmlspecialchars($cliente['telefono']); ?>"
                                        data-email="<?php echo htmlspecialchars($cliente['email']); ?>"
                                        data-direccion="<?php echo htmlspecialchars($cliente['direccion']); ?>"
                                        <?php echo $cliente['id'] == $document['id_cliente'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cliente['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Cliente *</label>
                            <input type="text" name="cliente_nombre" id="cliente_nombre"
                                   value="<?php echo htmlspecialchars($document['cliente_nombre'] ?? ''); ?>"
                                   class="input-field bg-gray-200 p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                            <input type="tel" name="cliente_telefono" id="cliente_telefono"
                                   value="<?php echo htmlspecialchars($document['telefono'] ?? ''); ?>"
                                   class="input-field bg-gray-200 p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="cliente_email" id="cliente_email"
                                   value="<?php echo htmlspecialchars($document['email'] ?? ''); ?>"
                                   class="input-field bg-gray-200 p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                            <input type="text" name="cliente_direccion" id="cliente_direccion"
                                   value="<?php echo htmlspecialchars($document['direccion'] ?? ''); ?>"
                                   class="input-field bg-gray-200 p-2">
                        </div>
                    </div>
                </div>

                <!-- Items del documento -->
                <div class="border-t pt-6 mb-8">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-2">
                        <h3 class="text-lg font-semibold text-gray-900">Items del Documento</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php if ($document['tipo'] === 'estimate'): ?>
                            <button type="button" onclick="addPresetItem('Chairs (10 yards Fabric including for love seat)', 4, 100)" class="text-sm bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded">
                                + Chairs Set
                            </button>
                            <button type="button" onclick="addPresetItem('Love Seat', 1, 220)" class="text-sm bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1 rounded">
                                + Love Seat
                            </button>
                            <button type="button" onclick="addPresetItem('EZ Dry foam (3 inches)', 1, 370)" class="text-sm bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-1 rounded">
                                + EZ Dry Foam
                            </button>
                            <?php endif; ?>
                            <button type="button" onclick="addItem()" class="btn-success">
                                <i class="fas fa-plus mr-2"></i>Agregar Item
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTable">
                                <!-- Los items se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notas adicionales horizontal y grande, responsive -->
                <?php if ($document['tipo'] === 'estimate'): ?>
                <div class="border-t pt-6 mb-8">
                    <div class="flex flex-col md:flex-row md:items-center gap-4 responsive-note">
                        <label class="block text-sm font-medium text-gray-700 mb-2 md:mb-0 md:mr-4 min-w-[160px]">
                            Notas Adicionales
                        </label>
                        <textarea name="notas"
                            class="input-field bg-gray-200 p-2 w-full h-24 md:h-28 resize-y md:resize-none rounded-lg"
                            placeholder="Información adicional sobre el presupuesto..."><?php echo htmlspecialchars($document['notas'] ?? ''); ?></textarea>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Totales -->
                <div class="border-t pt-6 mb-8">
                    <div class="flex justify-end">
                        <div class="w-64 responsive-totals">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium">Subtotal:</span>
                                    <span id="subtotal">$0.00</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium"><?php echo TAX_LABEL; ?>:</span>
                                    <span id="tax">$0.00</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold border-t pt-2">
                                    <span>Total:</span>
                                    <span id="total">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex flex-wrap justify-end gap-4">
                    <a href="view-document.php?id=<?php echo $document['id']; ?>" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-warning">
                        <i class="fas fa-save mr-2"></i>Actualizar Documento
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
let itemCounter = 0;

// Datos del documento actual
const currentDocument = <?php echo json_encode($document); ?>;

// Cargar items existentes al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    loadExistingItems();
    calculateDocumentTotals();
});

// Función para cargar items existentes
function loadExistingItems() {
    currentDocument.items.forEach(function(item, index) {
        const table = document.getElementById('itemsTable');
        const row = document.createElement('tr');
        row.className = 'item-row border-b';
        row.innerHTML = `
            <td class="px-4 py-3">
                <input type="text" name="items[${itemCounter}][descripcion]" class="input-field" 
                       value="${escapeHtml(item.descripcion)}" required>
            </td>
            <td class="px-4 py-3">
                <input type="number" name="items[${itemCounter}][cantidad]" class="quantity-input input-field w-20" 
                       step="0.01" min="0" value="${item.cantidad}" onchange="calculateRowTotal(this.closest('tr'))" required>
            </td>
            <td class="px-4 py-3">
                <input type="number" name="items[${itemCounter}][precio_unitario]" class="unit-price-input input-field w-24" 
                       step="0.01" min="0" value="${item.precio_unitario}" onchange="calculateRowTotal(this.closest('tr'))" required>
            </td>
            <td class="px-4 py-3">
                <span class="row-total font-medium">$${parseFloat(item.total).toFixed(2)}</span>
            </td>
            <td class="px-4 py-3">
                <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        table.appendChild(row);
        itemCounter++;
    });
}

// Función para manejar cambio de cliente
function handleClientChange() {
    const selector = document.getElementById('cliente_selector');
    const selectedOption = selector.options[selector.selectedIndex];

    if (selector.value === 'current') {
        // Mantener datos actuales
        return;
    }

    // Cargar datos del cliente seleccionado
    document.getElementById('cliente_nombre').value = selectedOption.textContent;
    document.getElementById('cliente_telefono').value = selectedOption.dataset.telefono || '';
    document.getElementById('cliente_email').value = selectedOption.dataset.email || '';
    document.getElementById('cliente_direccion').value = selectedOption.dataset.direccion || '';
}

// Función para agregar nuevo item
function addItem() {
    const table = document.getElementById('itemsTable');
    const row = document.createElement('tr');
    row.className = 'item-row border-b';
    row.innerHTML = `
        <td class="px-4 py-3">
            <input type="text" name="items[${itemCounter}][descripcion]" class="input-field" 
                   placeholder="Descripción del item..." required>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${itemCounter}][cantidad]" class="quantity-input input-field w-20" 
                   step="0.01" min="0" value="1" onchange="calculateRowTotal(this.closest('tr'))" required>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${itemCounter}][precio_unitario]" class="unit-price-input input-field w-24" 
                   step="0.01" min="0" placeholder="0.00" onchange="calculateRowTotal(this.closest('tr'))" required>
        </td>
        <td class="px-4 py-3">
            <span class="row-total font-medium">$0.00</span>
        </td>
        <td class="px-4 py-3">
            <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    table.appendChild(row);
    itemCounter++;

    // Hacer focus en la descripción del nuevo item
    row.querySelector('input[type="text"]').focus();
}

// Función para agregar item predefinido (solo para presupuestos)
function addPresetItem(descripcion, cantidad, precio) {
    const table = document.getElementById('itemsTable');
    const row = document.createElement('tr');
    row.className = 'item-row border-b';
    row.innerHTML = `
        <td class="px-4 py-3">
            <input type="text" name="items[${itemCounter}][descripcion]" class="input-field" 
                   value="${descripcion}" required>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${itemCounter}][cantidad]" class="quantity-input input-field w-20" 
                   step="0.01" min="0" value="${cantidad}" onchange="calculateRowTotal(this.closest('tr'))" required>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${itemCounter}][precio_unitario]" class="unit-price-input input-field w-24" 
                   step="0.01" min="0" value="${precio}" onchange="calculateRowTotal(this.closest('tr'))" required>
        </td>
        <td class="px-4 py-3">
            <span class="row-total font-medium">$${(cantidad * precio).toFixed(2)}</span>
        </td>
        <td class="px-4 py-3">
            <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    table.appendChild(row);
    itemCounter++;

    calculateDocumentTotals();
}

// Función para remover item
function removeItem(button) {
    if (confirm('¿Estás seguro de eliminar este item?')) {
        button.closest('tr').remove();
        calculateDocumentTotals();
    }
}

// Función para escapar HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Validar formulario antes de enviar
document.getElementById('editForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.item-row');
    if (items.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un item al documento');
        return false;
    }

    let hasValidItems = false;
    items.forEach(function(row) {
        const descripcion = row.querySelector('input[name*="[descripcion]"]').value.trim();
        const cantidad = row.querySelector('input[name*="[cantidad]"]').value;
        const precio = row.querySelector('input[name*="[precio_unitario]"]').value;

        if (descripcion && cantidad && precio) {
            hasValidItems = true;
        }
    });

    if (!hasValidItems) {
        e.preventDefault();
        alert('Debe completar al menos un item con descripción, cantidad y precio');
        return false;
    }
});

// Calcular totales
function calculateRowTotal(row) {
    const cantidad = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const precioUnitario = parseFloat(row.querySelector('.unit-price-input').value) || 0;
    const total = cantidad * precioUnitario;
    row.querySelector('.row-total').textContent = '$' + total.toFixed(2);
    calculateDocumentTotals();
}

function calculateDocumentTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(function(row) {
        const cantidad = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const precioUnitario = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        subtotal += cantidad * precioUnitario;
    });
    const taxRate = <?php echo (TAX_RATE / 100); ?>;
    const tax = subtotal * taxRate;
    const total = subtotal + tax;
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('tax').textContent = '$' + tax.toFixed(2);
    document.getElementById('total').textContent = '$' + total.toFixed(2);
}
</script>

<?php includeFooter(); ?>