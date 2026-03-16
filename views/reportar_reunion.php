<?php
/**
 * VISTA - REPORTAR REUNIÓN (Mobile Optimized)
 * Formulario para que líderes de célula reporten reuniones
 */

require_once __DIR__ . '/../config/config.php';
validarSesion();

// Validar que sea líder de célula
validarRol(['lider_celula', 'lider_area', 'pastor']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Reunión - TCAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">
            <h1 class="text-xl font-bold text-purple-600">Reportar Reunión</h1>
            <button onclick="history.back()" class="text-gray-600 hover:text-gray-900">
                ← Atrás
            </button>
        </div>
    </nav>

    <div class="max-w-lg mx-auto p-4 pb-20">
        <!-- Formulario -->
        <form id="form-reporte" class="bg-white rounded-lg shadow-lg p-6 space-y-4">
            
            <!-- Célula (Selector) -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Célula *
                </label>
                <select id="celula_id" name="celula_id" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-base">
                    <option value="">-- Selecciona tu célula --</option>
                </select>
            </div>

            <!-- Fecha de Reunión -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Fecha de Reunión *
                </label>
                <input type="date" id="fecha_reunion" name="fecha_reunion" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-base">
            </div>

            <!-- Checkbox: ¿Se realizó la reunión? -->
            <div class="flex items-center space-x-2">
                <input type="checkbox" id="realizada" name="realizada" checked
                    class="w-5 h-5 text-purple-600 rounded">
                <label for="realizada" class="text-sm font-medium text-gray-700">
                    La reunión se realizó
                </label>
            </div>

            <!-- Si no se realizó: Motivo -->
            <div id="div-motivo" class="hidden">
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    ¿Por qué no se realizó?
                </label>
                <textarea id="motivo_cancelacion" name="motivo_cancelacion" rows="2"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-base"
                    placeholder="Explica brevemente..."></textarea>
            </div>

            <!-- Cantidad de Asistentes -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Cantidad de Asistentes *
                </label>
                <input type="number" id="cantidad_asistentes" name="cantidad_asistentes" min="0" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-base"
                    placeholder="0">
            </div>

            <!-- Nuevos Visitantes -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Nuevos Visitantes
                </label>
                <input type="number" id="cantidad_nuevos" name="cantidad_nuevos" min="0" value="0"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-base"
                    placeholder="0">
            </div>

            <!-- Monto de Ofrenda -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Monto de Ofrenda ($)
                </label>
                <input type="number" id="monto_ofrenda" name="monto_ofrenda" min="0" step="0.01" value="0"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-base"
                    placeholder="0.00">
                <small class="text-gray-500">Si se recolectó ofrenda, ingresa el monto</small>
            </div>

            <!-- Temas Tratados -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Temas / Actividades
                </label>
                <textarea id="temas_tratados" name="temas_tratados" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-base"
                    placeholder="¿Qué temas o actividades hicieron?"></textarea>
            </div>

            <!-- Comentarios Adicionales -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    Comentarios / Notas
                </label>
                <textarea id="comentarios" name="comentarios" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-base"
                    placeholder="Cualquier otra observación importante..."></textarea>
            </div>

            <!-- Mensaje de Error -->
            <div id="error-msg" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <span id="error-text"></span>
            </div>

            <!-- Mensaje de Éxito -->
            <div id="success-msg" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                ✓ Reporte enviado exitosamente
            </div>

            <!-- Botón Enviar -->
            <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 rounded-lg hover:shadow-lg transition text-lg">
                📤 Enviar Reporte
            </button>

            <!-- Nota -->
            <p class="text-xs text-gray-500 text-center">
                Asegúrate de llenar todos los campos marcados con *
            </p>
        </form>
    </div>

    <script>
        // Cargar mis células
        async function cargarMisCelulas() {
            try {
                const response = await fetch('<?php echo BASE_URL; ?>api/reuniones.php?accion=listar&lider_id=<?php echo $_SESSION['usuario_id']; ?>');
                const data = await response.json();
                
                if (data.exito) {
                    const select = document.getElementById('celula_id');
                    select.innerHTML = '<option value="">-- Selecciona tu célula --</option>';
                    
                    data.data.forEach(celula => {
                        const option = document.createElement('option');
                        option.value = celula.id;
                        option.textContent = celula.nombre;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error cargando células:', error);
            }
        }

        // Control de checkbox
        document.getElementById('realizada').addEventListener('change', (e) => {
            const divMotivo = document.getElementById('div-motivo');
            if (!e.target.checked) {
                divMotivo.classList.remove('hidden');
                document.getElementById('motivo_cancelacion').required = true;
            } else {
                divMotivo.classList.add('hidden');
                document.getElementById('motivo_cancelacion').required = false;
            }
        });

        // Fecha por defecto a hoy
        document.getElementById('fecha_reunion').valueAsDate = new Date();

        // Enviar reporte
        document.getElementById('form-reporte').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const errorMsg = document.getElementById('error-msg');
            const successMsg = document.getElementById('success-msg');
            errorMsg.classList.add('hidden');
            successMsg.classList.add('hidden');
            
            const datos = {
                celula_id: document.getElementById('celula_id').value,
                fecha_reunion: document.getElementById('fecha_reunion').value,
                realizada: document.getElementById('realizada').checked,
                motivo_cancelacion: document.getElementById('motivo_cancelacion').value,
                cantidad_asistentes: document.getElementById('cantidad_asistentes').value,
                cantidad_nuevos: document.getElementById('cantidad_nuevos').value,
                monto_ofrenda: document.getElementById('monto_ofrenda').value,
                temas_tratados: document.getElementById('temas_tratados').value,
                comentarios: document.getElementById('comentarios').value
            };
            
            try {
                const response = await fetch('<?php echo BASE_URL; ?>api/reuniones.php?accion=registrar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });
                
                const data = await response.json();
                
                if (data.exito) {
                    successMsg.classList.remove('hidden');
                    document.getElementById('form-reporte').reset();
                    
                    // Redirigir en 2 segundos
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_URL; ?>dashboard.php';
                    }, 2000);
                } else {
                    errorMsg.classList.remove('hidden');
                    document.getElementById('error-text').textContent = data.mensaje;
                }
            } catch (error) {
                errorMsg.classList.remove('hidden');
                document.getElementById('error-text').textContent = 'Error en la conexión';
            }
        });

        // Inicializar
        cargarMisCelulas();
    </script>
</body>
</html>
