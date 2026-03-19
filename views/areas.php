<?php
require_once __DIR__ . '/../config/config.php';
validarSesion();
validarRol(['pastor','lider_area']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Áreas / Ministerios - TCAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/celulas.css">
</head>
<body class="bg-gray-100 overflow-x-hidden">
<?php include __DIR__ . '/../components/navbar.php'; ?>
<div class="flex">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <main class="flex-1 lg:ml-64 p-3 sm:p-5 min-w-0 overflow-x-hidden" data-base-url="<?php echo BASE_URL; ?>">
        <div class="w-full lg:max-w-screen-lg mx-auto space-y-6 min-w-0">
            <div class="flex items-start sm:items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-500">Gestión / Áreas</p>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Áreas / Ministerios</h1>
                </div>
                <button class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 min-h-[44px] text-sm sm:text-base rounded-lg shadow"
                        onclick="abrirModalArea()">
                    + Nueva área
                </button>
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs text-gray-600">Buscar</label>
                    <input id="filtro-q" type="text" placeholder="Nombre o descripción"
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="text-xs text-gray-600">Estado</label>
                    <select id="filtro-estado" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Todos</option>
                        <option value="activa">Activa</option>
                        <option value="inactiva">Inactiva</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button class="bg-gray-200 px-4 py-3 min-h-[44px] rounded-lg text-sm" onclick="limpiarFiltrosAreas()">Limpiar</button>
                    <button class="bg-purple-600 text-white px-5 py-3 min-h-[44px] rounded-lg shadow text-sm" onclick="cargarAreas()">Aplicar</button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="hidden sm:block bg-white rounded-xl shadow overflow-hidden max-w-screen-lg mx-auto">
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full table-auto text-xs sm:text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Nombre</th>
                            <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Líder</th>
                            <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Estado</th>
                            <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Creación</th>
                            <th class="px-3 sm:px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="tabla-areas">
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500 text-sm">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-between items-center px-4 py-3 border-t bg-gray-50 text-sm" id="paginacion-areas"></div>
            </div>

            <!-- Cards mobile -->
            <div class="sm:hidden w-full max-w-screen-sm space-y-3 px-3 mx-auto overflow-x-hidden" id="cards-areas"></div>
        </div>
    </main>
</div>

<!-- Toast -->
<div id="toast-areas" class="hidden fixed bottom-6 right-6 bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg text-sm z-50"></div>

<!-- Modal Área -->
<div id="modal-area" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
    <div class="modal-card p-6 relative">
        <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarModalArea()">&times;</button>
        <h3 id="modal-area-titulo" class="text-xl font-semibold mb-4 text-gray-800">Nueva área</h3>
        <div class="modal-divider mb-4"></div>
        <form id="form-area" class="space-y-4" onsubmit="guardarArea(event)">
            <input type="hidden" id="area-id">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-600">Nombre *</label>
                    <input id="area-nombre" class="w-full border px-3 py-2.5 text-sm" required>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Líder (opcional)</label>
                    <input type="hidden" id="area-lider-id">
                    <input id="area-lider-search" class="w-full border px-3 py-2.5 text-sm" placeholder="Buscar líder por nombre/correo" autocomplete="off">
                    <div id="area-lider-suggestions" class="hidden mt-1 border rounded-lg bg-white shadow max-h-44 overflow-y-auto text-sm"></div>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs text-gray-600">Descripción</label>
                    <textarea id="area-desc" class="w-full border px-3 py-2.5 text-sm" rows="3"></textarea>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Estado</label>
                    <select id="area-estado" class="w-full border px-3 py-2.5 text-sm">
                        <option value="1">Activa</option>
                        <option value="0">Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" class="px-5 py-3 min-h-[44px] rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300" onclick="cerrarModalArea()">Cancelar</button>
                <button type="submit" class="px-5 py-3 min-h-[44px] rounded-lg bg-gradient-to-r from-purple-500 to-blue-500 text-white shadow-md">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detalle Área -->
<div id="modal-det-area" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
    <div class="modal-card p-6 relative max-w-xl w-full">
        <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarDetalleArea()">&times;</button>
        <h3 class="text-xl font-semibold mb-2 text-gray-800">Detalle del área</h3>
        <div class="modal-divider mb-4"></div>
        <div class="space-y-3 text-sm text-gray-700">
            <div>
                <p class="text-xs text-gray-500">Nombre</p>
                <p class="font-semibold text-gray-900" id="det-area-nombre">—</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Líder</p>
                    <p class="font-medium" id="det-area-lider">—</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Estado</p>
                    <p class="font-medium" id="det-area-estado">—</p>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-500">Descripción</p>
                <p class="font-medium" id="det-area-desc">—</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Creación</p>
                    <p class="font-medium" id="det-area-creacion">—</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Última modificación</p>
                    <p class="font-medium" id="det-area-mod">—</p>
                </div>
            </div>
        </div>
        <div class="mt-5 flex justify-end">
            <button class="px-5 py-2 rounded-lg bg-purple-600 text-white" onclick="cerrarDetalleArea()">Cerrar</button>
        </div>
    </div>
 </div>

<?php $verJs = @filemtime(__DIR__ . '/../assets/js/areas.js'); ?>
<script src="<?php echo BASE_URL; ?>assets/js/areas.js?v=<?php echo $verJs ?: time(); ?>"></script>
</body>
</html>
