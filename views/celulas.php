<?php
/**
 * VISTA - CÉLULAS
 * Listado y CRUD básico de células
 */

require_once __DIR__ . '/../config/config.php';
validarSesion();
validarRol(['pastor', 'lider_area', 'lider_celula']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Células - TCAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/celulas.css">
</head>
<body class="bg-gray-100 overflow-x-hidden">
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    <div class="flex">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>
        
        <main class="flex-1 lg:ml-64 p-3 sm:p-5" data-base-url="<?php echo BASE_URL; ?>" data-user-role="<?php echo $_SESSION['rol_nombre'] ?? ($_SESSION['rol'] ?? ''); ?>">
            <div class="w-full max-w-screen-sm sm:max-w-screen-md lg:max-w-screen-lg mx-auto space-y-6">
                <div class="flex items-start sm:items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500">Gestión / Células</p>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Células</h1>
                    </div>
                    <button id="btn-nueva" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 min-h-[44px] text-sm sm:text-base rounded-lg shadow hidden sm:w-auto"
                            onclick="abrirModal()">
                        + Nueva célula
                    </button>
                </div>
            
            <!-- Filtros -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs text-gray-600">Buscar</label>
                    <input id="filtro-buscar" type="text" placeholder="Nombre de célula"
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="text-xs text-gray-600">Área de servicio</label>
                    <select id="filtro-area" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Estado</label>
                    <select id="filtro-estado" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Todos</option>
                        <option value="activa">Activa</option>
                        <option value="pausada">Pausada</option>
                        <option value="inactiva">Inactiva</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button class="bg-gray-200 px-4 py-3 min-h-[44px] rounded-lg text-sm" onclick="limpiarFiltros()">Limpiar</button>
                    <button class="bg-purple-600 text-white px-5 py-3 min-h-[44px] rounded-lg shadow text-sm" onclick="cargarCelulas()">Aplicar</button>
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
                                <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600 hidden sm:table-cell">Área</th>
                                <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600">Día / Hora</th>
                                <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600 hidden sm:table-cell">Zona</th>
                                <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600 hidden sm:table-cell">Estado</th>
                                <th class="px-3 sm:px-4 py-3 text-right font-semibold text-gray-600 hidden sm:table-cell">Asist.</th>
                                <th class="px-3 sm:px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-celulas">
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500 text-sm">Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-between items-center px-4 py-3 border-t bg-gray-50 text-sm" id="paginacion"></div>
            </div>

            <!-- Cards mobile -->
            <div class="sm:hidden w-full space-y-3" id="cards-celulas"></div>
            </div>
        </main>
    </div>
    
    <!-- Toast -->
    <div id="toast" class="hidden fixed bottom-6 right-6 bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg text-sm"></div>

    <!-- Modal -->
    <div id="modal" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-card p-6 relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarModal()">✕</button>
            <h3 id="modal-titulo" class="text-xl font-semibold mb-4 text-gray-800">Nueva célula</h3>
            <div class="modal-divider mb-4"></div>
            <form id="form-celula" class="space-y-4" onsubmit="guardarCelula(event)">
                <input type="hidden" id="celula-id">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Nombre *</label>
                        <input id="nombre" class="w-full border px-3 py-2.5 text-sm" required>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Líder *</label>
                        <select id="lider_id" class="w-full border px-3 py-2.5 text-sm" required></select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Líder de área</label>
                        <select id="lider_area_id" class="w-full border px-3 py-2.5 text-sm"></select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Anfitrión</label>
                        <select id="anfitrion_id" class="w-full border px-3 py-2.5 text-sm"></select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Área de servicio *</label>
                        <select id="area_servicio_id" class="w-full border px-3 py-2.5 text-sm" required></select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Zona</label>
                        <input id="zona" class="w-full border px-3 py-2.5 text-sm" placeholder="Colonia / sector">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Dirección *</label>
                        <input id="direccion" class="w-full border px-3 py-2.5 text-sm" required>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Día de reunión *</label>
                        <select id="dia_semana" class="w-full border px-3 py-2.5 text-sm" required></select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Hora inicio *</label>
                        <input id="hora_inicio" type="time" class="w-full border px-3 py-2.5 text-sm" required>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Estado</label>
                        <select id="estado" class="w-full border px-3 py-2.5 text-sm"></select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Asistentes promedio</label>
                        <input id="cantidad_promedio_asistentes" type="number" min="0" class="w-full border px-3 py-2.5 text-sm" value="0">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" class="px-5 py-3 min-h-[44px] rounded-lg bg-gray-700 text-gray-100 hover:bg-gray-600" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="px-5 py-3 min-h-[44px] rounded-lg bg-gradient-to-r from-purple-500 to-blue-500 text-white shadow-md">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal confirmación -->
    <div id="modal-confirm" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-card rounded-2xl w-full max-w-md p-6">
            <h4 class="text-lg font-bold text-white mb-2">Confirmar acción</h4>
            <p id="confirm-mensaje" class="text-sm text-gray-300 mb-4">¿Seguro que deseas continuar?</p>
            <div class="modal-divider mb-4"></div>
            <div class="flex justify-end space-x-2">
                <button class="px-4 py-3 min-h-[44px] rounded-lg bg-gray-700 text-gray-100 hover:bg-gray-600" onclick="cerrarConfirm()">Cancelar</button>
                <button id="confirm-aceptar" class="px-4 py-3 min-h-[44px] rounded-lg bg-gradient-to-r from-rose-500 to-orange-500 text-white shadow-md">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Modal historial -->
    <div id="modal-historial" class="modal-overlay hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="modal-card rounded-2xl p-6 relative">
            <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" onclick="cerrarHistorial()">✕</button>
            <h4 id="historial-titulo" class="text-xl font-semibold mb-3 text-gray-800">Historial de reuniones</h4>
            <div class="modal-divider mb-4"></div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs sm:text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Fecha</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Asistentes</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Nuevos</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Realizada</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Ofrenda</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Comentarios</th>
                        </tr>
                    </thead>
                    <tbody id="historial-body">
                        <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>assets/js/celulas.js"></script>
</body>
</html>
