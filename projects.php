<?php
session_start();
require_once 'config/config.php';

// --- IDIOMA Y TEXTOS ---
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
$_SESSION['lang'] = $lang;

$texts = [
    'en' => [
        'web_title' => 'Project Gallery',
        'gallery_section' => 'Project Gallery',
        'gallery_sub' => 'All our work and transformations',
        'menu_home' => 'Home',
        'menu_gallery' => 'Projects',
        'menu_contact' => 'Contact',
        'menu_login' => 'Login'
    ],
    'es' => [
        'web_title' => 'Galería de Proyectos',
        'gallery_section' => 'Galería de Proyectos',
        'gallery_sub' => 'Todos nuestros trabajos y transformaciones',
        'menu_home' => 'Inicio',
        'menu_gallery' => 'Proyectos',
        'menu_contact' => 'Contacto',
        'menu_login' => 'Ingresar'
    ]
];
$t = $texts[$lang];

// --- CARGA DE PROYECTOS DESDE LA BD ---
$projects = [];
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, image_url, title_en, title_es, desc_en, desc_es FROM projects ORDER BY created_at DESC");
    $stmt->execute();
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    $projects = [];
}

includeHeader($t['web_title']);
?>

<!-- NAVIGATION & LANG (igual que index, sin admin) -->
<nav class="bg-gradient-to-r from-gray-900 to-gray-800 px-4 py-4 flex items-center justify-between shadow-lg relative">
    <div class="flex items-center space-x-4">
        <span class="text-2xl font-bold text-company-gold">CNA</span>
        <span class="text-white font-bold text-xl">UPHOLSTERY</span>
    </div>
    <!-- Desktop Menu -->
    <div class="hidden md:flex items-center space-x-4">
        <a href="?lang=en" class="px-3 py-1 rounded text-sm <?php echo $lang === 'en' ? 'bg-company-gold text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600'; ?>">EN</a>
        <a href="?lang=es" class="px-3 py-1 rounded text-sm <?php echo $lang === 'es' ? 'bg-company-gold text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600'; ?>">ES</a>
        <a href="index.php?lang=<?php echo $lang; ?>" class="text-white hover:text-company-gold px-3 py-1"><?php echo $t['menu_home']; ?></a>
        <a href="projects.php?lang=<?php echo $lang; ?>" class="text-white hover:text-company-gold px-3 py-1"><?php echo $t['menu_gallery']; ?></a>
        <a href="login.php?lang=<?php echo $lang; ?>" class="bg-company-gold hover:bg-yellow-600 text-gray-900 font-semibold px-4 py-2 rounded-lg transition duration-200 ml-2">
            <i class="fas fa-user-shield mr-2"></i><?php echo $t['menu_login']; ?>
        </a>
    </div>
    <!-- Hamburger for mobile -->
    <button class="md:hidden flex items-center text-white" onclick="toggleMenu()" aria-label="Open menu">
        <i class="fas fa-bars text-2xl"></i>
    </button>
    <!-- Mobile Menu -->
    <div id="mobileMenu" class="absolute top-full right-0 w-56 bg-gray-900 rounded-b-lg shadow-lg z-50 hidden flex-col py-2">
        <a href="?lang=en" class="block px-4 py-2 text-sm <?php echo $lang === 'en' ? 'bg-company-gold text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600'; ?>">EN</a>
        <a href="?lang=es" class="block px-4 py-2 text-sm <?php echo $lang === 'es' ? 'bg-company-gold text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600'; ?>">ES</a>
        <a href="index.php?lang=<?php echo $lang; ?>" class="block px-4 py-2 text-white hover:bg-company-gold"><?php echo $t['menu_home']; ?></a>
        <a href="projects.php?lang=<?php echo $lang; ?>" class="block px-4 py-2 text-white hover:bg-company-gold"><?php echo $t['menu_gallery']; ?></a>
        <a href="#contact" class="block px-4 py-2 text-white hover:bg-company-gold"><?php echo $t['menu_contact']; ?></a>
        <a href="login.php?lang=<?php echo $lang; ?>" class="block px-4 py-2 text-white hover:bg-company-gold"><?php echo $t['menu_login']; ?></a>
    </div>
</nav>
<script>
function toggleMenu() {
    var menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
    document.addEventListener('click', function handler(e) {
        if (!e.target.closest('#mobileMenu') && !e.target.closest('button[aria-label="Open menu"]')) {
            menu.classList.add('hidden');
            document.removeEventListener('click', handler);
        }
    });
}
</script>

<!-- HERO -->
<section class="bg-gradient-to-r from-gray-900 to-gray-700 text-white">
    <div class="container mx-auto px-4 py-8 flex flex-col items-center justify-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-2"><?php echo $t['gallery_section']; ?></h1>
        <div class="w-20 h-1 bg-company-gold mb-4"></div>
        <p class="text-xl text-gray-300 text-center"><?php echo $t['gallery_sub']; ?></p>
    </div>
</section>

<!-- PROJECTS GALLERY (solo cliente, hermoso y responsive) -->
<section class="py-8 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($projects as $project): ?>
            <div class="bg-white rounded-2xl overflow-hidden shadow-xl flex flex-col group hover:scale-105 transition duration-300">
                <div class="h-64 bg-gray-200 flex items-center justify-center overflow-hidden">
                    <?php if (!empty($project['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($project['image_url']); ?>" alt="<?php echo $lang === 'es' ? $project['title_es'] : $project['title_en']; ?>" class="object-cover w-full h-full group-hover:scale-110 transition duration-500">
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center w-full h-full">
                            <i class="fas fa-image text-4xl text-gray-400"></i>
                            <span class="ml-2 text-gray-500"><?php echo $lang === 'es' ? $project['title_es'] : $project['title_en']; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-6 flex-grow flex flex-col justify-between">
                    <div>
                        <h4 class="font-semibold mb-2 text-lg"><?php echo $lang === 'es' ? $project['title_es'] : $project['title_en']; ?></h4>
                        <p class="text-sm text-gray-600"><?php echo $lang === 'es' ? $project['desc_es'] : $project['desc_en']; ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php includeFooter(); ?>