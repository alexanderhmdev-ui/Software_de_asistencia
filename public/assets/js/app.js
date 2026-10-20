document.addEventListener('DOMContentLoaded', () => {
    const themeButton = document.querySelector('[data-theme-toggle]');
    const savedTheme = localStorage.getItem('simon-theme');
    if (savedTheme === 'dark') document.body.classList.add('dark');
    const updateThemeButton = () => {
        const dark = document.body.classList.contains('dark');
        themeButton?.setAttribute('aria-pressed', String(dark));
        if (themeButton) themeButton.textContent = dark ? 'Modo claro' : 'Modo oscuro';
    };
    updateThemeButton();
    themeButton?.addEventListener('click', () => {
        document.body.classList.toggle('dark');
        localStorage.setItem('simon-theme', document.body.classList.contains('dark') ? 'dark' : 'light');
        updateThemeButton();
    });

    const sidebar = document.querySelector('[data-sidebar]');
    const menuButton = document.querySelector('[data-menu-toggle]');
    menuButton?.addEventListener('click', () => sidebar?.classList.toggle('open'));

    document.querySelectorAll('.nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            document.querySelectorAll('.nav-link').forEach((item) => item.classList.remove('active'));
            link.classList.add('active');
            sidebar?.classList.remove('open');
        });
    });

    const imageModal = document.querySelector('[data-image-modal]');
    const imagePreview = imageModal?.querySelector('[data-image-preview]');
    const closeImage = () => { if (imageModal) imageModal.hidden = true; };
    document.querySelectorAll('[data-image-src]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            imagePreview.src = trigger.dataset.imageSrc;
            imagePreview.alt = trigger.dataset.imageAlt || '';
            imageModal.hidden = false;
        });
    });
    imageModal?.querySelector('[data-image-close]')?.addEventListener('click', closeImage);
    imageModal?.addEventListener('click', (event) => { if (event.target === imageModal) closeImage(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeImage(); });

    const attendanceForm = document.querySelector('[data-attendance-form]');
    const attendanceTable = document.querySelector('[data-attendance-table]');
    const attendanceFeedback = document.querySelector('[data-attendance-feedback]');
    const loadMoreButton = document.querySelector('[data-load-more]');
    let attendanceRequest;
    let attendanceOffset = 0;

    const renderAttendance = (records, append = false) => {
        if (!append) attendanceTable.replaceChildren();
        if (!records.length) {
            if (!append) attendanceFeedback.textContent = 'No hay registros para esta consulta.';
            return;
        }
        attendanceFeedback.textContent = append ? 'Más registros cargados.' : `${records.length} registros encontrados.`;
        const fragment = document.createDocumentFragment();
        records.forEach((record) => {
            const row = document.createElement('div');
            row.className = 'attendance-row';
            const student = document.createElement('span');
            const name = document.createElement('strong');
            name.textContent = record.student_name;
            const details = document.createElement('small');
            details.textContent = `DNI ${record.dni} · ${record.grade}${record.section ? ` · ${record.section}` : ''}`;
            student.append(name, details);
            const date = document.createElement('span');
            date.textContent = record.fecha;
            const status = document.createElement('span');
            const statusClass = String(record.status).toLowerCase().replaceAll(' ', '-');
            status.className = `attendance attendance-${statusClass}`;
            status.textContent = record.status;
            row.append(student, date, status);
            if (attendanceTable.dataset.canEdit === '1') {
                const edit = document.createElement('button'); edit.type = 'button'; edit.className = 'detail-button'; edit.textContent = 'Editar';
                edit.addEventListener('click', () => editAttendance(record));
                row.append(edit);
            }
            fragment.append(row);
        });
        attendanceTable.append(fragment);
    };

    const editAttendance = async (record) => {
        const status = window.prompt('Estado: presente, tardanza, falta o justificada', record.attendance_status === 'presente' && record.status === 'falta automática' ? 'falta' : record.attendance_status);
        if (!['presente', 'tardanza', 'falta', 'justificada'].includes(status)) return;
        const observation = window.prompt('Observación o justificación', record.observation || 'Revisado por administración.');
        if (!observation) return;
        const form = new FormData(); form.append('csrf_token', attendanceTable.dataset.csrf); form.append('dni', record.dni); form.append('date', record.fecha.slice(0, 10)); form.append('status', status); form.append('observation', observation);
        const response = await fetch('update_attendance.php', { method: 'POST', body: form, headers: { Accept: 'application/json' } });
        if (!response.ok) { window.alert('No se pudo guardar la corrección.'); return; }
        loadAttendance(false);
    };

    const loadAttendance = async (append = false) => {
        const formData = new FormData(attendanceForm);
        const query = new URLSearchParams({ month: formData.get('month'), search: formData.get('search'), offset: String(append ? attendanceOffset : 0) });
        attendanceFeedback.textContent = 'Cargando registros...';
        attendanceRequest?.abort();
        attendanceRequest = new AbortController();
        try {
            const response = await fetch(`${attendanceTable.dataset.endpoint}?${query}`, { headers: { Accept: 'application/json' }, signal: attendanceRequest.signal });
            if (!response.ok) throw new Error('attendance request failed');
            const data = await response.json();
            const records = data.records || [];
            renderAttendance(records, append);
            attendanceOffset = append ? attendanceOffset + records.length : records.length;
            if (loadMoreButton) loadMoreButton.hidden = !data.has_more;
        } catch (error) {
            if (error.name === 'AbortError') return;
            attendanceTable.replaceChildren();
            attendanceFeedback.textContent = 'No se pudo cargar la asistencia. Intenta nuevamente.';
        }
    };

    attendanceForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        attendanceOffset = 0;
        loadAttendance(false);
    });
    loadMoreButton?.addEventListener('click', () => loadAttendance(true));

    const studentForm = document.querySelector('[data-student-form]');
    const studentResults = document.querySelector('[data-student-results]');
    const studentFeedback = document.querySelector('[data-student-feedback]');
    const renderStudents = (students) => {
        studentResults.replaceChildren();
        studentFeedback.textContent = students.length ? `${students.length} estudiantes encontrados.` : 'No hay estudiantes para esos filtros.';
        const fragment = document.createDocumentFragment();
        students.forEach((student) => {
            const row = document.createElement('article'); row.className = 'directory-row';
            const identity = document.createElement('div');
            const name = document.createElement('strong'); name.textContent = student.name;
            const details = document.createElement('small'); details.textContent = `DNI ${student.dni} · Grado ${student.grade} · Sección ${student.section}`;
            identity.append(name, details);
            const average = document.createElement('div'); average.className = 'average'; average.textContent = `${student.average}%`;
            const label = document.createElement('small'); label.textContent = 'promedio del mes'; average.append(label);
            const button = document.createElement('button'); button.className = 'detail-button'; button.type = 'button'; button.textContent = 'Ver detalle';
            const detail = document.createElement('div'); detail.className = 'student-detail'; detail.hidden = true; detail.textContent = `${student.present} presentes · ${student.late} tardanzas · ${student.absent} faltas · ${student.total} registros del mes`;
            button.addEventListener('click', () => { detail.hidden = !detail.hidden; button.textContent = detail.hidden ? 'Ver detalle' : 'Ocultar detalle'; });
            row.append(identity, average, button, detail); fragment.append(row);
        });
        studentResults.append(fragment);
    };
    const loadStudents = async () => {
        const values = Object.fromEntries(new FormData(studentForm).entries());
        studentFeedback.textContent = 'Buscando estudiantes...';
        try {
            const query = new URLSearchParams(values);
            const response = await fetch(`${studentResults.dataset.endpoint}?${query}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('students request failed');
            renderStudents((await response.json()).students || []);
        } catch { studentFeedback.textContent = 'No se pudo consultar estudiantes.'; studentResults.replaceChildren(); }
    };
    studentForm?.addEventListener('submit', (event) => { event.preventDefault(); loadStudents(); });

    document.querySelectorAll('[data-count]').forEach((element) => {
        const target = Number(element.dataset.count || 0);
        const duration = 650;
        const started = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - started) / duration, 1);
            element.textContent = String(Math.round(target * (1 - Math.pow(1 - progress, 3))));
            if (progress < 1) requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
    });
});
