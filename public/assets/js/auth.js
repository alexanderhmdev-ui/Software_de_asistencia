document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        button.addEventListener('click', () => {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.textContent = showing ? 'Mostrar' : 'Ocultar';
            button.setAttribute('aria-label', `${showing ? 'Mostrar' : 'Ocultar'} contraseña`);
            button.setAttribute('aria-pressed', String(!showing));
        });
    });
});
