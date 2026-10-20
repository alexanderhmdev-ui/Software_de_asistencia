<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/img/SB.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <?php
    $user = $user ?? ['full_name' => '', 'role' => 'padre'];
    $summary = $summary ?? ['posts' => 0, 'students' => 0, 'users' => 0, 'linked_parents' => 0, 'teachers' => 0, 'present' => 0, 'late' => 0, 'absent' => 0];
    $children = $children ?? [];
    $posts = $posts ?? [];
    $attendance = $attendance ?? [];
    $month = $month ?? date('Y-m');
    $search = $search ?? '';
    $attendanceSource = $attendanceSource ?? 'MySQL';
    $postErrors = $postErrors ?? [];
    $view = $view ?? 'inicio';
    $roleLabel = $user['role'] === 'padre' ? 'Tutor' : ucfirst($user['role']);
    $viewTitles = ['inicio' => 'Inicio', 'publicaciones' => 'Publicaciones', 'asistencia' => 'Asistencia', 'estudiantes' => 'Estudiantes', 'conocenos' => 'Conócenos mejor'];
    ?>
    <div class="app-shell">
        <aside class="sidebar" data-sidebar>
            <div class="brand"><span class="brand-mark">SB</span><div><strong>Simon Bolivar</strong><small>Comunidad educativa</small></div></div>
            <nav class="main-nav" aria-label="Navegación principal">
                <?php foreach ($viewTitles as $key => $title): ?><a class="nav-link <?= $view === $key ? 'active' : '' ?>" href="index.php?view=<?= e($key) ?>"><?= e($title) ?></a><?php endforeach; ?>
            </nav>
            <div class="sidebar-footer"><span class="status">Sesión protegida</span><a href="logout.php">Cerrar sesión</a></div>
        </aside>
        <main class="dashboard" id="<?= e($view) ?>">
        <header class="topbar reveal">
            <div>
                <p class="eyebrow">I.E. SIMON BOLIVAR</p>
                <h1>Panel de gestión</h1>
            </div>
            <button class="menu-button" type="button" data-menu-toggle aria-label="Abrir menú">Menú</button>
            <div class="account"><button class="theme-button" type="button" data-theme-toggle aria-pressed="false">Modo oscuro</button><span><?= e($user['full_name']) ?> · <?= e($roleLabel) ?></span></div>
        </header>

        <?php if ($view === 'inicio'): ?><section class="welcome reveal">
            <p class="eyebrow">Resumen operativo</p>
            <h2>Hola, <?= e($user['full_name']) ?></h2>
            <p>Consulta la actividad de tu institución desde un solo lugar.</p>
        </section>

        <section class="metric-grid" aria-label="Resumen de actividad">
            <article class="metric reveal"><span>Comunicados</span><strong data-count="<?= $summary['posts'] ?>">0</strong><small>Publicaciones registradas</small></article>
            <article class="metric reveal"><span><?= $user['role'] === 'padre' ? 'Mis estudiantes' : 'Estudiantes' ?></span><strong data-count="<?= $summary['students'] ?>">0</strong><small>Personas vinculadas</small></article>
            <?php if ($user['role'] === 'admin'): ?><article class="metric reveal"><span>Padres vinculados</span><strong data-count="<?= $summary['linked_parents'] ?>">0</strong><small>De <?= $summary['users'] ?> cuentas registradas</small></article><article class="metric reveal"><span>Docentes</span><strong data-count="<?= $summary['teachers'] ?>">0</strong><small>Cuentas con rol docente</small></article><?php endif; ?>
            <article class="metric reveal"><span>Asistencias hoy</span><strong data-count="<?= $summary['present'] + $summary['late'] + $summary['absent'] ?>">0</strong><small><?= $summary['present'] ?> presentes · <?= $summary['late'] ?> tardanzas · <?= $summary['absent'] ?> faltas</small></article>
        </section><?php endif; ?>

        <?php if ($view === 'inicio' && $user['role'] === 'padre'): ?>
            <section class="section-block reveal">
                <div class="section-heading"><div><p class="eyebrow">Seguimiento familiar</p><h2>Asistencia de hoy</h2></div><span class="status"><?= count($children) ?> <?= count($children) === 1 ? 'estudiante' : 'estudiantes' ?></span></div>
                <div class="student-list">
                    <?php if (!$children): ?><p class="muted">Todavía no tienes estudiantes vinculados a tu cuenta.</p><?php endif; ?>
                    <?php foreach ($children as $child): ?>
                        <article class="student-row"><div><strong><?= e($child['name']) ?></strong><small><?= e($child['grade'] ?: 'Grado no definido') ?><?= $child['section'] ? ' · Sección ' . e($child['section']) : '' ?></small></div><span class="attendance attendance-<?= e(str_replace(' ', '-', $child['today_status'])) ?>"><?= e(ucfirst($child['today_status'])) ?></span></article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($view === 'publicaciones'): ?><section class="section-block reveal" id="publicaciones">
            <div class="section-heading"><div><p class="eyebrow">Información reciente</p><h2>Comunicados</h2></div><span class="status">Actualizado ahora</span></div>
            <?php if (in_array($user['role'], ['admin', 'docente'], true)): ?><details class="post-composer"><summary>Subir publicación</summary><form method="POST" action="create_post.php" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="composer-grid"><label>Título<input type="text" name="title" maxlength="150" required></label><label>Tipo<select name="type"><option value="aviso">Aviso</option><option value="evento">Evento</option><option value="app">Aplicación</option></select></label></div><label>Descripción<textarea name="description" rows="4" maxlength="5000" required></textarea></label><label>Imagen (opcional)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><button type="submit">Publicar</button></form></details><?php endif; ?>
            <?php if ($postErrors): ?><div class="error-msg"><?= e(implode(' ', $postErrors)) ?></div><?php endif; ?>
            <?php if (isset($_GET['created'])): ?><div class="success-msg">Publicación creada correctamente.</div><?php endif; ?>
            <div class="post-list <?= $user['role'] === 'padre' ? 'post-list-family' : '' ?>">
                <?php if (!$posts): ?><p class="muted">Aún no hay comunicados publicados.</p><?php endif; ?>
                <?php foreach ($posts as $post): ?>
                    <article class="post-row"><div><span class="post-type"><?= e(strtoupper($post['type'])) ?></span><h3><?= e($post['title']) ?></h3><p><?= e($post['descripcion'] ?: 'Sin descripción') ?></p><?php if (!empty($post['image_patch'])): ?><button class="post-image-trigger" type="button" data-image-src="<?= e($post['image_patch']) ?>" data-image-alt="Imagen de <?= e($post['title']) ?>"><img class="post-image" src="<?= e($post['image_patch']) ?>" alt="Imagen de <?= e($post['title']) ?>"><span>Ver imagen</span></button><?php endif; ?></div><div class="post-meta"><span><?= e($post['author']) ?></span><small><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?> · <?= (int) $post['likes'] ?> likes</small></div></article>
                <?php endforeach; ?>
            </div>
        </section><?php endif; ?>
        <?php if ($view === 'asistencia'): ?><section class="section-block reveal" id="asistencia">
            <div class="section-heading"><div><p class="eyebrow">Consulta histórica</p><h2>Asistencia por mes</h2></div><span class="status">Fuente actual: <?= e($attendanceSource) ?></span></div>
            <form class="month-filter" method="GET" action="attendance.php" data-attendance-form><label for="month">Mes<input type="month" id="month" name="month" value="<?= e($month) ?>"></label><label for="search">Buscar por DNI o nombre<input type="search" id="search" name="search" value="<?= e($search) ?>" placeholder="Ej. 78364884" autocomplete="off"></label><button type="submit">Buscar</button></form>
            <p class="attendance-feedback" data-attendance-feedback aria-live="polite">Escribe un DNI o deja el campo vacío para mostrar todos los registros.</p>
            <div class="attendance-table" data-attendance-table data-endpoint="attendance.php" data-csrf="<?= e(csrf_token()) ?>" data-can-edit="<?= $user['role'] === 'admin' ? '1' : '0' ?>"></div>
            <button class="load-more" type="button" data-load-more hidden>Cargar más registros</button>
            <?php if ($user['role'] === 'admin'): ?><p class="admin-note">Como administrador puedes corregir el estado y registrar justificaciones. Las correcciones se guardan localmente y no modifican el Google Sheet.</p><?php endif; ?>
        </section><?php endif; ?>
        <?php if ($view === 'estudiantes'): ?><section class="section-block reveal" id="estudiantes">
            <div class="section-heading"><div><p class="eyebrow">Directorio académico</p><h2>Buscar estudiantes</h2></div><span class="status">Promedio mensual</span></div>
            <form class="student-filter" data-student-form><label>Mes<input type="month" name="month" value="<?= e($month) ?>"></label><label>DNI<input type="search" name="dni" placeholder="DNI del estudiante" inputmode="numeric"></label><label>Grado<select name="grade"><option value="">Todos</option><?php for ($gradeOption = 1; $gradeOption <= 5; $gradeOption++): ?><option value="<?= $gradeOption ?>"><?= $gradeOption ?></option><?php endfor; ?></select></label><label>Sección<select name="section"><option value="">Todas</option><?php foreach (range('A', 'E') as $sectionOption): ?><option value="<?= $sectionOption ?>"><?= $sectionOption ?></option><?php endforeach; ?></select></label><button type="submit">Buscar</button></form>
            <p class="attendance-feedback" data-student-feedback aria-live="polite">Completa un filtro o busca todos los estudiantes del mes.</p><div class="student-results" data-student-results data-endpoint="students.php"></div>
        </section><?php endif; ?>
        <?php if ($view === 'conocenos'): ?><section class="section-block reveal" id="conocenos">
            <div class="section-heading"><div><p class="eyebrow">Nuestra propuesta</p><h2>Conócenos mejor</h2></div></div>
            <div class="about-copy"><p>Esta plataforma reúne en un solo espacio la comunicación y el seguimiento de la comunidad educativa de la I.E. Simon Bolivar. Aquí las familias pueden consultar novedades y revisar la asistencia de sus estudiantes de forma clara y ordenada.</p><p>El proyecto crece por etapas: cada aplicación se incorpora cuando está lista para resolver una necesidad concreta de la institución.</p></div>
            <div class="apps-heading"><h3>Aplicaciones de la plataforma</h3><span>Espacio para nuevos módulos</span></div>
            <div class="app-grid"><article class="app-card"><span class="app-icon">01</span><h3>Asistencias</h3><p>Consulta la asistencia organizada por estudiante, DNI y fecha, con información sincronizada desde el registro institucional.</p><span class="app-state">Disponible</span></article><article class="app-card beta"><span class="app-icon">02</span><h3>Comunidad Simon</h3><p>Un espacio para novedades, actividades y comunicación entre las familias y el equipo educativo.</p><span class="app-state">Fase beta</span></article><article class="app-card app-placeholder"><span class="app-icon">03</span><h3>Próxima aplicación</h3><p>Este espacio queda reservado para el siguiente módulo que la institución quiera incorporar.</p><span class="app-state">En planificación</span></article></div>
        </section><?php endif; ?>
        </main>
    </div>
    <?php if ($view === 'publicaciones'): ?><div class="image-modal" data-image-modal hidden role="dialog" aria-modal="true" aria-label="Vista ampliada de publicación"><button class="image-modal-close" type="button" data-image-close aria-label="Cerrar imagen">Cerrar</button><img data-image-preview alt=""></div><?php endif; ?>
    <script src="assets/js/app.js" defer></script>
</body>
</html>
