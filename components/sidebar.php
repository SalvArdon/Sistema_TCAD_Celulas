<?php
/**
 * COMPONENTE - SIDEBAR
 * Panel lateral para navegación en escritorio
 */
require_once __DIR__ . '/icons.php';
?>
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>
<aside id="sidebar" class="hidden lg:block w-64 bg-gray-900 text-white h-screen fixed left-0 top-16 overflow-y-auto transform -translate-x-full transition-transform duration-200 ease-out lg:translate-x-0 lg:transform-none z-50">
    <div class="p-6">
        <!-- Sección: Dashboard -->
        <div class="mb-8">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Principal</h3>
            <ul class="space-y-2">
                <li>
                    <a href="<?php echo BASE_URL; ?>dashboard" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('dashboard', 'w-5 h-5'); ?>
                        </span>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Sección: Gestión -->
        <div class="mb-8">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Gestión</h3>
            <ul class="space-y-2">
                <li>
                    <a href="<?php echo BASE_URL; ?>celulas" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('cells', 'w-5 h-5'); ?>
                        </span>
                        <div class="flex-1">
                            <span class="block">Células</span>
                            <span class="text-xs text-gray-400">Listar, crear, editar</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>reuniones" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('calendar', 'w-5 h-5'); ?>
                        </span>
                        <div class="flex-1">
                            <span class="block">Reuniones</span>
                            <span class="text-xs text-gray-400">Registro y asistencia</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>ofrendas" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('offering', 'w-5 h-5'); ?>
                        </span>
                        <div class="flex-1">
                            <span class="block">Ofrendas</span>
                            <span class="text-xs text-gray-400">Control financiero</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>servidores" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('servers', 'w-5 h-5'); ?>
                        </span>
                        <div class="flex-1">
                            <span class="block">Servidores</span>
                            <span class="text-xs text-gray-400">Miembros del ministerio</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Sección: Administración -->
        <div class="mb-8">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Administración</h3>
            <ul class="space-y-2">
                <li>
                    <a href="<?php echo BASE_URL; ?>liderazgo" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('leadership', 'w-5 h-5'); ?>
                        </span>
                        <div class="flex-1">
                            <span class="block">Liderazgo</span>
                            <span class="text-xs text-gray-400">Delegaciones y jerarquía</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>materiales" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('materials', 'w-5 h-5'); ?>
                        </span>
                        <div class="flex-1">
                            <span class="block">Materiales</span>
                            <span class="text-xs text-gray-400">Estudio y recursos</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>notificaciones" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('notifications', 'w-5 h-5'); ?>
                        </span>
                        <div class="flex-1">
                            <span class="block">Notificaciones</span>
                            <span class="text-xs text-gray-400">Alertas y mensajes</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Sección: Sistema -->
        <div class="mb-8">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Sistema</h3>
            <ul class="space-y-2">
                <li>
                    <a href="<?php echo BASE_URL; ?>auditoria" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('audit', 'w-5 h-5'); ?>
                        </span>
                        <div class="flex-1">
                            <span class="block">Auditoría</span>
                            <span class="text-xs text-gray-400">Historial de cambios</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>reportes" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('reports', 'w-5 h-5'); ?>
                        </span>
                        <div class="flex-1">
                            <span class="block">Reportes</span>
                            <span class="text-xs text-gray-400">Exportar datos</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Separador -->
        <hr class="border-gray-700 my-4">

        <!-- Sección: Configuración -->
        <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Mi Cuenta</h3>
            <ul class="space-y-2">
                <li>
                    <a href="<?php echo BASE_URL; ?>perfil" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('profile', 'w-5 h-5'); ?>
                        </span>
                        <span>Mi Perfil</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>configuracion" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-800 transition group">
                        <span class="text-white group-hover:text-purple-300">
                            <?php echo tcad_icon('settings', 'w-5 h-5'); ?>
                        </span>
                        <span>Configuración</span>
                    </a>
                </li>
                <li>
                    <button onclick="logout()" class="w-full flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-red-900 transition text-red-300 group">
                        <span class="text-red-300 group-hover:text-red-100">
                            <?php echo tcad_icon('logout', 'w-5 h-5'); ?>
                        </span>
                        <span>Salir</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</aside>

<script>
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
</script>
