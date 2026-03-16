<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema TCAD Células - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Card Login -->
        <div class="bg-white rounded-lg shadow-2xl p-8">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">TCAD</h1>
                <p class="text-gray-600 text-sm mt-2">Sistema de Control de Células</p>
            </div>

            <!-- Formulario Login -->
            <form id="form-login" method="POST" class="space-y-4">
                <!-- Correo -->
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700 mb-2">
                        Correo Electrónico
                    </label>
                    <input 
                        type="email" 
                        id="correo" 
                        name="correo" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition"
                        placeholder="tu@correo.com"
                    >
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition"
                        placeholder="••••••••"
                    >
                </div>

                <!-- Mensaje de error -->
                <div id="error-msg" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <span id="error-text"></span>
                </div>

                <!-- Botón Login -->
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-3 rounded-lg hover:shadow-lg transition duration-300"
                >
                    Iniciar Sesión
                </button>
            </form>

            <!-- Información adicional -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Sistema para administración de células y comunidades iglesia</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-white text-sm">
            <p>Sistema TCAD Células v1.0.0</p>
        </div>
    </div>

    <script>
        document.getElementById('form-login').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const correo = document.getElementById('correo').value;
            const password = document.getElementById('password').value;
            const errorMsg = document.getElementById('error-msg');
            const errorText = document.getElementById('error-text');

            try {
                const endpoint = '<?php echo BASE_URL; ?>api/auth.php';
                console.log('Enviando login a:', endpoint);
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        accion: 'login',
                        correo: correo,
                        password: password
                    })
                });

                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }

                const data = await response.json();
                console.log('Response data:', data);

                if (data.exito) {
                    window.location.href = '<?php echo BASE_URL; ?>dashboard';
                } else {
                    errorMsg.classList.remove('hidden');
                    errorText.textContent = data.mensaje || 'Error desconocido';
                }
            } catch (error) {
                console.error('Error completo:', error);
                errorMsg.classList.remove('hidden');
                errorText.textContent = 'Error en la conexión: ' + error.message;
            }
        });
    </script>
</body>
</html>
