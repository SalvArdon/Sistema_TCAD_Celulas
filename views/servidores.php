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
    <title>Servidores - TCAD</title>
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
                        <p class="text-xs sm:text-sm text-gray-500">Gestión / Servidores</p>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Servidores</h1>
                    </div>
                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 min-h-[44px] text-sm sm:text-base rounded-lg shadow"
                            onclick="abrirModalServidor()">
                        + Nuevo servidor
                    </button>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs text-gray-600">Buscar</label>
                        <input id="filtro-q" type="text" placeholder="Nombre, email o código"
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Área</label>
                        <select id="filtro-area" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Todas</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="bg-gray-200 px-4 py-3 min-h-[44px] rounded-lg text-sm" onclick="limpiarFiltrosServ()">Limpiar</button>
                        <button class="bg-purple-600 text-white px-5 py-3 min-h-[44px] rounded-lg shadow text-sm" onclick="cargarServidores()">Aplicar</button>
                    </div>
                </div>

                <!-- Tabla desktop -->
                <div class="hidden sm:block bg-white rounded-xl shadow overflow-hidden max-w-screen-lg mx-auto">
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full table-auto text-xs sm:text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Nombre</th>
                                    <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Email</th>
                                    <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Teléfono</th>
                                    <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Área</th>
                                    <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Ingreso</th>
                                    <th class="px-3 sm:px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-servidores">
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between items-center px-4 py-3 border-t bg-gray-50 text-sm" id="paginacion-servidores"></div>
                </div>

                <!-- Cards mobile -->
                <div class="sm:hidden w-full max-w-screen-sm space-y-3 px-3 mx-auto overflow-x-hidden" id="cards-servidores"></div>
            </div>
        </main>
    </div>

    <!-- Toast -->
    <div id="toast-servidores" class="hidden fixed bottom-6 right-6 bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg text-sm z-50"></div>

    <!-- Modal Servidor -->
    <div id="modal-servidor" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-card p-6 relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarModalServidor()">✕</button>
            <h3 id="modal-servidor-titulo" class="text-xl font-semibold mb-4 text-gray-800">Nuevo servidor</h3>
            <div class="modal-divider mb-4"></div>
                        <form id="form-servidor" class="space-y-4" onsubmit="guardarServidor(event)">
                <input type="hidden" id="srv-id">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-600">Nombre completo *</label>
                        <input id="srv-nombre" class="w-full border px-3 py-2.5 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Email *</label>
                        <input id="srv-email" type="email" class="w-full border px-3 py-2.5 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Teléfono</label>
                        <input id="srv-telefono" class="w-full border px-3 py-2.5 text-sm" placeholder="+503...">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">DUI</label>
                        <input id="srv-dui" class="w-full border px-3 py-2.5 text-sm" placeholder="00000000-0">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Género</label>
                        <select id="srv-genero" class="w-full border px-3 py-2.5 text-sm">
                            <option value="">No especifica</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Fecha de nacimiento</label>
                        <input id="srv-nacimiento" type="date" class="w-full border px-3 py-2.5 text-sm">
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input id="srv-bautizado" type="checkbox" class="h-4 w-4 text-purple-600 border-gray-300 rounded">
                        <label for="srv-bautizado" class="text-xs text-gray-600">Bautizado</label>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Fecha de bautizo</label>
                        <input id="srv-bautizo-fecha" type="date" class="w-full border px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Áreas de servicio *</label>
                        <select id="srv-area" multiple aria-hidden="true" style="display:none;" class="w-full border px-3 py-2.5 text-sm min-h-[96px]"></select>
                        <p class="text-[11px] text-gray-500 mt-1">Busca y agrega múltiples áreas desde el buscador</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Dirección</label>
                        <input id="srv-direccion" class="w-full border px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Fecha ingreso</label>
                        <input id="srv-fecha" type="date" class="w-full border px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Código membresía</label>
                        <input id="srv-codigo" class="w-full border px-3 py-2.5 text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="px-5 py-3 min-h-[44px] rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300" onclick="cerrarModalServidor()">Cancelar</button>
                    <button type="submit" class="px-5 py-3 min-h-[44px] rounded-lg bg-gradient-to-r from-purple-500 to-blue-500 text-white shadow-md">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <?php $verJs = @filemtime(__DIR__ . '/../assets/js/servidores.js'); ?>
    <script src="<?php echo BASE_URL; ?>assets/js/servidores.js?v=<?php echo $verJs ?: time(); ?>"></script>
</body>
</html>





    <!-- Modal Detalle Servidor -->
    <div id="modal-det-servidor" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-card p-6 relative max-w-xl w-full">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarDetalleServidor()">&times;</button>
            <h3 class="text-xl font-semibold mb-2 text-gray-800">Detalle del servidor</h3>
            <div class="modal-divider mb-4"></div>
            <div class="space-y-3 text-sm text-gray-700">
                <div>
                    <p class="text-xs text-gray-500">Nombre</p>
                    <p class="font-semibold text-gray-900" id="det-nombre">—</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium" id="det-email">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Teléfono</p>
                        <p class="font-medium" id="det-telefono">—</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Áreas / ministerios</p>
                    <p class="font-medium" id="det-areas">—</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Código membresía</p>
                        <p class="font-medium" id="det-codigo">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Fecha ingreso</p>
                        <p class="font-medium" id="det-ingreso">—</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">DUI</p>
                        <p class="font-medium" id="det-dui">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Género</p>
                        <p class="font-medium" id="det-genero">—</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Bautizado</p>
                        <p class="font-medium" id="det-bautizado">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Fecha bautizo</p>
                        <p class="font-medium" id="det-bautizo-fecha">—</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Fecha de nacimiento</p>
                    <p class="font-medium" id="det-nacimiento">—</p>
                </div>
            </div>
            <div class="mt-5 flex justify-end">
                <button class="px-5 py-2 rounded-lg bg-purple-600 text-white" onclick="cerrarDetalleServidor()">Cerrar</button>
            </div>
        </div>
    </div>



