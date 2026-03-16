<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema TCAD Células</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- NAVBAR -->
    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="lg:ml-64 pt-16">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h2 class="text-4xl font-bold text-gray-900">Dashboard Principal</h2>
                <p class="text-gray-600 mt-2">Control y seguimiento de células, servidores y ofrendas</p>
                <p id="fecha-actual" class="text-sm text-gray-500 mt-1"></p>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <!-- Células Activas -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-l-4 border-purple-600">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 text-sm font-semibold">Células Activas</h3>
                        <div class="bg-purple-100 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.5 1.5H3.75A2.25 2.25 0 001.5 3.75v12.5A2.25 2.25 0 003.75 18.5h12.5a2.25 2.25 0 002.25-2.25V9.5"/>
                                <path d="M6.5 10.5h7M6.5 6.5h7m-7 8h7"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-4xl font-bold text-purple-600" id="stat-celulas">0</p>
                    <p class="text-xs text-gray-400 mt-2">Activas en el sistema</p>
                </div>

                <!-- Asistentes Mes -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-l-4 border-blue-600">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 text-sm font-semibold">Asistentes</h3>
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 10a3 3 0 100-6 3 3 0 000 6zM0 15s0-4 10-4 10 4 10 4v5H0v-5z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-4xl font-bold text-blue-600" id="stat-asistentes">0</p>
                    <p class="text-xs text-gray-400 mt-2">Este mes</p>
                </div>

                <!-- Ofrendas Mes -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-l-4 border-green-600">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 text-sm font-semibold">Ofrendas</h3>
                        <div class="bg-green-100 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11.5 16H5a3 3 0 01-3-3V7a1 1 0 012 0v6a1 1 0 001 1h6.5V4h-6a1 1 0 110-2h7a1 1 0 011 1v12a1 1 0 01-1 1zM8 5h4v10H8V5z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-4xl font-bold text-green-600" id="stat-ofrendas">$0</p>
                    <p class="text-xs text-gray-400 mt-2">Este mes</p>
                </div>

                <!-- Servidores Activos -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-l-4 border-orange-600">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 text-sm font-semibold">Servidores</h3>
                        <div class="bg-orange-100 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM9 3a6 6 0 11-12 0 6 6 0 0112 0zM17 10h-6v4h6v-4z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-4xl font-bold text-orange-600" id="stat-servidores">0</p>
                    <p class="text-xs text-gray-400 mt-2">Activos</p>
                </div>

                <!-- Usuarios Activos -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-l-4 border-pink-600">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 text-sm font-semibold">Usuarios</h3>
                        <div class="bg-pink-100 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-4xl font-bold text-pink-600" id="stat-usuarios">0</p>
                    <p class="text-xs text-gray-400 mt-2">Activos</p>
                </div>
            </div>

        <!-- Gráficas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Gráfica: Asistencia por Mes -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-600">
                <h3 class="text-lg font-bold text-gray-800 mb-4 text-gray-700">Asistencia por Mes</h3>
                <div class="h-80">
                    <canvas id="chart-asistencia"></canvas>
                </div>
            </div>

            <!-- Gráfica: Ofrendas por Mes -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-green-600">
                <h3 class="text-lg font-bold text-gray-800 mb-4 text-gray-700">Ofrendas por Mes</h3>
                <div class="h-80">
                    <canvas id="chart-ofrendas"></canvas>
                </div>
            </div>

            <!-- Gráfica: Estado de Células -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-purple-600">
                <h3 class="text-lg font-bold text-gray-800 mb-4 text-gray-700">Estado de Células</h3>
                <div class="h-80">
                    <canvas id="chart-celulas"></canvas>
                </div>
            </div>

            <!-- Gráfica: Servidores por Área -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-orange-600">
                <h3 class="text-lg font-bold text-gray-800 mb-4 text-gray-700">Servidores por Área</h3>
                <div class="h-80">
                    <canvas id="chart-servidores"></canvas>
                </div>
            </div>
        </div>

        <!-- Últimas Reuniones -->
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-indigo-600 mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4 text-gray-700">Últimas Reuniones Registradas</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Célula</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Fecha</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-700">Asistentes</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-700">Ofrenda</th>
                        </tr>
                    </thead>
                    <tbody id="ultimas-reuniones">
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">Cargando...</td>
                            <td colspan="3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </main>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        let chartAsistencia, chartOfrendas, chartCelulas, chartServidores;

        // Mostrar fecha actual
        function actualizarFecha() {
            const hoy = new Date();
            const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('fecha-actual').textContent = 
                '📅 ' + hoy.toLocaleDateString('es-ES', opciones);
        }

        // Cargar datos del dashboard
        async function cargarDashboard() {
            try {
                const response = await fetch(BASE_URL + 'api/dashboard.php?tipo=todo');
                
                if (!response.ok) {
                    throw new Error('Error al cargar dashboard');
                }

                const datos = await response.json();
                
                // Actualizar estadísticas
                if (datos.estadisticas) {
                    document.getElementById('stat-celulas').textContent = 
                        datos.estadisticas.celulas_activas || 0;
                    document.getElementById('stat-asistentes').textContent = 
                        datos.estadisticas.asistentes_mes || 0;
                    document.getElementById('stat-ofrendas').textContent = 
                        '$' + (datos.estadisticas.ofrendas_mes || 0).toFixed(2);
                    document.getElementById('stat-servidores').textContent = 
                        datos.estadisticas.servidores_activos || 0;
                    document.getElementById('stat-usuarios').textContent = 
                        datos.estadisticas.usuarios_activos || 0;
                }

                // Cargar gráficas
                cargarGraficas(datos);

                // Últimas reuniones
                if (datos.ultimas_reuniones && datos.ultimas_reuniones.length > 0) {
                    mostrarUltimasReuniones(datos.ultimas_reuniones);
                }

            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Mostrar últimas reuniones
        function mostrarUltimasReuniones(reuniones) {
            const tabla = document.getElementById('ultimas-reuniones');
            tabla.innerHTML = reuniones.map(r => `
                <tr class="border-b hover:bg-blue-50 transition">
                    <td class="px-4 py-4 font-semibold text-gray-800">${r.celula || 'N/A'}</td>
                    <td class="px-4 py-4 text-gray-600">${new Date(r.fecha).toLocaleDateString('es-ES')}</td>
                    <td class="px-4 py-4 text-center text-gray-700 font-medium">${r.numero_asistentes || 0}</td>
                    <td class="px-4 py-4 text-right font-bold text-green-600">$${(r.ofrenda_monto || 0).toFixed(2)}</td>
                </tr>
            `).join('');
        }

        // Cargar gráficas
        function cargarGraficas(datos) {
            // Gráfica: Asistencia por Mes
            if (datos.asistencia_meses && datos.asistencia_meses.length > 0) {
                const ctxAsistencia = document.getElementById('chart-asistencia').getContext('2d');
                
                if (chartAsistencia) chartAsistencia.destroy();
                
                chartAsistencia = new Chart(ctxAsistencia, {
                    type: 'line',
                    data: {
                        labels: datos.asistencia_meses.map(d => d.nombre_mes || d.mes),
                        datasets: [{
                            label: 'Total Asistentes',
                            data: datos.asistencia_meses.map(d => d.total_asistentes || 0),
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: '#667eea'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, position: 'top' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            // Gráfica: Ofrendas por Mes
            if (datos.ofrendas_meses && datos.ofrendas_meses.length > 0) {
                const ctxOfrendas = document.getElementById('chart-ofrendas').getContext('2d');
                
                if (chartOfrendas) chartOfrendas.destroy();
                
                chartOfrendas = new Chart(ctxOfrendas, {
                    type: 'bar',
                    data: {
                        labels: datos.ofrendas_meses.map(d => d.nombre_mes || d.mes),
                        datasets: [{
                            label: 'Total Ofrendas ($)',
                            data: datos.ofrendas_meses.map(d => d.total_ofrendas || 0),
                            backgroundColor: '#10b981',
                            borderColor: '#059669',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            // Gráfica: Estado de Células
            if (datos.celulas_estado && datos.celulas_estado.length > 0) {
                const ctxCelulas = document.getElementById('chart-celulas').getContext('2d');
                
                if (chartCelulas) chartCelulas.destroy();
                
                chartCelulas = new Chart(ctxCelulas, {
                    type: 'doughnut',
                    data: {
                        labels: datos.celulas_estado.map(d => d.estado || 'Desconocido'),
                        datasets: [{
                            data: datos.celulas_estado.map(d => d.cantidad || 0),
                            backgroundColor: ['#667eea', '#f59e0b', '#ef4444'],
                            borderColor: ['#fff'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, position: 'bottom' }
                        }
                    }
                });
            }

            // Gráfica: Servidores por Área
            if (datos.servidores_area && datos.servidores_area.length > 0) {
                const ctxServidores = document.getElementById('chart-servidores').getContext('2d');
                
                if (chartServidores) chartServidores.destroy();
                
                chartServidores = new Chart(ctxServidores, {
                    type: 'bar',
                    data: {
                        labels: datos.servidores_area.map(d => d.area || 'N/A'),
                        datasets: [{
                            label: 'Cantidad de Servidores',
                            data: datos.servidores_area.map(d => d.cantidad || 0),
                            backgroundColor: '#f97316',
                            borderColor: '#ea580c',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true }
                        },
                        scales: {
                            x: { beginAtZero: true }
                        }
                    }
                });
            }
        }

        // Logout
        async function logout() {
            try {
                const response = await fetch(BASE_URL + 'api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'logout' })
                });

                window.location.href = BASE_URL + 'login';
            } catch (error) {
                console.error('Error en logout:', error);
                window.location.href = BASE_URL + 'login';
            }
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            actualizarFecha();
            cargarDashboard();
            setInterval(cargarDashboard, 300000); // Actualizar cada 5 minutos
        });
    </script>
</body>
</html>
