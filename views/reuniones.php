<?php
/**
 * VISTA - REUNIONES
 * Listado y acciones básicas (mobile-first con tarjetas)
 */

require_once __DIR__ . '/../config/config.php';
validarSesion();
validarRol(['pastor','lider_area','lider_celula','tesorero']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reuniones - TCAD</title>
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
                        <p class="text-xs sm:text-sm text-gray-500">Gestión / Reuniones</p>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Reuniones</h1>
                    </div>
                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 min-h-[44px] text-sm sm:text-base rounded-lg shadow"
                            onclick="abrirModalReunion()">
                        + Reportar reunión
                    </button>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs text-gray-600">Estado</label>
                        <select id="filtro-estado" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Todos</option>
                            <option value="hoy">Hoy</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="realizada">Realizada</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Buscar célula</label>
                        <input id="filtro-celula-search" type="text" placeholder="Nombre de célula"
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" autocomplete="off">
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
                <div>
                    <label class="text-xs text-gray-600">Área</label>
                    <select id="filtro-area" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button class="bg-gray-200 px-4 py-3 min-h-[44px] rounded-lg text-sm" onclick="limpiarFiltros()">Limpiar</button>
                    <button class="bg-purple-600 text-white px-5 py-3 min-h-[44px] rounded-lg shadow text-sm" onclick="cargarReuniones()">Aplicar</button>
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
                                    <th class="px-3 sm:px-4 py-3 text-center font-semibold text-gray-600">Estado</th>
                                    <th class="px-3 sm:px-4 py-3 text-right font-semibold text-gray-600">Asist.</th>
                                    <th class="px-3 sm:px-4 py-3 text-right font-semibold text-gray-600">Ofrenda</th>
                                    <th class="px-3 sm:px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-reuniones">
                                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500 text-sm">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between items-center px-4 py-3 border-t bg-gray-50 text-sm" id="paginacion-reuniones"></div>
                </div>

                <!-- Cards mobile -->
                <div class="sm:hidden w-full max-w-screen-sm space-y-3 px-4 mx-auto overflow-x-hidden" id="cards-reuniones"></div>

                <!-- Toast -->
                <div id="toast-reuniones" class="hidden fixed bottom-6 right-6 bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg text-sm z-50"></div>
            </div>
        </main>
    </div>

    <!-- Modal Detalle Reunión -->
    <div id="modal-detalle-reunion" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-card p-6 relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarDetalleReunion()">✕</button>
            <h3 class="text-xl font-semibold mb-4 text-gray-800">Detalle de reunión</h3>
            <div class="modal-divider mb-4"></div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-start gap-4">
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500">Célula</p>
                        <p id="det-celula" class="text-base font-semibold text-gray-900 truncate">—</p>
                    </div>
                    <span id="det-estado" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">—</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-500">Fecha</p>
                        <p id="det-fecha" class="text-sm text-gray-800">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Líder</p>
                        <p id="det-lider" class="text-sm text-gray-800">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Asistentes</p>
                        <p id="det-asistentes" class="text-sm text-gray-800">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Nuevos</p>
                        <p id="det-nuevos" class="text-sm text-gray-800">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Ofrenda</p>
                        <p id="det-ofrenda" class="text-sm text-gray-800">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Motivo (si no se realizó)</p>
                        <p id="det-motivo" class="text-sm text-gray-800">—</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Comentarios</p>
                    <p id="det-comentarios" class="text-sm text-gray-800 whitespace-pre-line">—</p>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button class="px-5 py-3 min-h-[44px] rounded-lg bg-gray-700 text-gray-100 hover:bg-gray-600" onclick="cerrarDetalleReunion()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal Reportar Reunión -->
    <div id="modal-reunion" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-card p-6 relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarModalReunion()">✕</button>
            <h3 id="modal-reunion-titulo" class="text-xl font-semibold mb-4 text-gray-800">Reportar reunión</h3>
            <div class="modal-divider mb-4"></div>
            <form id="form-reunion" class="space-y-4" onsubmit="guardarReunion(event)">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="text-sm text-gray-600">Buscar célula *</label>
                        <input id="celula_search" type="text" placeholder="Nombre de célula"
                               class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" autocomplete="off" required>
                        <input type="hidden" id="celula_id">
                        <div id="celula_suggestions" class="hidden bg-white border rounded-lg mt-1 shadow max-h-48 overflow-y-auto text-sm"></div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Fecha *</label>
                        <input type="date" id="fecha_reunion" class="w-full border px-3 py-2.5 text-sm" required>
                    </div>
                    <div class="flex items-center space-x-2 sm:col-span-2">
                        <input type="checkbox" id="realizada" class="w-5 h-5 text-purple-600 rounded" checked>
                        <label for="realizada" class="text-sm text-gray-700">La reunión se realizó</label>
                    </div>
                    <div id="div-motivo" class="hidden sm:col-span-2">
                        <label class="text-sm text-gray-600">Motivo (si no se realizó)</label>
                        <textarea id="motivo_cancelacion" rows="2" class="w-full border px-3 py-2.5 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Asistentes *</label>
                        <input type="number" id="cantidad_asistentes" min="0" class="w-full border px-3 py-2.5 text-sm" required value="0">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Nuevos</label>
                        <input type="number" id="cantidad_nuevos" min="0" class="w-full border px-3 py-2.5 text-sm" value="0">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Ofrenda ($)</label>
                        <input type="number" id="monto_ofrenda" min="0" step="0.01" class="w-full border px-3 py-2.5 text-sm" value="0">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm text-gray-600">Temas / Actividades</label>
                        <textarea id="temas_tratados" rows="2" class="w-full border px-3 py-2.5 text-sm"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm text-gray-600">Comentarios</label>
                        <textarea id="comentarios" rows="2" class="w-full border px-3 py-2.5 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" class="px-5 py-3 min-h-[44px] rounded-lg bg-gray-700 text-gray-100 hover:bg-gray-600" onclick="cerrarModalReunion()">Cancelar</button>
                    <button type="submit" class="px-5 py-3 min-h-[44px] rounded-lg bg-gradient-to-r from-purple-500 to-blue-500 text-white shadow-md">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <?php $verJs = @filemtime(__DIR__ . '/../assets/js/reuniones.js'); ?>
    <script src="<?php echo BASE_URL; ?>assets/js/reuniones.js?v=<?php echo $verJs ?: time(); ?>"></script>
</body>
</html>
