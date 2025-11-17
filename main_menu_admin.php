<?php
// Ensure this file handles session start and admin check
require_once 'connection.php'; 
session_start();

// Task 4.1 Requirement: Admin Access Check
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Disable caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$page_title = "Admin Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Merriweather:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
</head>

<body class="main-menu-page admin-dashboard-page">

    <header>
        <nav class="navbar navbar-expand-lg fixed-top admin-navbar"> 
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="main_menu_admin.php">
                    <img src="images/logo.svg" alt="Root Flowers Logo" class="navbar-logo me-2">
                    <span class="navbar-brand brand-logo-text text-white">Root Flowers Admin</span>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarContent" aria-controls="adminNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars text-white"></i> 
                </button>

                <div class="collapse navbar-collapse" id="adminNavbarContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        
                        <li class="nav-item">
                            <a class="btn btn-light rounded-pill ms-lg-3 px-3 py-2" href="main_menu_admin.php" title="Admin Home">
                                <i class="fas fa-home"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <button id="live-datetime-btn" 
                                    class="btn rounded-pill ms-lg-3 px-3 py-2" 
                                    data-bs-toggle="popover" 
                                    data-bs-placement="bottom">
                                <i class="far fa-calendar-alt me-2"></i> <span id="current-time"></span>
                            </button>
                        </li>
                        
                        <li class="nav-item">
                            <a href="http://localhost/phpmyadmin" class="btn btn-light rounded-pill ms-lg-3 px-3 py-2" role="button" title="Database Management">
                                <i class="fas fa-database me-1"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="start_impersonation.php" class="btn btn-light rounded-pill ms-lg-3 px-3 py-2" role="button" title="View as User">
                                <i class="fas fa-user-ninja me-1"></i> View Website as User
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="logout.php" class="btn btn-light rounded-pill ms-lg-3 px-3 py-2" role="button">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <section class="hero d-flex align-items-center justify-content-center text-center text-white admin-hero-section">
        <div class="hero-content">
            <section class="container my-5">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold">Admin Dashboard</h1>
                    <p class="lead">Welcome, <strong>Admin</strong>! Select a management task below.</p>
                </div>
                
                <div class="row g-4 justify-content-center">
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100 text-center shadow-sm admin-card">
                            <div class="card-body p-4 d-flex flex-column">
                                <i class="fas fa-users fs-1 mb-3 admin_icon"></i>
                                <h5 class="card-title">Manage User Accounts</h5>
                                <p class="card-text">Add, Edit, and Delete user login accounts.</p>
                                <a href="manage_accounts.php" class="btn btn-dark mt-auto">Go to Management</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100 text-center shadow-sm admin-card">
                            <div class="card-body p-4 d-flex flex-column">
                                <i class="fas fa-palette fs-1 mb-3 admin_icon"></i>
                                <h5 class="card-title">Manage Student Works</h5>
                                <p class="card-text">Approve, Reject, Edit, and Delete student submissions.</p>
                                <a href="manage_studentwork.php" class="btn btn-dark mt-auto">Go to Management</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card h-100 text-center shadow-sm admin-card">
                            <div class="card-body p-4 d-flex flex-column">
                                <i class="fas fa-calendar-check fs-1 mb-3 admin_icon"></i>
                                <h5 class="card-title">Manage Workshop Reg.</h5>
                                <p class="card-text">Approve, Reject, Edit, and Delete workshop sign-ups.</p>
                                <a href="manage_workshop_reg.php" class="btn btn-dark mt-auto">Go to Management</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Calendar iframe HTML. We must ensure the style attribute is correctly included.
        const CALENDAR_IFRAME_CONTENT = `
            <iframe 
                src="https://calendar.google.com/calendar/embed?showTitle=0&showPrint=0&showTabs=0&showCalendars=0&height=250&wkst=1&bgcolor=%23FFFFFF&ctz=Asia%2FKuala_Lumpur&src=e1970d1a001c20d5bc711fba8fe63995a030455091cb6d5ba1eb5d362bc35865%40group.calendar.google.com&color=%230B8043&mode=agenda" 
                style="border:none; width: 100%; height: 300px;">
            </iframe>
        `;

        // Live Clock Function
        function updateLiveClock() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            
            document.getElementById('current-time').textContent = `${dateStr} | ${timeStr}`;
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Run the clock
            updateLiveClock();
            setInterval(updateLiveClock, 1000);

            // --- START OF FIX ---
            
            // 1. Get a copy of the default allowed tags and attributes
            const customAllowList = {
                ...bootstrap.Popover.Default.allowList,
                // 2. Add 'iframe' and its necessary attributes to the list
                iframe: ['src', 'style', 'width', 'height', 'frameborder', 'allowfullscreen']
            };

            // --- END OF FIX ---

            // Initialize Popover via JS for reliability
            const datetimeBtn = document.getElementById('live-datetime-btn');
            if (datetimeBtn) {
                new bootstrap.Popover(datetimeBtn, {
                    container: 'body',
                    placement: 'bottom',
                    html: true,
                    customClass: 'admin-calendar-popover',
                    title: 'Current Date and Time',
                    content: CALENDAR_IFRAME_CONTENT,
                    allowList: customAllowList // <-- 3. Pass your new custom list here
                });
            }
        });
    </script>
</body>
</html>