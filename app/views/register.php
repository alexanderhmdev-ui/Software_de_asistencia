<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/img/SB.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css?v=2">
</head>
<body>
    <?php $errors = $errors ?? []; $old = $old ?? ['dni' => '', 'email' => '', 'full_name' => '']; ?>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-form-side">
                <div class="form-content">
                    <p class="tagline">I.E. SIMON BOLIVAR</p>
                    <h1>Crear una cuenta</h1>
                    <?php if ($errors): ?><div class="error-msg"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
                    <form method="POST" action="registro.php">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <div class="field">
                            <label for="full_name">Nombre completo</label>
                            <input type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" autocomplete="name" required>
                        </div>
                        <div class="field">
                            <label for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" value="<?= e($old['dni']) ?>" inputmode="numeric" autocomplete="username" placeholder="12345678" required>
                        </div>
                        <div class="field">
                            <label for="email">Correo electrónico</label>
                            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" autocomplete="email" placeholder="tu@correo.com" required>
                        </div>
                        <p class="access-note">Después podrás iniciar sesión solo con tu DNI.</p>
                        <button type="submit" class="btn-main">Crear Cuenta</button>
                        <p class="footer-text">¿Ya tienes cuenta? <a href="login.php">Inicia Sesión aquí</a></p>
                    </form>
                </div>
            </div>
            <div class="auth-visual-side">
                <div class="visual-box">
                    <div class="school-logo-frame">
                        <img src="assets/img/SB.png" alt="Escudo de la I.E. Simon Bolivar">
                    </div>
                    <p class="school-name">I.E. Simon Bolivar</p>
                    <h2>Comunicación clara</h2>
                    <p>Centraliza la información de tu institución y mantén a todos al día.</p>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/auth.js" defer></script>
</body>
</html>
