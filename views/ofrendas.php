<?php
/**
 * VISTA - OFRENDAS
 * Listado y gestión de estados (mobile-first)
 */

require_once __DIR__ . '/../config/config.php';
validarSesion();
validarRol(['pastor','tesorero','lider_area','lider_celula']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofrendas - TCAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/celulas.css">
</head>
<body class="bg-gray-100 overflow-x-hidden">
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    <div class="flex">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="flex-1 lg:ml-64 p-3 sm:p-5 min-w-0 overflow-x-hidden" data-base-url="<?php echo BASE_URL; ?>" data-user-role="<?php echo $_SESSION['rol_nombre'] ?? ($_SESSION['rol'] ?? ''); ?>">
            <div class="w-full lg:max-w-screen-lg mx-auto space-y-6 min-w-0">
                <div class="flex items-start sm:items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500">Gestión / Ofrendas</p>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Ofrendas</h1>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs text-gray-600">Estado</label>
                        <select id="filtro-estado" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Todos</option>
                            <option value="reportada">Reportada</option>
                            <option value="recibida">Recibida</option>
                            <option value="conciliada">Conciliada</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Buscar célula</label>
                        <input id="filtro-celula-search" type="text" placeholder="Nombre de célula" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" autocomplete="off">
                        <input type="hidden" id="filtro-celula-id">
                        <div id="filtro-celula-suggestions" class="hidden bg-white border rounded-lg mt-1 shadow max-h-48 overflow-y-auto text-sm"></div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Desde</label>
                        <input type="date" id="filtro-inicio" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Hasta</label>
                        <input type="date" id="filtro-fin" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="bg-gray-200 px-4 py-3 min-h-[44px] rounded-lg text-sm" onclick="limpiarFiltrosOfrendas()">Limpiar</button>
                        <button class="bg-purple-600 text-white px-5 py-3 min-h-[44px] rounded-lg shadow text-sm" onclick="cargarOfrendas()">Aplicar</button>
                    </div>
                </div>

                <!-- Tabla desktop -->
                <div class="hidden sm:block bg-white rounded-xl shadow overflow-hidden max-w-screen-lg mx-auto">
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full table-auto text-xs sm:text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Fecha</th>
                                    <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Célula</th>
                                    <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Líder</th>
                                    <th class="px-3 sm:px-4 py-3 text-right font-semibold text-gray-600">Monto</th>
                                    <th class="px-3 sm:px-4 py-3 text-center font-semibold text-gray-600">Estado</th>
                                    <th class="px-3 sm:px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-ofrendas">
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between items-center px-4 py-3 border-t bg-gray-50 text-sm" id="paginacion-ofrendas"></div>
                </div>

                <!-- Cards mobile -->
                <div class="sm:hidden w-full max-w-screen-sm space-y-3 px-4 mx-auto overflow-x-hidden" id="cards-ofrendas"></div>
            </div>
        </main>
    </div>

    <!-- Toast -->
    <div id="toast-ofrendas" class="hidden fixed bottom-6 right-6 bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg text-sm z-50"></div>

    <!-- Modal Registrar Ofrenda -->
    <div id="modal-registrar-ofrenda" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-card p-6 relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarModalRegistrarOfrenda()">✕</button>
            <h3 class="text-xl font-semibold mb-4 text-gray-800">Registrar ofrenda</h3>
            <div class="modal-divider mb-4"></div>
            <form class="space-y-4" onsubmit="guardarOfrenda(event)">
                <div>
                    <label class="text-xs text-gray-600">Célula</label>
                    <input id="ofr-celula-search" type="text" placeholder="Nombre de célula" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" autocomplete="off">
                    <input type="hidden" id="ofr-celula-id">
                    <div id="ofr-celula-suggestions" class="hidden bg-white border rounded-lg mt-1 shadow max-h-48 overflow-y-auto text-sm"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-600">Fecha de reporte</label>
                        <input type="date" id="ofr-fecha" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Reunión</label>
                        <input id="ofr-reunion-search" type="text" placeholder="Buscar por fecha o célula" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" autocomplete="off">
                        <input type="hidden" id="ofr-reunion-id" required>
                        <div id="ofr-reunion-suggestions" class="hidden bg-white border rounded-lg mt-1 shadow max-h-48 overflow-y-auto text-sm"></div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Monto</label>
                        <input type="number" min="0" step="0.01" id="ofr-monto" class="w-full border rounded-lg px-3 py-2" required>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Notas</label>
                    <textarea id="ofr-notas" class="w-full border rounded-lg px-3 py-2" rows="2" placeholder="Opcional"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" class="px-5 py-3 min-h-[44px] rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300" onclick="cerrarModalRegistrarOfrenda()">Cancelar</button>
                    <button type="submit" class="px-5 py-3 min-h-[44px] rounded-lg bg-gradient-to-r from-purple-500 to-blue-500 text-white shadow-md">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detalle Ofrenda -->
    <div id="modal-detalle-ofrenda" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-card p-6 relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarDetalleOfrenda()">✕</button>
            <h3 class="text-xl font-semibold mb-4 text-gray-800">Detalle de ofrenda</h3>
            <div class="modal-divider mb-4"></div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-start gap-4">
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500">Célula</p>
                        <p id="det-ofr-celula" class="text-base font-semibold text-gray-900 truncate">—</p>
                        <p class="text-xs text-gray-500">Líder: <span id="det-ofr-lider" class="text-gray-700">—</span></p>
                    </div>
                    <span id="det-ofr-estado" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">—</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-500">Fecha reporte</p>
                        <p id="det-ofr-fecha" class="text-sm text-gray-800">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Monto</p>
                        <p id="det-ofr-monto" class="text-sm text-gray-800 font-semibold">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Reunión</p>
                        <p id="det-ofr-reunion" class="text-sm text-gray-800">—</p>
                    </div>
                    <div id="det-ofr-notas-wrap">
                        <p class="text-xs text-gray-500">Notas / discrepancia</p>
                        <p id="det-ofr-notas" class="text-sm text-gray-800 whitespace-pre-line">—</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button class="px-5 py-3 min-h-[44px] rounded-lg bg-gray-700 text-gray-100 hover:bg-gray-600" onclick="cerrarDetalleOfrenda()">Cerrar</button>
            </div>
        </div>
    </div>

    <?php $verJs = @filemtime(__DIR__ . '/../assets/js/ofrendas.js'); ?>
    <script src="<?php echo BASE_URL; ?>assets/js/ofrendas.js?v=<?php echo $verJs ?: time(); ?>"></script>
</body>
</html>
