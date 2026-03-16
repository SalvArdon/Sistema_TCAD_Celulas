<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Páginas en Construcción - Sistema TCAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <main class="lg:ml-64 pt-16">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <?php
            // Obtener el módulo solicitado
            $pagina = $partes[0] ?? '';
            
            // Cargar el autorizador
            require_once __DIR__ . '/../config/Autorizador.php';
            
            // Verificar permisos
            $tiene_permiso = Autorizador::puedeAcceder($pagina);
            
            if (!$tiene_permiso && !empty($pagina)) {
                // Acceso denegado
                ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-8">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">🚫</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                Acceso Denegado
                            </p>
                            <p class="text-sm text-red-700 mt-1">
                                No tienes permisos para acceder a este módulo. Tu rol es: <strong><?php echo Autorizador::nombreRol($_SESSION['rol']); ?></strong>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="text-6xl mb-4">🔒</div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Acceso Restringido</h1>
                    <p class="text-gray-600 mb-8">
                        Este módulo requiere un rol diferente. Si crees que es un error, contacta al administrador.
                    </p>
                    <a href="<?php echo BASE_URL; ?>dashboard" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition">
                        ← Volver al Dashboard
                    </a>
                </div>
                <?php
            } else {
                // Página en construcción
                ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <span class="text-2xl">🚧</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">
                                Esta página está en construcción
                            </p>
                            <p class="text-sm text-yellow-700 mt-1">
                                Vuelve pronto para ver el módulo completo
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="text-6xl mb-4">🏗️</div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Página en Construcción</h1>
                    <p class="text-gray-600 mb-8">
                        Este módulo está siendo desarrollado actualmente. La funcionalidad completa estará disponible pronto.
                    </p>
                    <a href="<?php echo BASE_URL; ?>dashboard" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition">
                        ← Volver al Dashboard
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </main>
</body>
</html>
