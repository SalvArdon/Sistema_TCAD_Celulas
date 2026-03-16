<?php
/**
 * VISTA - ERROR 404
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - No Encontrado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-600 to-pink-600 min-h-screen flex items-center justify-center">
    <div class="text-center text-white">
        <h1 class="text-8xl font-bold mb-4">404</h1>
        <p class="text-2xl mb-6">Página no encontrada</p>
        <a href="<?php echo BASE_URL; ?>" class="bg-white text-purple-600 font-bold px-6 py-3 rounded-lg hover:bg-gray-100">
            ← Volver al inicio
        </a>
    </div>
</body>
</html>
