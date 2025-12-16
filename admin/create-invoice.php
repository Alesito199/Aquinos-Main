<?php

/**
 * Crear nueva factura
 * CNA Upholstery System
 */

require_once '../config/config.php';


$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'es';
$_SESSION['lang'] = $lang;

$t = [
    'es' => [
        'web_title' => 'Crear Nueva Factura',
        'admin_panel' => 'Panel de Administración',
        'subtitle' => 'Generar factura para cliente',
        'home' => 'Inicio',
        'view_invoices' => 'Ver Facturas',
        'date' => 'Fecha de la Factura',
        'number' => 'Número de Factura',
        'auto_generated' => 'Se generará automáticamente',
        'client_info' => 'Información del Cliente',
        'select_client' => 'Seleccionar Cliente',
        'new_client' => 'Nuevo Cliente',
        'client_name' => 'Nombre del Cliente',
        'client_phone' => 'Teléfono',
        'client_email' => 'Email',
        'client_address' => 'Dirección',
        'items_title' => 'Items de la Factura',
        'add_item' => 'Agregar Item',
        'description' => 'Descripción',
        'quantity' => 'Cantidad',
        'unit_price' => 'Precio Unitario',
        'total' => 'Total',
        'action' => 'Acción',
        'notes' => 'Notas Adicionales',
        'notes_placeholder' => 'Información adicional sobre la factura...',
        'subtotal' => 'Subtotal',
        'tax' => TAX_LABEL,
        'total_label' => 'Total',
        'cancel' => 'Cancelar',
        'create_invoice' => 'Crear Factura',
        'client_required' => 'El nombre del cliente es requerido',
        'email_invalid' => 'El email no es válido',
        'item_required' => 'Debe agregar al menos un item a la factura',
        'item_complete' => 'Debe completar al menos un item con descripción, cantidad y precio',
        'delete_item' => '¿Estás seguro de eliminar este item?',
        'success_title' => '¡Éxito!',
        'success_msg' => 'Factura creada exitosamente',
        'error_title' => 'Error',
        'change_lang' => 'Cambiar idioma',
        'lang_es' => 'Español',
        'lang_en' => 'English',
    ],
    'en' => [
        'web_title' => 'Create New Invoice',
        'admin_panel' => 'Admin Panel',
        'subtitle' => 'Generate invoice for client',
        'home' => 'Home',
        'view_invoices' => 'View Invoices',
        'date' => 'Invoice Date',
        'number' => 'Invoice Number',
        'auto_generated' => 'Will be auto-generated',
        'client_info' => 'Client Information',
        'select_client' => 'Select Client',
        'new_client' => 'New Client',
        'client_name' => 'Client Name',
        'client_phone' => 'Phone',
        'client_email' => 'Email',
        'client_address' => 'Address',
        'items_title' => 'Invoice Items',
        'add_item' => 'Add Item',
        'description' => 'Description',
        'quantity' => 'Quantity',
        'unit_price' => 'Unit Price',
        'total' => 'Total',
        'action' => 'Action',
        'notes' => 'Additional Notes',
        'notes_placeholder' => 'Extra information about the invoice...',
        'subtotal' => 'Subtotal',
        'tax' => TAX_LABEL,
        'total_label' => 'Total',
        'cancel' => 'Cancel',
        'create_invoice' => 'Create Invoice',
        'client_required' => 'Client name is required',
        'email_invalid' => 'Email is not valid',
        'item_required' => 'You must add at least one item to the invoice',
        'item_complete' => 'Complete at least one item with description, quantity and price',
        'delete_item' => 'Are you sure you want to delete this item?',
        'success_title' => 'Success!',
        'success_msg' => 'Invoice created successfully',
        'error_title' => 'Error',
        'change_lang' => 'Change language',
        'lang_es' => 'Español',
        'lang_en' => 'English',
    ]
][$lang];

includeHeader($t['web_title']);
// Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDB();
        $pdo->beginTransaction();

        $cliente_data = [
            'nombre' => cleanInput($_POST['cliente_nombre']),
            'telefono' => cleanInput($_POST['cliente_telefono']),
            'email' => cleanInput($_POST['cliente_email']),
            'direccion' => cleanInput($_POST['cliente_direccion'])
        ];

        if (empty($cliente_data['nombre'])) {
            throw new Exception($t['client_required']);
        }
        if (!empty($cliente_data['email']) && !validateEmail($cliente_data['email'])) {
            throw new Exception($t['email_invalid']);
        }

        $cliente_id = null;
        if (!empty($_POST['cliente_id']) && $_POST['cliente_id'] !== 'nuevo') {
            $cliente_id = (int)$_POST['cliente_id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO clientes (nombre, telefono, email, direccion) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cliente_data['nombre'], $cliente_data['telefono'], $cliente_data['email'], $cliente_data['direccion']]);
            $cliente_id = $pdo->lastInsertId();
        }

        $numero_documento = generateDocumentNumber('invoice');
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
            throw new Exception($t['item_required']);
        }

        $impuestos = calculateTax($subtotal);
        $total = $subtotal + $impuestos;

        $stmt = $pdo->prepare("
            INSERT INTO documentos (numero_documento, tipo, fecha, estado, subtotal, impuestos, total, id_cliente) 
            VALUES (?, 'invoice', ?, 'pendiente', ?, ?, ?, ?)
        ");

        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $stmt->execute([$numero_documento, $fecha, $subtotal, $impuestos, $total, $cliente_id]);
        $documento_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO items_documento (id_documento, descripcion, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->execute([$documento_id, $item['descripcion'], $item['cantidad'], $item['precio_unitario'], $item['total']]);
        }

        $pdo->commit();

        // SweetAlert2 de éxito y redirección
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'success',
                    title: '" . $t['success_title'] . "',
                    text: '" . $t['success_msg'] . "',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'view-document.php?id={$documento_id}&lang={$lang}';
                });
            }
        </script>";
        exit;
    } catch (Exception $e) {
        if (isset($pdo)) $pdo->rollBack();
        $error_message = $e->getMessage();
    }
}

$clientes = getAllClients();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

<div class="min-h-screen bg-gray-50">
    <nav class="bg-gradient-to-r from-gray-900 to-gray-800 px-4 py-4 flex items-center justify-between shadow-lg relative">
        <!-- Logo -->
        <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-company-gold">CNA</span>
            <span class="text-white font-bold text-xl">UPHOLSTERY</span>
        </div>
        <!-- Desktop Menu -->
        <div class="hidden md:flex gap-2 items-center">
            <a href="index.php?lang=<?php echo $lang; ?>" class="min-w-[120px] text-center bg-white hover:bg-gray-200 text-gray-900 font-semibold px-4 py-2 rounded-lg shadow transition duration-200 flex items-center gap-2">
                <i class="fas fa-home"></i> <?php echo $t['home']; ?>
            </a>
            <div>
                <form method="get" id="langForm" class="inline">
                    <select name="lang" onchange="document.getElementById('langForm').submit()" class="input-field h-10 w-28 bg-gray-800 text-white border-gray-700">
                        <option value="es" <?php if ($lang === 'es') echo 'selected'; ?>><?php echo $t['lang_es']; ?></option>
                        <option value="en" <?php if ($lang === 'en') echo 'selected'; ?>><?php echo $t['lang_en']; ?></option>
                    </select>
                </form>
            </div>
        </div>
        <!-- Hamburger Button for Mobile -->
        <button class="md:hidden text-white focus:outline-none" onclick="toggleMobileMenu()" aria-label="Open menu">
            <i class="fas fa-bars text-2xl"></i>
        </button>
        <!-- Mobile Menu (hidden by default) -->
        <div id="mobileMenu"
            class="absolute top-full left-0 w-full bg-gray-900 text-white z-40 shadow-lg hidden flex-col py-2 animate__animated animate__fadeInDown">
            <a href="index.php?lang=<?php echo $lang; ?>" class="block px-4 py-3 border-b border-gray-700 flex items-center gap-2">
                <i class="fas fa-home"></i> <?php echo $t['home']; ?>
            </a>
            <form method="get" id="langFormMobile" class="block px-4 py-3">
                <select name="lang" onchange="document.getElementById('langFormMobile').submit()" class="input-field h-10 w-full bg-gray-800 text-white border-gray-700">
                    <option value="es" <?php if ($lang === 'es') echo 'selected'; ?>><?php echo $t['lang_es']; ?></option>
                    <option value="en" <?php if ($lang === 'en') echo 'selected'; ?>><?php echo $t['lang_en']; ?></option>
                </select>
            </form>
        </div>
    </nav>

    <div class="container mx-auto px-2 md:px-4 py-8">
        <?php if (isset($error_message)): ?>
            <script>
                window.onload = function() {
                    Swal.fire({
                        icon: 'error',
                        title: '<?php echo $t['error_title']; ?>',
                        text: '<?php echo htmlspecialchars($error_message); ?>',
                        confirmButtonText: 'OK'
                    });
                }
            </script>
        <?php endif; ?>

        <div class="max-w-4xl mx-auto">
            <form method="POST" id="invoiceForm" class="space-y-8">
                <!-- Información del documento -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['date']; ?></label>
                            <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="input-field bg-gray-200 p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['number']; ?></label>
                            <input type="text" value="<?php echo generateDocumentNumber('invoice'); ?>" class="input-field bg-gray-200 p-2" readonly>
                            <p class="text-xs text-gray-500 mt-1"><?php echo $t['auto_generated']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Información del cliente -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo $t['client_info']; ?></h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['select_client']; ?></label>
                        <select id="cliente_selector" class="input-field" onchange="handleClientChange()">
                            <option value="nuevo"><?php echo $t['new_client']; ?></option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>"
                                    data-telefono="<?php echo htmlspecialchars($cliente['telefono']); ?>"
                                    data-email="<?php echo htmlspecialchars($cliente['email']); ?>"
                                    data-direccion="<?php echo htmlspecialchars($cliente['direccion']); ?>">
                                    <?php echo htmlspecialchars($cliente['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="cliente_id" id="cliente_id" value="nuevo">
                    </div>
                    <!-- SIEMPRE mostrar los inputs, pero si es cliente existente, se autocompletan (y pueden editarse) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['client_name']; ?> *</label>
                            <input type="text" name="cliente_nombre" id="cliente_nombre" class="input-field bg-gray-200 p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['client_phone']; ?></label>
                            <input type="tel" name="cliente_telefono" id="cliente_telefono" class="input-field bg-gray-200 p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['client_email']; ?></label>
                            <input type="email" name="cliente_email" id="cliente_email" class="input-field bg-gray-200 p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $t['client_address']; ?></label>
                            <input type="text" name="cliente_direccion" id="cliente_direccion" class="input-field bg-gray-200 p-2">
                        </div>
                    </div>
                </div>

                <!-- Items de la factura -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-2">
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo $t['items_title']; ?></h3>
                        <button type="button" onclick="addItem()" class="btn-success">
                            <i class="fas fa-plus mr-1"></i><?php echo $t['add_item']; ?>
                        </button>
                    </div>
                    <!-- Tabla responsiva -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full block md:table">
                            <thead class="bg-gray-50 block md:table-header-group">
                                <tr class="md:table-row flex flex-col md:flex-row">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider block md:table-cell"><?php echo $t['description']; ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider block md:table-cell"><?php echo $t['quantity']; ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider block md:table-cell"><?php echo $t['unit_price']; ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider block md:table-cell"><?php echo $t['total']; ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider block md:table-cell"><?php echo $t['action']; ?></th>
                                </tr>
                            </thead>
                            <tbody id="itemsTable" class="block md:table-row-group">
                                <!-- Los items se agregan dinámicamente aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notas adicionales -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2 md:mb-0 md:mr-4 min-w-[160px]">
                            <?php echo $t['notes']; ?>
                        </label>
                        <textarea name="notas"
                            class="input-field bg-gray-200 p-2 w-full h-24 md:h-28 resize-y md:resize-none rounded-lg"
                            placeholder="<?php echo $t['notes_placeholder']; ?>"></textarea>
                    </div>
                </div>

                <!-- Totales -->
                <div class="bg-white rounded-lg shadow-lg p-6 flex justify-end">
                    <div class="w-full md:w-64">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between mb-2">
                                <span class="font-medium"><?php echo $t['subtotal']; ?>:</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="font-medium"><?php echo $t['tax']; ?>:</span>
                                <span id="tax">$0.00</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span><?php echo $t['total_label']; ?>:</span>
                                <span id="total">$0.00</span>
                            </div>
                            <input type="hidden" id="subtotal_input" name="subtotal">
                            <input type="hidden" id="tax_input" name="tax">
                            <input type="hidden" id="total_input" name="total">
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-4">
                    <a href="index.php?lang=<?php echo $lang; ?>" class="btn-secondary"><?php echo $t['cancel']; ?></a>
                    <button type="submit" class="bg-company-gold hover:bg-yellow-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i><?php echo $t['create_invoice']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let itemCounter = 0;

    // SIEMPRE mostrar los inputs, solo autocompletar si es cliente existente
    function handleClientChange() {
        const selector = document.getElementById('cliente_selector');
        const selectedOption = selector.options[selector.selectedIndex];

        if (selector.value === 'nuevo') {
            document.getElementById('cliente_id').value = 'nuevo';
            document.getElementById('cliente_nombre').value = '';
            document.getElementById('cliente_telefono').value = '';
            document.getElementById('cliente_email').value = '';
            document.getElementById('cliente_direccion').value = '';
        } else {
            document.getElementById('cliente_id').value = selector.value;
            document.getElementById('cliente_nombre').value = selectedOption.textContent;
            document.getElementById('cliente_telefono').value = selectedOption.dataset.telefono || '';
            document.getElementById('cliente_email').value = selectedOption.dataset.email || '';
            document.getElementById('cliente_direccion').value = selectedOption.dataset.direccion || '';
        }
    }

    // Función para agregar nuevo item
    function addItem() {
        const table = document.getElementById('itemsTable');
        const row = document.createElement('tr');
        row.className = 'item-row border-b flex flex-col md:table-row';
        row.innerHTML = `
        <td class="px-4 py-3 block md:table-cell">
            <input type="text" name="items[${itemCounter}][descripcion]" class="input-field"
                   placeholder="Ej: King Size Headboard Reupholstered (New)" required>
        </td>
        <td class="px-4 py-3 block md:table-cell">
            <input type="number" name="items[${itemCounter}][cantidad]" class="quantity-input input-field w-20"
                   step="0.01" min="0" value="1" onchange="calculateRowTotal(this.closest('tr'))" required>
        </td>
        <td class="px-4 py-3 block md:table-cell">
            <input type="number" name="items[${itemCounter}][precio_unitario]" class="unit-price-input input-field w-24"
                   step="0.01" min="0" placeholder="0.00" onchange="calculateRowTotal(this.closest('tr'))" required>
        </td>
        <td class="px-4 py-3 block md:table-cell">
            <span class="row-total font-medium">$0.00</span>
        </td>
        <td class="px-4 py-3 block md:table-cell">
            <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
        table.appendChild(row);
        itemCounter++;

        if (itemCounter === 1) {
            row.querySelector('input[type="text"]').focus();
        }
    }

    // Función para remover item con SweetAlert2
    function removeItem(button) {
        Swal.fire({
            icon: 'warning',
            title: '<?php echo $t['delete_item']; ?>',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<?php echo $t['delete_item']; ?>',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                button.closest('tr').remove();
                calculateDocumentTotals();
            }
        });
    }

    // Agregar primer item automáticamente
    document.addEventListener('DOMContentLoaded', function() {
        addItem();
    });

    // Validar formulario antes de enviar con SweetAlert2
    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.item-row');
        if (items.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: '<?php echo $t['error_title']; ?>',
                text: '<?php echo $t['item_required']; ?>'
            });
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
            Swal.fire({
                icon: 'error',
                title: '<?php echo $t['error_title']; ?>',
                text: '<?php echo $t['item_complete']; ?>'
            });
            return false;
        }
    });

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

        document.getElementById('subtotal_input').value = subtotal.toFixed(2);
        document.getElementById('tax_input').value = tax.toFixed(2);
        document.getElementById('total_input').value = total.toFixed(2);
    }

    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
        // Opcional: cerrar el menú al hacer click fuera de él
        document.body.onclick = function(e) {
            if (!e.target.closest('#mobileMenu') && !e.target.closest('button[aria-label="Open menu"]')) {
                menu.classList.add('hidden');
            }
        }
    }
</script>

<?php includeFooter(); ?>