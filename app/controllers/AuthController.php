<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';

final class AuthController
{
    public function __construct(private User $users)
    {
    }

    public function login(): void
    {
        $errors = [];
        $old = ['dni' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['dni'] = trim((string) ($_POST['dni'] ?? ''));
            if (!verify_csrf($_POST['csrf_token'] ?? null)) {
                $errors[] = 'La sesión del formulario expiró. Intenta nuevamente.';
            } elseif (!preg_match('/^[0-9]{6,20}$/', $old['dni'])) {
                $errors[] = 'Ingresa un DNI válido.';
            } else {
                $user = $this->users->findByDni($old['dni']);

                if ($user && (int) $user['active'] === 1) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int) $user['id'],
                        'dni' => $user['dni'],
                        'full_name' => $user['full_name'],
                        'role' => $user['role'],
                    ];
                    redirect('index.php');
                }

                $errors[] = 'El DNI no está registrado.';
            }
        }

        require __DIR__ . '/../views/login.php';
    }

    public function register(): void
    {
        $errors = [];
        $old = ['dni' => '', 'email' => '', 'full_name' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['dni'] = trim((string) ($_POST['dni'] ?? ''));
            $old['email'] = trim((string) ($_POST['email'] ?? ''));
            $old['full_name'] = trim((string) ($_POST['full_name'] ?? ''));

            if (!verify_csrf($_POST['csrf_token'] ?? null)) {
                $errors[] = 'La sesión del formulario expiró. Intenta nuevamente.';
            }
            if (!preg_match('/^[0-9]{6,20}$/', $old['dni'])) {
                $errors[] = 'El DNI debe contener entre 6 y 20 números.';
            }
            if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Ingresa un correo electrónico válido.';
            }
            if (mb_strlen($old['full_name']) < 3 || mb_strlen($old['full_name']) > 120) {
                $errors[] = 'Ingresa tu nombre completo.';
            }
            if (!$errors && $this->users->exists($old['dni'], $old['email'])) {
                $errors[] = 'El DNI o correo electrónico ya está registrado.';
            }

            if (!$errors) {
                $this->users->create($old['dni'], $old['email'], $old['full_name']);
                redirect('login.php?registered=1');
            }
        }

        require __DIR__ . '/../views/register.php';
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
        redirect('login.php');
    }
}
