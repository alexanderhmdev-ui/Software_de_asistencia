<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/bootstrap.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/models/GoogleAttendance.php';

require_auth();
(new DashboardController(new Dashboard(db()), new GoogleAttendance(GOOGLE_SHEET_ID, GOOGLE_SHEET_GID)))->index($_SESSION['user']);