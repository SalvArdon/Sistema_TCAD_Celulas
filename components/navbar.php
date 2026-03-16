<?php
/**
 * COMPONENTE - NAVBAR
 * Barra de navegación superior responsiva
 */
require_once __DIR__ . '/icons.php';
?>
<nav class="bg-gradient-to-r from-purple-600 to-pink-600 shadow-lg sticky top-0 z-50">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="bg-white rounded-lg p-2">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.5 1.5H3.75A2.25 2.25 0 001.5 3.75v12.5A2.25 2.25 0 003.75 18.5h12.5a2.25 2.25 0 002.25-2.25V9.5"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">TCAD</h1>
                    <p class="text-xs text-purple-100">Sistema de Células</p>
                </div>
            </div>

            <!-- Menu Desktop -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="<?php echo BASE_URL; ?>dashboard" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium transition">
                    <?php echo tcad_icon('dashboard'); ?>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo BASE_URL; ?>celulas" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium transition">
                    <?php echo tcad_icon('cells'); ?>
                    <span>Células</span>
                </a>
                <a href="<?php echo BASE_URL; ?>reuniones" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium transition">
                    <?php echo tcad_icon('calendar'); ?>
                    <span>Reuniones</span>
                </a>
                <a href="<?php echo BASE_URL; ?>ofrendas" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium transition">
                    <?php echo tcad_icon('offering'); ?>
                    <span>Ofrendas</span>
                </a>
                <a href="<?php echo BASE_URL; ?>servidores" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium transition">
                    <?php echo tcad_icon('servers'); ?>
                    <span>Servidores</span>
                </a>
                <a href="<?php echo BASE_URL; ?>materiales" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium transition">
                    <?php echo tcad_icon('materials'); ?>
                    <span>Materiales</span>
                </a>
            </div>

            <!-- User Menu & Mobile Toggle -->
            <div class="flex items-center space-x-4">
                <!-- Sidebar Toggle (desktop y mobile) -->
                <button id="sidebar-toggle-btn" class="flex lg:hidden items-center justify-center text-white bg-white bg-opacity-10 hover:bg-opacity-20 p-2 rounded-md transition" onclick="toggleSidebar()" aria-label="Alternar menú lateral">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <!-- User Info (Hidden on Mobile) -->
                <div class="hidden sm:block text-white text-right">
                    <p class="font-semibold text-sm"><?php echo $_SESSION['nombre'] ?? 'Usuario'; ?></p>
                    <p class="text-xs opacity-90"><?php echo $_SESSION['rol_nombre'] ?? ''; ?></p>
                </div>

                <!-- Dropdown User Menu -->
                <div class="relative group">
                    <button class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-3 py-2 rounded-full transition text-sm font-medium" aria-label="Menú de usuario">
                        <?php echo tcad_icon('profile'); ?>
                    </button>
                    <div class="hidden group-hover:block absolute right-0 mt-0 w-52 bg-white rounded-lg shadow-lg z-10">
                        <a href="<?php echo BASE_URL; ?>perfil" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-t-lg">
                            <?php echo tcad_icon('profile', 'w-5 h-5 text-gray-500'); ?>
                            <span>Mi Perfil</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>notificaciones" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <?php echo tcad_icon('notifications', 'w-5 h-5 text-gray-500'); ?>
                            <span>Notificaciones</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>configuracion" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <?php echo tcad_icon('settings', 'w-5 h-5 text-gray-500'); ?>
                            <span>Configuración</span>
                        </a>
                        <button onclick="logout()" class="flex items-center gap-2 w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 rounded-b-lg font-medium">
                            <?php echo tcad_icon('logout', 'w-5 h-5 text-red-500'); ?>
                            <span>Salir</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-purple-500">
            <a href="<?php echo BASE_URL; ?>dashboard" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                <?php echo tcad_icon('dashboard'); ?>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo BASE_URL; ?>celulas" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                <?php echo tcad_icon('cells'); ?>
                <span>Células</span>
            </a>
            <a href="<?php echo BASE_URL; ?>reuniones" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                <?php echo tcad_icon('calendar'); ?>
                <span>Reuniones</span>
            </a>
            <a href="<?php echo BASE_URL; ?>ofrendas" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                <?php echo tcad_icon('offering'); ?>
                <span>Ofrendas</span>
            </a>
            <a href="<?php echo BASE_URL; ?>servidores" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                <?php echo tcad_icon('servers'); ?>
                <span>Servidores</span>
            </a>
            <a href="<?php echo BASE_URL; ?>materiales" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                <?php echo tcad_icon('materials'); ?>
                <span>Materiales</span>
            </a>
            <hr class="my-2 border-purple-500">
            <a href="<?php echo BASE_URL; ?>perfil" class="flex items-center gap-2 text-white hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                <?php echo tcad_icon('profile'); ?>
                <span>Mi Perfil</span>
            </a>
            <button onclick="logout()" class="flex items-center gap-2 w-full text-left text-red-300 hover:bg-purple-700 px-3 py-2 rounded-md text-sm font-medium">
                <?php echo tcad_icon('logout', 'w-5 h-5 text-red-300'); ?>
                <span>Salir</span>
            </button>
        </div>
    </div>
</nav>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('menu-icon');
        menu.classList.toggle('hidden');
        
        // Cambiar icono
        if (menu.classList.contains('hidden')) {
            icon.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
        } else {
            icon.setAttribute('d', 'M6 18L18 6M6 6l12 12');
        }
    }

    async function logout() {
        const BASE_URL = '<?php echo BASE_URL; ?>';
        try {
            await fetch(BASE_URL + 'api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'logout' })
            });
            window.location.href = BASE_URL + 'login';
        } catch (error) {
            console.error('Error:', error);
            window.location.href = BASE_URL + 'login';
        }
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (!sidebar) return;
        const isHidden = sidebar.classList.contains('hidden');
        if (isHidden) {
            sidebar.classList.remove('hidden');
            requestAnimationFrame(() => sidebar.classList.remove('-translate-x-full'));
            overlay?.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay?.classList.add('hidden');
            setTimeout(() => sidebar.classList.add('hidden'), 180);
        }
    }
</script>
