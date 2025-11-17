<?php
require_once 'connection.php';
session_start();

// Task 4.1/4.2 Requirement: Admin Access Check
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Disable caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$page_title = "Manage User Accounts";
$message = '';
$error = '';

// --- HANDLE DELETE USER ---
if (isset($_GET['delete_email'])) {
    try {
        // Deleting from user_table will cascade delete from account_table
        $stmt = $conn->prepare("DELETE FROM user_table WHERE email = ?");
        $stmt->execute([$_GET['delete_email']]);
        $message = "User '{$_GET['delete_email']}' has been deleted successfully.";
    } catch (PDOException $e) {
        $error = "Error deleting user: " . $e->getMessage();
    }
}

// --- HANDLE ADD USER ---
if (isset($_POST['add_user'])) {
    $email = $_POST['email'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $hometown = $_POST['hometown'];
    $password = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $profile_image = 'default_profile.png'; // Default image for new users

    if ($password !== $confirm_pass) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if email already exists
            $check = $conn->prepare("SELECT COUNT(*) FROM user_table WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetchColumn() > 0) {
                $error = "A user with this email already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $conn->beginTransaction();
                
                // Insert into user_table, including the default profile_image
                $stmt1 = $conn->prepare("INSERT INTO user_table (email, first_name, last_name, dob, gender, hometown, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt1->execute([$email, $fname, $lname, $dob, $gender, $hometown, $profile_image]);
                
                // Insert into account_table
                $stmt2 = $conn->prepare("INSERT INTO account_table (email, password, type) VALUES (?, ?, 'user')");
                $stmt2->execute([$email, $hashed_password]);
                
                $conn->commit();
                $message = "User '$email' created successfully.";
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Error adding user: " . $e->getMessage();
        }
    }
}

// --- HANDLE EDIT USER ---
if (isset($_POST['edit_user'])) {
    $original_email = $_POST['original_email'];
    $email = $_POST['email'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $hometown = $_POST['hometown'];
    $password = $_POST['password'];
    // Profile image is not edited via this modal, only displayed.

    try {
        $conn->beginTransaction();
        
        // Update user_table
        $stmt = $conn->prepare("UPDATE user_table SET email = ?, first_name = ?, last_name = ?, dob = ?, gender = ?, hometown = ? WHERE email = ?");
        $stmt->execute([$email, $fname, $lname, $dob, $gender, $hometown, $original_email]);

        // Check if password needs updating
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_pass = $conn->prepare("UPDATE account_table SET password = ? WHERE email = ?");
            $stmt_pass->execute([$hashed_password, $email]);
        }
        
        $conn->commit();
        $message = "User '$email' updated successfully.";
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error updating user: " . $e->getMessage();
    }
}


$stmt = $conn->prepare("SELECT u.*, a.type FROM user_table u JOIN account_table a ON u.email = a.email WHERE a.type = 'user' ORDER BY u.first_name");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<body class="admin-user-manager-page">

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
                            <a href="logout.php" class="btn btn-light rounded-pill ms-lg-3 px-3 py-2" role="button">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <section class="hero text-center text-white admin-hero-section">
        <div class="hero-content">
            <div class="container py-5">
                <h1 class="display-4 fw-bold text-white">Manage User Accounts</h1>
                <p class="lead">Add, edit, and delete user login accounts.</p>
            </div>
    
            <div class="container admin-card-container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card h-100 shadow-lg admin-card" style="border: none;">
                            <div class="card-body p-4 p-md-5">
                                
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title" style="font-size: 1.5rem; margin-bottom: 0;">All Users (<?php echo count($users); ?>)</h5>
                                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                        <i class="fas fa-plus me-2"></i> Add New User
                                    </button>
                                </div>
                                
                                <?php if ($message): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <?php echo $message; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php echo $error; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle admin-user-table">
                                        <thead>
                                            <tr>
                                                <th></th> <th>Name</th>
                                                <th>Email</th>
                                                <th>Date of Birth</th>
                                                <th>Gender</th>
                                                <th>Hometown</th>
                                                <th>Resume</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($users)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No users found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                            // Path to profile images
                                                            $profile_img_path = htmlspecialchars($user['profile_image']);
                                                            
                                                            // Check if the path is empty or the file doesn't exist
                                                            if (empty($user['profile_image']) || !file_exists($profile_img_path)) {
                                                                // If no image, use a default based on gender
                                                                if ($user['gender'] === 'Female') {
                                                                    $profile_img_path = 'images/profile_pic/girl.png';
                                                                } else {
                                                                    $profile_img_path = 'images/profile_pic/boys.jpg';
                                                                }
                                                            }
                                                        ?>
                                                        <img src="<?php echo $profile_img_path; ?>" alt="Profile" class="profile-thumb">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['dob']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['gender']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['hometown']); ?></td>
                                                    <td>
                                                        <?php 
                                                            $resume_path = htmlspecialchars($user['resume']);
                                                            
                                                            // Check if a resume file path is actually set
                                                            if (!empty($resume_path)) {
                                                                
                                                                // Get just the filename for the hover-over title
                                                                $resume_filename = htmlspecialchars(basename($user['resume']));
                                                                
                                                                // Create a small button link. target="_blank" opens it in a new tab.
                                                                echo "<a href='{$resume_path}' 
                                                                        target='_blank' 
                                                                        class='btn btn-outline-dark btn-sm' 
                                                                        title='View: {$resume_filename}'>
                                                                            <i class='fas fa-file-alt me-1'></i> View
                                                                    </a>";
                                                            } else {
                                                                // If no resume, display a muted, italic 'N/A'
                                                                echo "<span class='text-muted fst-italic'>N/A</span>";
                                                            }
                                                        ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <button class="btn btn-primary btn-sm edit-btn" 
                                                                title="Edit User"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editUserModal"
                                                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                                data-fname="<?php echo htmlspecialchars($user['first_name']); ?>"
                                                                data-lname="<?php echo htmlspecialchars($user['last_name']); ?>"
                                                                data-dob="<?php echo htmlspecialchars($user['dob']); ?>"
                                                                data-gender="<?php echo htmlspecialchars($user['gender']); ?>"
                                                                data-hometown="<?php echo htmlspecialchars($user['hometown']); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <a href="manage_accounts.php?delete_email=<?php echo htmlspecialchars($user['email']); ?>" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Delete User"
                                                        onclick="return confirm('Are you sure you want to delete this user?\nThis action cannot be undone.');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>
    </section>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Calendar iframe HTML. We must ensure the style attribute is correctly included.
        const CALENDAR_IFRAME_CONTENT = `
            <iframe 
                src="https://calendar.google.com/calendar/embed?showTitle=0&showPrint=0&showTabs=0&showCalendars=0&height=250&wkst=1&bgcolor=%23FFFFFF&ctz=Asia%2FKuala_Lumpur&src=en.malaysian%23holiday%40group.v.calendar.google.com&color=%230B8043&mode=month" 
                style="border:none; width: 100%; height: 250px;">
            </iframe>
        `;
        
        // 1. Get a copy of the default allowed tags and attributes
        const customAllowList = {
            ...bootstrap.Popover.Default.allowList,
            // 2. Add 'iframe' and its necessary attributes to the list
            iframe: ['src', 'style', 'width', 'height', 'frameborder', 'allowfullscreen']
        };

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
            
            // --- EDIT MODAL SCRIPT ---
            const editUserModal = document.getElementById('editUserModal');
            editUserModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                
                const email = button.getAttribute('data-email');
                const fname = button.getAttribute('data-fname');
                const lname = button.getAttribute('data-lname');
                const dob = button.getAttribute('data-dob');
                const gender = button.getAttribute('data-gender');
                const hometown = button.getAttribute('data-hometown');

                const modal = this;
                modal.querySelector('#edit_original_email').value = email;
                modal.querySelector('#edit_email').value = email;
                modal.querySelector('#edit_first_name').value = fname;
                modal.querySelector('#edit_last_name').value = lname;
                modal.querySelector('#edit_dob').value = dob;
                modal.querySelector('#edit_gender').value = gender;
                modal.querySelector('#edit_hometown').value = hometown;
                modal.querySelector('#edit_password').value = ""; // Clear password field
            });
        });
    </script>

    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_accounts.php" method="POST">
                    <div class="modal-header admin-navbar text-white">
                        <h5 class="modal-title text-white" id="addUserModalLabel">Add New User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="col-md-12">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="col-md-4">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob">
                            </div>
                            <div class="col-md-4">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select" required>
                                    <option value="" selected disabled>Choose...</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="hometown" class="form-label">Hometown</label>
                                <input type="text" class="form-control" id="hometown" name="hometown">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_accounts.php" method="POST">
                    <input type="hidden" id="edit_original_email" name="original_email">
                    
                    <div class="modal-header admin-navbar text-white">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            </div>
                            <div class="col-md-12">
                                <label for="edit_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-12">
                                <label for="edit_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="edit_password" name="password" aria-describedby="passwordHelp">
                                <div id="passwordHelp" class="form-text">Leave blank to keep the current password.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="edit_dob" name="dob">
                            </div>
                            <div class="col-md-4">
                                <label for="edit_gender" class="form-label">Gender</label>
                                <select id="edit_gender" name="gender" class="form-select" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_hometown" class="form-label">Hometown</label>
                                <input type="text" class="form-control" id="edit_hometown" name="hometown">
                                </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>