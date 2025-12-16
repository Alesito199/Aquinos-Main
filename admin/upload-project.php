<?php
require_once '../config/config.php';
requireAdmin();

$lang = $_SESSION['lang'] ?? $_GET['lang'] ?? 'es';
$texts = [
    'en' => [
        'admin_panel' => 'Home',
        'upload_project' => 'Upload Project',
        'project_gallery' => 'Project Gallery',
        'title_en' => 'Title (English)',
        'title_es' => 'Title (Spanish)',
        'desc_en' => 'Description (English)',
        'desc_es' => 'Description (Spanish)',
        'image_url' => 'Image URL (Google Drive, Dropbox, Imgur, Unsplash, etc)',
        'save' => 'Save',
        'success' => 'Project uploaded successfully!',
        'error' => 'Error uploading project!',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'confirm_delete' => 'Are you sure you want to delete this project?',
        'deleted_success' => 'Project deleted successfully!',
        'deleted_error' => 'Error deleting project!',
        'edit_project' => 'Edit Project',
        'update' => 'Update',
        'updated_success' => 'Project updated successfully!',
        'updated_error' => 'Error updating project!',
        'cancel' => 'Cancel',
        'page' => 'Page',
        'of' => 'of',
        'new_estimate' => 'New Estimate',
        'new_invoice' => 'New Invoice',
        'logout' => 'Logout'
    ],
    'es' => [
        'admin_panel' => 'Inicio',
        'upload_project' => 'Cargar Proyecto',
        'project_gallery' => 'Galería de Proyectos',
        'title_en' => 'Título (Inglés)',
        'title_es' => 'Título (Español)',
        'desc_en' => 'Descripción (Inglés)',
        'desc_es' => 'Descripción (Español)',
        'image_url' => 'URL de Imagen (Google Drive, Dropbox, Imgur, Unsplash, etc)',
        'save' => 'Guardar',
        'success' => '¡Proyecto cargado correctamente!',
        'error' => '¡Error al cargar el proyecto!',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'confirm_delete' => '¿Seguro que deseas eliminar este proyecto?',
        'deleted_success' => '¡Proyecto eliminado correctamente!',
        'deleted_error' => '¡Error al eliminar el proyecto!',
        'edit_project' => 'Editar Proyecto',
        'update' => 'Actualizar',
        'updated_success' => '¡Proyecto actualizado correctamente!',
        'updated_error' => '¡Error al actualizar el proyecto!',
        'cancel' => 'Cancelar',
        'page' => 'Página',
        'of' => 'de',
        'new_estimate' => 'Nuevo Presupuesto',
        'new_invoice' => 'Nueva Factura',
        'logout' => 'Cerrar Sesión'
    ]
];
$t = $texts[$lang];

// FUNCION PARA CONVERTIR LINK DE GOOGLE DRIVE, DROPBOX, ETC
function get_img_embed_url($url)
{
    // Google Drive
    if (preg_match('/drive\.google\.com\/file\/d\/([^\/]+)\//', $url, $match)) {
        return 'https://drive.google.com/uc?export=view&id=' . $match[1];
    }
    // Dropbox
    if (preg_match('/dropbox\.com\/s\/([^\/]+)\/([^?]+)\?dl=0/', $url, $match)) {
        return 'https://www.dropbox.com/s/' . $match[1] . '/' . $match[2] . '?raw=1';
    }
    // Si ya es link directo (Unsplash, Imgur, etc), lo deja igual
    return $url;
}

// --- Procesar acciones AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $pdo = getDB();
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM projects WHERE id=?");
            $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => true, 'message' => $t['deleted_success']]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $t['deleted_error']]);
        }
        exit;
    }
    if ($_POST['action'] === 'edit' && isset($_POST['id'])) {
        try {
            $image_url = get_img_embed_url($_POST['image_url']);
            $stmt = $pdo->prepare("UPDATE projects SET image_url=?, title_en=?, title_es=?, desc_en=?, desc_es=? WHERE id=?");
            $stmt->execute([
                $image_url,
                $_POST['title_en'],
                $_POST['title_es'],
                $_POST['desc_en'],
                $_POST['desc_es'],
                $_POST['id']
            ]);
            echo json_encode(['success' => true, 'message' => $t['updated_success']]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $t['updated_error']]);
        }
        exit;
    }
}

// --- Procesar formulario de carga normal ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    try {
        $pdo = getDB();
        $image_url = get_img_embed_url($_POST['image_url']);
        $stmt = $pdo->prepare("INSERT INTO projects (image_url, title_en, title_es, desc_en, desc_es, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $image_url,
            $_POST['title_en'],
            $_POST['title_es'],
            $_POST['desc_en'],
            $_POST['desc_es']
        ]);
        $success = $t['success'];
    } catch (Exception $e) {
        $error = $t['error'];
    }
}

// --- PAGINACIÓN Y CARGA DE PROYECTOS ---
$projectsPerPage = 9;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $projectsPerPage;

$totalProjects = 0;
$projects = [];
try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects");
    $totalProjects = (int)($stmt->fetch()['total'] ?? 0);

    $stmt = $pdo->prepare("SELECT id, image_url, title_en, title_es, desc_en, desc_es FROM projects ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $projectsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    $projects = [];
}
$totalPages = max(1, ceil($totalProjects / $projectsPerPage));

includeHeader($t['upload_project']);
?>

<!-- NAVIGATION -->
<nav class="bg-gradient-to-r from-gray-900 to-gray-800 px-4 py-6 flex flex-col md:flex-row items-center justify-between shadow-lg">
    <div class="flex items-center gap-3 mb-4 md:mb-0">
        <span class="text-2xl font-bold text-company-gold">CNA</span>
        <span class="text-white font-bold text-xl">UPHOLSTERY</span>
    </div>
    <div class="flex gap-2 w-full md:w-auto justify-center md:justify-end">
        <a href="create-estimate.php" class="min-w-[160px] text-center bg-gray-300 hover:bg-yellow-600 text-gray-900 font-semibold px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow">
            <i class="fas fa-file-invoice-dollar"></i>
            <?php echo $t['admin_panel']; ?>
        </a>
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

<!-- FORMULARIO DE CARGA -->
<div class="max-w-xl mx-auto bg-white rounded-lg shadow-lg p-8 mt-10 mb-12">
    <h1 class="text-3xl font-bold mb-6 text-center"><?php echo $t['upload_project']; ?></h1>
    <?php if (isset($success)): ?>
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4"><?php echo $success; ?></div>
    <?php elseif (isset($error)): ?>
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-6">
        <div>
            <label class="block font-medium mb-2"><?php echo $t['title_en']; ?></label>
            <input type="text" name="title_en" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block font-medium mb-2"><?php echo $t['title_es']; ?></label>
            <input type="text" name="title_es" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block font-medium mb-2"><?php echo $t['desc_en']; ?></label>
            <textarea name="desc_en" rows="2" required class="w-full border rounded px-3 py-2"></textarea>
        </div>
        <div>
            <label class="block font-medium mb-2"><?php echo $t['desc_es']; ?></label>
            <textarea name="desc_es" rows="2" required class="w-full border rounded px-3 py-2"></textarea>
        </div>
        <div>
            <label class="block font-medium mb-2"><?php echo $t['image_url']; ?></label>
            <input type="url" name="image_url" required class="w-full border rounded px-3 py-2" placeholder="Pega aquí el link de Google Drive, Dropbox, Imgur, Unsplash, etc">
        </div>
        <div id="imgPreview"></div>
        <div class="flex justify-end items-center mt-6">
            <button type="submit" class="bg-company-gold hover:bg-yellow-500 text-gray-900 font-semibold px-6 py-2 rounded transition duration-200">
                <?php echo $t['save']; ?>
            </button>
        </div>
    </form>
</div>

<!-- MODAL EDITAR PROYECTO -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden flex items-center justify-center">
    <form id="editProjectForm" class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg relative">
        <button type="button" onclick="closeEditModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800"><i class="fas fa-times"></i></button>
        <h3 class="text-2xl font-bold mb-4"><?php echo $t['edit_project']; ?></h3>
        <input type="hidden" name="id" id="edit_id">
        <div class="mb-3">
            <label class="block font-medium mb-1"><?php echo $t['title_en']; ?></label>
            <input type="text" name="title_en" id="edit_title_en" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-3">
            <label class="block font-medium mb-1"><?php echo $t['title_es']; ?></label>
            <input type="text" name="title_es" id="edit_title_es" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-3">
            <label class="block font-medium mb-1"><?php echo $t['desc_en']; ?></label>
            <textarea name="desc_en" id="edit_desc_en" class="w-full border rounded px-3 py-2" rows="2" required></textarea>
        </div>
        <div class="mb-3">
            <label class="block font-medium mb-1"><?php echo $t['desc_es']; ?></label>
            <textarea name="desc_es" id="edit_desc_es" class="w-full border rounded px-3 py-2" rows="2" required></textarea>
        </div>
        <div class="mb-3">
            <label class="block font-medium mb-1"><?php echo $t['image_url']; ?></label>
            <input type="url" name="image_url" id="edit_image_url" class="w-full border rounded px-3 py-2" required>
            <div id="editImgPreview"></div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 rounded"><?php echo $t['cancel']; ?></button>
            <button type="submit" class="px-4 py-2 bg-company-gold text-gray-900 rounded font-bold hover:bg-yellow-500"><?php echo $t['update']; ?></button>
        </div>
    </form>
</div>

<!-- GALERÍA DE CARDS CON FONDO Y PAGINACIÓN -->
<div class="py-12 px-2" style="background: linear-gradient(135deg, #f0f2f6 0%, #e0e7ef 100%);">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 text-center"><?php echo $t['project_gallery']; ?></h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($projects as $project): ?>
                <div class="bg-white rounded-2xl overflow-hidden shadow-xl flex flex-col group hover:scale-105 transition duration-300 relative">
                    <div class="h-64 bg-gray-200 flex items-center justify-center overflow-hidden">
                        <?php if (!empty($project['image_url'])): ?>
                            <img src="<?php echo $project['image_url']; ?>"
                                alt="<?php echo $lang === 'es' ? $project['title_es'] : $project['title_en']; ?>"
                                class="object-cover w-full h-full group-hover:scale-110 transition duration-500"
                                onerror="this.style.display='none';">
                        <?php else: ?>
                            <div class="flex flex-col items-center justify-center w-full h-full">
                                <i class="fas fa-image text-4xl text-gray-400"></i>
                                <span class="ml-2 text-gray-500"><?php echo $lang === 'es' ? $project['title_es'] : $project['title_en']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="absolute top-3 right-3 flex space-x-2">
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($project)); ?>)" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs shadow"><?php echo $t['edit']; ?></button>
                        <button onclick="deleteProject(<?php echo $project['id']; ?>)" class="bg-red-600 text-white px-2 py-1 rounded text-xs shadow"><?php echo $t['delete']; ?></button>
                    </div>
                    <div class="p-6 flex-grow flex flex-col justify-between">
                        <h4 class="font-semibold mb-2 text-lg"><?php echo $lang === 'es' ? $project['title_es'] : $project['title_en']; ?></h4>
                        <p class="text-sm text-gray-600"><?php echo $lang === 'es' ? $project['desc_es'] : $project['desc_en']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="flex items-center justify-center space-x-2 mt-10">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="upload-project.php?page=<?php echo $i; ?>"
                        class="px-4 py-2 rounded <?php echo $i === $page ? 'bg-company-gold text-gray-900 font-bold' : 'bg-gray-200 text-gray-700'; ?>">
                        <?php echo $t['page']; ?> <?php echo $i; ?> <?php echo $t['of']; ?> <?php echo $totalPages; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php includeFooter(); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Eliminar proyecto
    function deleteProject(id) {
        Swal.fire({
            title: '<?php echo $t['confirm_delete']; ?>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<?php echo $t['delete']; ?>',
            cancelButtonText: '<?php echo $t['cancel']; ?>'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('upload-project.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=delete&id=' + encodeURIComponent(id)
                }).then(res => res.json()).then(json => {
                    if (json.success) {
                        Swal.fire('<?php echo $t['delete']; ?>', json.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('<?php echo $t['delete']; ?>', json.message, 'error');
                    }
                });
            }
        });
    }

    // Abrir Modal Editar
    function openEditModal(project) {
        document.getElementById('edit_id').value = project.id;
        document.getElementById('edit_title_en').value = project.title_en;
        document.getElementById('edit_title_es').value = project.title_es;
        document.getElementById('edit_desc_en').value = project.desc_en;
        document.getElementById('edit_desc_es').value = project.desc_es;
        document.getElementById('edit_image_url').value = project.image_url;
        showEditPreview(project.image_url);
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Guardar edición
    document.getElementById('editProjectForm').onsubmit = function(e) {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);
        data.append('action', 'edit');
        fetch('upload-project.php', {
            method: 'POST',
            body: data
        }).then(res => res.json()).then(json => {
            closeEditModal();
            if (json.success) {
                Swal.fire('<?php echo $t['edit']; ?>', json.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('<?php echo $t['edit']; ?>', json.message, 'error');
            }
        });
    }

    // Preview imagen carga
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.querySelector('input[name="image_url"]');
        const previewDiv = document.getElementById('imgPreview');
        input.addEventListener('input', function() {
            let url = input.value.trim();

            // JS version of get_img_embed_url
            if (url.match(/drive\.google\.com\/file\/d\/([^\/]+)\//)) {
                url = 'https://drive.google.com/uc?export=view&id=' + url.match(/drive\.google\.com\/file\/d\/([^\/]+)\//)[1];
            }
            if (url.match(/dropbox\.com\/s\/([^\/]+)\/([^?]+)\?dl=0/)) {
                var m = url.match(/dropbox\.com\/s\/([^\/]+)\/([^?]+)\?dl=0/);
                url = 'https://www.dropbox.com/s/' + m[1] + '/' + m[2] + '?raw=1';
            }

            if (url) {
                previewDiv.innerHTML = '<img src="' + url + '" style="max-width:100%;margin-top:10px;" onerror="this.style.display=\'none\'">';
            } else {
                previewDiv.innerHTML = '';
            }
        });

        // Edit preview
        const editInput = document.getElementById('edit_image_url');
        const editPreviewDiv = document.getElementById('editImgPreview');
        if (editInput) {
            editInput.addEventListener('input', function() {
                showEditPreview(editInput.value.trim());
            });
        }
    });

    function showEditPreview(url) {
        // JS version of get_img_embed_url
        if (url.match(/drive\.google\.com\/file\/d\/([^\/]+)\//)) {
            url = 'https://drive.google.com/uc?export=view&id=' + url.match(/drive\.google\.com\/file\/d\/([^\/]+)\//)[1];
        }
        if (url.match(/dropbox\.com\/s\/([^\/]+)\/([^?]+)\?dl=0/)) {
            var m = url.match(/dropbox\.com\/s\/([^\/]+)\/([^?]+)\?dl=0/);
            url = 'https://www.dropbox.com/s/' + m[1] + '/' + m[2] + '?raw=1';
        }
        const editPreviewDiv = document.getElementById('editImgPreview');
        if (url) {
            editPreviewDiv.innerHTML = '<img src="' + url + '" style="max-width:100%;margin-top:10px;" onerror="this.style.display=\'none\'">';
        } else {
            editPreviewDiv.innerHTML = '';
        }
    }
</script>