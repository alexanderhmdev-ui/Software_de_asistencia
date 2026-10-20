<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | <?php echo e(APP_NAME); ?></title>
    <link rel="icon" type="image/png" href="assets/img/SB.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css?v=2">
</head>
<body>
    <?php
$errors = isset($errors) ? $errors : [];
$old = isset($old) ? $old : ['dni' => ''];
?>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-form-side">
                <div class="form-content">
                    <p class="tagline">I.E. Simon Bolivar</p>
                    <h3>Plataforma de Comunicados y Asistencias</h3>
                    <?php if (isset($_GET['registered'])): ?><p class="success-msg">Cuenta creada. Ya puedes iniciar sesión.</p><?php endif; ?>
                    <?php if ($errors): ?><div class="error-msg"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
                    <form method="POST" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <div class="field">
                            <label for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" value="<?= e($old['dni']) ?>" inputmode="numeric" autocomplete="username" placeholder="22334455" required>
                        </div>
                        <p class="access-note">Ingresa únicamente tu DNI registrado.</p>
                        <button type="submit" class="btn-main">Iniciar Sesión</button>
                    </form>
                    <p class="footer-text">¿No tienes cuenta? <a href="registro.php">Crea una aquí</a></p>
                </div>
            </div>
            <div class="auth-visual-side">
                <div class="visual-box">
                    <div class="school-logo-frame">
                        <img src="assets/img/SB.png" alt="Escudo de la I.E. Simon Bolivar">
                    </div>
                    <p class="school-name">I.E. Simon Bolivar</p>
                    <h2>Control Total</h2>
                    <p>Gestión centralizada de comunicados y asistencias en tiempo real.</p>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/auth.js" defer></script>
</body>
</html>
