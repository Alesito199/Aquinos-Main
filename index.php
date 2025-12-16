<?php

/**
 * Página principal / Landing Page
 * CNA Upholstery System
 * Versión bilingüe y mejorada y responsive + menú hamburguesa
 */

// --------- CONFIGURACIÓN DE IDIOMA ---------
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
$_SESSION['lang'] = $lang;

// --------- TEXTOS MULTIIDIOMA ---------
$texts = [
    'en' => [
        'web_title' => 'CNA Upholstery - Professional Upholstery Services',
        'hero_title' => 'CNA UPHOLSTERY',
        'hero_sub'   => 'Professional upholstery with over 10 years of experience',
        'stats_title' => 'System Statistics',
        'stats_docs' => 'Documents this month',
        'stats_clients' => 'Registered clients',
        'service_section' => 'Our Services',
        'service_sub' => 'Specialists in residential and commercial upholstery',
        'gallery_section' => 'Project Gallery',
        'gallery_sub' => 'Some of our latest work',
        'contact_section' => 'Contact Us',
        'contact_phone' => 'Call us for a free consultation',
        'contact_email' => 'Send us an email',
        'contact_address' => 'Visit our workshop',
        'whatsapp_button' => 'Contact via WhatsApp',
        'form_title'   => 'Contact Form',
        'form_name'    => 'Full Name',
        'form_phone'   => 'Phone',
        'form_email'   => 'Email',
        'form_message' => 'Message',
        'form_placeholder' => 'Describe your upholstery project...',
        'form_submit'  => 'Send Message',
        'session'      => 'Login',
        'footer'       => 'Professional upholstery services with guaranteed quality and customer satisfaction.',
        // Servicios
        'srv1_title' => 'Sofa Upholstery',
        'srv1_desc'  => 'We renew and reupholster sofas, armchairs, and living room furniture with the best materials and professional finishes.',
        'srv2_title' => 'Dining Chairs',
        'srv2_desc'  => 'Specialists in dining chair upholstery, arm chairs and more.',
        'srv3_title' => 'Headboards',
        'srv3_desc'  => 'Creation and reupholstery of headboards in all sizes, from Twin to King Size.',
        'srv4_title' => 'Repairs',
        'srv4_desc'  => 'Structure repair, foam replacement, and full restoration of antique furniture.',
        'srv5_title' => 'Premium Materials',
        'srv5_desc'  => 'We work with top quality fabrics and materials, including 3-inch EZ Dry foam for durability.',
        'srv6_title' => 'Full Service',
        'srv6_desc'  => 'From estimate to delivery, we offer a comprehensive service with quality guarantee.',
        // Galería
        'gal1' => 'King Size Headboard',
        'gal1_title' => 'Headboard Reupholstered',
        'gal1_desc' => 'Completely renovated king size headboard with premium fabric and new padding.',
        'gal2' => 'Dining Chairs Set',
        'gal2_title' => 'Set of 6 Dining Chairs',
        'gal2_desc' => 'Complete set of dining chairs reupholstered with new foam and durable fabric.',
        'gal3' => 'Love Seat',
        'gal3_title' => 'Love Seat Renovated',
        'gal3_desc' => 'Love seat fully restored with 3-inch EZ Dry foam.',
        // Mensaje WhatsApp
        'wa_text' => 'Hello! I am interested in information about your upholstery services.',
        'form_success' => 'Thank you! Your message has been sent via WhatsApp.',
        // Hamburguesa
        'menu_home' => 'Home',
        'menu_gallery' => 'Projects',
        'menu_contact' => 'Contact',
        'menu_login' => 'Login'
    ],
    'es' => [
        'web_title' => 'CNA Upholstery - Servicios Profesionales de Tapicería',
        'hero_title' => 'CNA UPHOLSTERY',
        'hero_sub'   => 'Tapicería profesional con más de 10 años de experiencia',
        'stats_title' => 'Estadísticas del Sistema',
        'stats_docs' => 'Documentos este mes',
        'stats_clients' => 'Clientes registrados',
        'service_section' => 'Nuestros Servicios',
        'service_sub' => 'Especialistas en tapicería residencial y comercial',
        'gallery_section' => 'Galería de Proyectos',
        'gallery_sub' => 'Algunos de nuestros trabajos más recientes',
        'contact_section' => 'Contáctanos',
        'contact_phone' => 'Llámanos para una consulta gratuita',
        'contact_email' => 'Envíanos un email',
        'contact_address' => 'Visítanos en nuestro taller',
        'whatsapp_button' => 'Contactar por WhatsApp',
        'form_title'   => 'Formulario de Contacto',
        'form_name'    => 'Nombre Completo',
        'form_phone'   => 'Teléfono',
        'form_email'   => 'Email',
        'form_message' => 'Mensaje',
        'form_placeholder' => 'Describe tu proyecto de tapicería...',
        'form_submit'  => 'Enviar Mensaje',
        'session'      => 'Iniciar Sesión',
        'footer'       => 'Servicios profesionales de tapicería con calidad garantizada y satisfacción del cliente.',
        'srv1_title' => 'Tapicería de Sofás',
        'srv1_desc'  => 'Renovamos y retapizamos sofás, sillones y muebles de sala con los mejores materiales y acabados profesionales.',
        'srv2_title' => 'Sillas y Dining',
        'srv2_desc'  => 'Especialistas en tapicería de sillas de comedor, dining chairs y sillas con brazos (arm chairs).',
        'srv3_title' => 'Headboards',
        'srv3_desc'  => 'Creación y retapizado de cabeceras de cama (headboards) en todos los tamaños, desde Twin hasta King Size.',
        'srv4_title' => 'Reparaciones',
        'srv4_desc'  => 'Reparación de estructuras, cambio de espuma (foam) y restauración completa de muebles antiguos.',
        'srv5_title' => 'Materiales Premium',
        'srv5_desc'  => 'Trabajamos con las mejores telas y materiales, incluyendo EZ Dry foam de 3 pulgadas para mayor durabilidad.',
        'srv6_title' => 'Servicio Completo',
        'srv6_desc'  => 'Desde el presupuesto hasta la entrega, ofrecemos un servicio integral con garantía de calidad.',
        'gal1' => 'Headboard King Size',
        'gal1_title' => 'Headboard Retapizado',
        'gal1_desc' => 'King Size headboard completamente renovado con tela premium y nuevo acolchado.',
        'gal2' => 'Dining Chairs Set',
        'gal2_title' => 'Set de 6 Sillas de Comedor',
        'gal2_desc' => 'Conjunto completo de sillas retapizadas con foam nuevo y tela resistente.',
        'gal3' => 'Love Seat',
        'gal3_title' => 'Love Seat Renovado',
        'gal3_desc' => 'Love seat completamente restaurado con EZ Dry foam de 3 pulgadas.',
        'wa_text' => 'Hola! Me interesa obtener información sobre sus servicios de tapicería.',
        'form_success' => '¡Gracias! Tu mensaje se ha enviado por WhatsApp.',
        'menu_home' => 'Inicio',
        'menu_gallery' => 'Proyectos',
        'menu_contact' => 'Contacto',
        'menu_login' => 'Ingresar'
    ]
];

$t = $texts[$lang];

// --------- DATOS DEL SISTEMA ---------
require_once 'config/config.php';

try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) as total_docs FROM documentos WHERE fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $recent_docs = $stmt->fetch()['total_docs'] ?? 0;
    $stmt = $pdo->query("SELECT COUNT(*) as total_clients FROM clientes");
    $total_clients = $stmt->fetch()['total_clients'] ?? 0;
} catch (Exception $e) {
    $recent_docs = 0;
    $total_clients = 0;
}

// --------- GALERÍA DE PROYECTOS DESDE LA BASE DE DATOS ---------
$gallery_projects = [];
try {
    $limit = 3;
    $offset = 0;
    $stmt = $pdo->prepare("SELECT id, image_url, title_en, title_es, desc_en, desc_es FROM projects ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $gallery_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $gallery_projects = [];
}

// --------- FUNCIÓN DE IMÁGENES EMBED ---------
function get_img_embed_url($url) {
    if (!$url) return '';
    // Dropbox raw
    if (strpos($url, 'www.dropbox.com') !== false) {
        return str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $url);
    }
    // Google Drive (compartido)
    if (preg_match('/drive\.google\.com\/file\/d\/([^\/]+)\//', $url, $match)) {
        return 'https://drive.google.com/uc?export=view&id=' . $match[1];
    }
    // Otros (Imgur, Unsplash, etc)
    return $url;
}

includeHeader($t['web_title']);
?>

<!-- NAVIGATION & LANG -->
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
        <a href="#contact" class="text-white hover:text-company-gold px-3 py-1"><?php echo $t['menu_contact']; ?></a>
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
        // Close on click outside
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
    <div class="container mx-auto px-4 py-16 flex flex-col md:flex-row items-center">
        <div class="md:w-1/2 mb-8 md:mb-0 flex flex-col items-start">
            <h1 class="text-4xl md:text-6xl font-bold mb-2"><?php echo $t['hero_title']; ?></h1>
            <div class="w-20 h-1 bg-company-gold mb-4"></div>
            <p class="text-xl text-gray-300"><?php echo $t['hero_sub']; ?></p>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4"><?php echo $t['service_section']; ?></h2>
            <p class="text-lg text-gray-600"><?php echo $t['service_sub']; ?></p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-2xl transition duration-300 flex flex-col items-center">
                <div class="text-company-gold text-4xl mb-4"><i class="fas fa-couch"></i></div>
                <h3 class="text-xl font-semibold mb-3 text-center"><?php echo $t['srv1_title']; ?></h3>
                <p class="text-gray-600 text-center"><?php echo $t['srv1_desc']; ?></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-2xl transition duration-300 flex flex-col items-center">
                <div class="text-company-gold text-4xl mb-4"><i class="fas fa-chair"></i></div>
                <h3 class="text-xl font-semibold mb-3 text-center"><?php echo $t['srv2_title']; ?></h3>
                <p class="text-gray-600 text-center"><?php echo $t['srv2_desc']; ?></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-2xl transition duration-300 flex flex-col items-center">
                <div class="text-company-gold text-4xl mb-4"><i class="fas fa-bed"></i></div>
                <h3 class="text-xl font-semibold mb-3 text-center"><?php echo $t['srv3_title']; ?></h3>
                <p class="text-gray-600 text-center"><?php echo $t['srv3_desc']; ?></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-2xl transition duration-300 flex flex-col items-center">
                <div class="text-company-gold text-4xl mb-4"><i class="fas fa-tools"></i></div>
                <h3 class="text-xl font-semibold mb-3 text-center"><?php echo $t['srv4_title']; ?></h3>
                <p class="text-gray-600 text-center"><?php echo $t['srv4_desc']; ?></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-2xl transition duration-300 flex flex-col items-center">
                <div class="text-company-gold text-4xl mb-4"><i class="fas fa-cut"></i></div>
                <h3 class="text-xl font-semibold mb-3 text-center"><?php echo $t['srv5_title']; ?></h3>
                <p class="text-gray-600 text-center"><?php echo $t['srv5_desc']; ?></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-2xl transition duration-300 flex flex-col items-center">
                <div class="text-company-gold text-4xl mb-4"><i class="fas fa-shipping-fast"></i></div>
                <h3 class="text-xl font-semibold mb-3 text-center"><?php echo $t['srv6_title']; ?></h3>
                <p class="text-gray-600 text-center"><?php echo $t['srv6_desc']; ?></p>
            </div>
        </div>
    </div>
</section>

<!-- GALLERY - responsive & botón ver más dentro del último card -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4"><?php echo $t['gallery_section']; ?></h2>
            <p class="text-lg text-gray-600"><?php echo $t['gallery_sub']; ?></p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($gallery_projects as $project): ?>
                <div class="bg-white rounded-2xl overflow-hidden shadow-xl flex flex-col">
                    <div class="h-48 bg-gray-200 flex items-center justify-center">
                        <?php 
                        $img_url = get_img_embed_url($project['image_url']);
                        if ($img_url): ?>
                            <img src="<?php echo htmlspecialchars($img_url); ?>" 
                                 alt="<?php echo $lang === 'es' ? $project['title_es'] : $project['title_en']; ?>" 
                                 class="object-cover w-full h-full" 
                                 style="max-height: 100%; max-width: 100%;" 
                                 onerror="this.style.display='none';">
                        <?php endif; ?>
                    </div>
                    <div class="p-6 flex-grow">
                        <h4 class="font-semibold mb-2">
                            <?php echo $lang === 'es' ? $project['title_es'] : $project['title_en']; ?>
                        </h4>
                        <p class="text-sm text-gray-600">
                            <?php echo $lang === 'es' ? $project['desc_es'] : $project['desc_en']; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="flex justify-end mt-4">
                <a href="projects.php?lang=<?php echo $lang; ?>"
                    class="bg-company-gold text-gray-900 font-semibold px-6 py-2 rounded-lg shadow transition duration-200 text-sm hover:bg-yellow-500">
                    <?php echo $lang === 'es' ? 'Ver más' : 'See more'; ?>
                </a>
            </div>
    </div>
</section>

<!-- CONTACT FOOTER FORM -->
<footer class="bg-gray-900 text-white pt-16 pb-8" id="contact">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Datos de contacto -->
            <div>
                <h2 class="text-3xl font-bold mb-6"><?php echo $t['contact_section']; ?></h2>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <i class="fas fa-phone text-company-gold text-xl mr-4"></i>
                        <div>
                            <div class="font-semibold"><?php echo COMPANY_PHONE; ?></div>
                            <div class="text-gray-300"><?php echo $t['contact_phone']; ?></div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope text-company-gold text-xl mr-4"></i>
                        <div>
                            <div class="font-semibold"><?php echo COMPANY_EMAIL; ?></div>
                            <div class="text-gray-300"><?php echo $t['contact_email']; ?></div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-company-gold text-xl mr-4"></i>
                        <div>
                            <div class="font-semibold"><?php echo COMPANY_ADDRESS; ?></div>
                            <div class="text-gray-300"><?php echo $t['contact_address']; ?></div>
                        </div>
                    </div>
                </div>
                <div class="mt-8">
                    <a href="<?php echo generateWhatsAppLink(COMPANY_PHONE, $t['wa_text']); ?>"
                        target="_blank"
                        class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition duration-200">
                        <i class="fab fa-whatsapp text-xl mr-2"></i>
                        <?php echo $t['whatsapp_button']; ?>
                    </a>
                </div>
            </div>
            <!-- Formulario contacto -->
            <div>
                <h3 class="text-2xl font-bold mb-6 animate-fade-in"><?php echo $t['form_title']; ?></h3>
                <form id="contactForm" class="space-y-6">
                    <div class="relative">
                        <label class="block text-sm font-medium mb-2 pl-2"><?php echo $t['form_name']; ?></label>
                        <div class="flex items-center bg-gray-800 rounded-lg px-4 py-3 border border-gray-700 focus-within:ring-2 focus-within:ring-company-gold">
                            <i class="fas fa-user text-company-gold mr-3"></i>
                            <input type="text" name="name" class="bg-transparent w-full text-white outline-none" required>
                        </div>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium mb-2 pl-2"><?php echo $t['form_phone']; ?></label>
                        <div class="flex items-center bg-gray-800 rounded-lg px-4 py-3 border border-gray-700 focus-within:ring-2 focus-within:ring-company-gold">
                            <i class="fas fa-phone text-company-gold mr-3"></i>
                            <input type="tel" name="phone" class="bg-transparent w-full text-white outline-none" required>
                        </div>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium mb-2 pl-2"><?php echo $t['form_email']; ?></label>
                        <div class="flex items-center bg-gray-800 rounded-lg px-4 py-3 border border-gray-700 focus-within:ring-2 focus-within:ring-company-gold">
                            <i class="fas fa-envelope text-company-gold mr-3"></i>
                            <input type="email" name="email" class="bg-transparent w-full text-white outline-none" required>
                        </div>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium mb-2 pl-2"><?php echo $t['form_message']; ?></label>
                        <div class="flex items-start bg-gray-800 rounded-lg px-4 py-3 border border-gray-700 focus-within:ring-2 focus-within:ring-company-gold">
                            <i class="fas fa-comment-dots text-company-gold mr-3 mt-1"></i>
                            <textarea name="message" rows="4" class="bg-transparent w-full text-white outline-none resize-none"
                                placeholder="<?php echo $t['form_placeholder']; ?>" required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-company-gold hover:bg-yellow-600 text-gray-900 font-semibold py-3 px-6 rounded-lg shadow-lg transition duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo $t['form_submit']; ?>
                    </button>
                </form>
            </div>
        </div>
        <div class="mt-12 text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-company-gold rounded-full flex items-center justify-center">
                    <i class="fas fa-couch text-lg text-gray-900"></i>
                </div>
                <div>
                    <div class="text-xl font-bold text-white">CNA UPHOLSTERY</div>
                </div>
            </div>
            <p class="mb-4"><?php echo $t['footer']; ?></p>
            <div class="text-sm text-gray-400">
                © <?php echo date('Y'); ?> CNA Upholstery. <?php echo $lang === 'es' ? 'Todos los derechos reservados.' : 'All rights reserved.'; ?>
            </div>
        </div>
    </div>
</footer>

<script>
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const name = formData.get('name');
        const phone = formData.get('phone');
        const email = formData.get('email');
        const message = formData.get('message');
        const lang = '<?php echo $lang; ?>';
        const whatsappMessage = lang === 'es' ?
            `Hola! Mi nombre es ${name}.\n\nTeléfono: ${phone}\nEmail: ${email}\n\nMensaje: ${message}` :
            `Hello! My name is ${name}.\n\nPhone: ${phone}\nEmail: ${email}\n\nMessage: ${message}`;
        const whatsappUrl = "<?php echo generateWhatsAppLink(COMPANY_PHONE, ''); ?>" + encodeURIComponent(whatsappMessage);
        window.open(whatsappUrl, '_blank');
        this.reset();
        alert('<?php echo $t['form_success']; ?>');
    });
</script>

<?php includeFooter(); ?>