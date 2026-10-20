# I.E. Simon Bolivar

## Estructura

- `app/controllers`: controladores de autenticacion y panel.
- `app/models`: acceso a datos y reglas del dominio.
- `app/views`: vistas HTML de inicio de sesion, registro y panel.
- `app/config`: configuracion y arranque de PHP.
- `public`: puntos de entrada web y recursos estaticos.

## Ejecucion local con Laragon

Se puede conservar la raiz virtual actual (`beta`). Apache reescribe las
solicitudes hacia `public/` mediante `.htaccess`; habilita `mod_rewrite` y
permite `AllowOverride All` para que funcionen las rutas y los recursos.
Tambien se puede configurar `public/` directamente como DocumentRoot.

La configuracion opcional de la base de datos se guarda en `.env` en la raiz
del proyecto. Usa `.env.example` como referencia y no publiques las credenciales.
