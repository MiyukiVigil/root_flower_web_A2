<?php
require_once 'connection.php';
session_start();

require_once 'mail_function.php';

// Task 4.4 Requirement: Admin Access Check
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Disable caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$page_title = "Manage Workshop Registrations";
$message = '';
$error = '';

// --- HANDLE DELETE ---
if (isset($_GET['delete_id'])) {
    try {
        $id = $_GET['delete_id'];
        $stmt = $conn->prepare("DELETE FROM workshop_table WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Registration entry deleted successfully.";
    } catch (PDOException $e) {
        $error = "Error deleting entry: " . $e->getMessage();
    }
}

// --- HANDLE STATUS CHANGE (Approve/Reject) ---
if (isset($_POST['update_status'])) {
    try {
        $id = $_POST['id'];
        $new_status = isset($_POST['approve']) ? 'approved' : 'rejected';
        
        $stmt = $conn->prepare("UPDATE workshop_table SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        $message = "Registration status updated to '$new_status'.";
    } catch (PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// --- HANDLE ADD REGISTRATION ---
if (isset($_POST['add_reg'])) {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $title = $_POST['workshop_title'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $contact = $_POST['contact_number'];

    try {
        // Admin-added registrations are 'approved' by default
        $sql = "INSERT INTO workshop_table (first_name, last_name, email, workshop_title, date, time, contact_number, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')";
        $conn->prepare($sql)->execute([$fname, $lname, $email, $title, $date, $time, $contact]);
        $message = "New registration added successfully.";
    } catch (PDOException $e) {
        $error = "Error adding registration: " . $e->getMessage();
    }
}

if (isset($_POST['update_status'])) {
    try {
        $id = $_POST['id'];
        $new_status = isset($_POST['approve']) ? 'approved' : 'rejected';
        
        $stmt = $conn->prepare("UPDATE workshop_table SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        
        // --- MODIFIED: Get user info for email ---
        $stmt_user = $conn->prepare("SELECT email, first_name, workshop_title FROM workshop_table WHERE id = ?");
        $stmt_user->execute([$id]);
        $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

        if ($user_info) {
            // --- MODIFIED: Call the new function from your file ---
            $email_sent = send_workshop_notification(
                $user_info['email'], 
                $user_info['first_name'], 
                $user_info['workshop_title'], 
                $new_status
            );
            
            if ($email_sent) {
                $message = "Status updated to '$new_status' and a notification email was sent.";
            } else {
                // Add a more specific error
                $error = "Status updated, but the notification email could not be sent. Check your .env credentials.";
            }
        } else {
            $message = "Status updated, but user info could not be found to send email.";
        }

    } catch (PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// --- HANDLE EDIT REGISTRATION ---
if (isset($_POST['edit_reg'])) {
    $id = $_POST['id'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $title = $_POST['workshop_title'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $contact = $_POST['contact_number'];

    try {
        $sql = "UPDATE workshop_table SET first_name = ?, last_name = ?, email = ?, workshop_title = ?, date = ?, time = ?, contact_number = ? WHERE id = ?";
        $conn->prepare($sql)->execute([$fname, $lname, $email, $title, $date, $time, $contact, $id]);
        $message = "Registration updated successfully.";
    } catch (PDOException $e) {
        $error = "Error updating registration: " . $e->getMessage();
    }
}

// --- FETCH ALL DATA ---
$stmt = $conn->prepare("SELECT * FROM workshop_table ORDER BY date DESC, time DESC");
$stmt->execute();
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<body class="admin-user-manager-page"> <header>
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
                <h1 class="display-4 fw-bold text-white">Manage Workshop Registrations</h1>
                <p class="lead">Approve, edit, and manage all workshop signups.</p>
            </div>
    
            <div class="container admin-card-container">
                <div class="row justify-content-center">
                    <div class="col-lg-12"> 
                        <div class="card h-100 shadow-lg admin-card" style="border: none;">
                            <div class="card-body p-4 p-md-5">
                                
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title" style="font-size: 1.5rem; margin-bottom: 0;">All Registrations (<?php echo count($registrations); ?>)</h5>
                                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addRegModal">
                                        <i class="fas fa-plus me-2"></i> Add Registration
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
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Workshop</th>
                                                <th>Date & Time</th>
                                                <th>Contact</th>
                                                <th>Status</th>
                                                <th class="text-end" style="min-width: 250px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($registrations)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No registrations found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($registrations as $reg): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($reg['workshop_title']); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($reg['date']); ?><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars(date('h:i A', strtotime($reg['time']))); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($reg['contact_number']); ?></td>
                                                    <td>
                                                        <?php
                                                            $status = htmlspecialchars($reg['status']);
                                                            $badge_class = 'text-bg-secondary';
                                                            if ($status === 'approved') $badge_class = 'text-bg-success';
                                                            if ($status === 'pending') $badge_class = 'text-bg-warning';
                                                            if ($status === 'rejected') $badge_class = 'text-bg-danger';
                                                        ?>
                                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                                    </td>
                                                    <td class="text-end">
                                                        <form action="manage_workshop_reg.php" method="POST" class="d-inline-block">
                                                            <input type="hidden" name="id" value="<?php echo $reg['id']; ?>">
                                                            
                                                            <button type="submit" name="approve" class="btn btn-success btn-sm" title="Approve" 
                                                                <?php if ($reg['status'] === 'approved') echo 'disabled'; ?>>
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            
                                                            <button type="submit" name="reject" class="btn btn-warning btn-sm" title="Reject" 
                                                                <?php if ($reg['status'] === 'rejected') echo 'disabled'; ?>>
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                            
                                                            <input type="hidden" name="update_status" value="1">
                                                        </form>
                                                        
                                                        <button class="btn btn-primary btn-sm edit-btn" 
                                                                title="Edit Registration"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editRegModal"
                                                                data-id="<?php echo $reg['id']; ?>"
                                                                data-fname="<?php echo htmlspecialchars($reg['first_name']); ?>"
                                                                data-lname="<?php echo htmlspecialchars($reg['last_name']); ?>"
                                                                data-email="<?php echo htmlspecialchars($reg['email']); ?>"
                                                                data-title="<?php echo htmlspecialchars($reg['workshop_title']); ?>"
                                                                data-date="<?php echo htmlspecialchars($reg['date']); ?>"
                                                                data-time="<?php echo htmlspecialchars($reg['time']); ?>"
                                                                data-contact="<?php echo htmlspecialchars($reg['contact_number']); ?>"
                                                                >
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <a href="manage_workshop_reg.php?delete_id=<?php echo $reg['id']; ?>" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Delete Registration"
                                                        onclick="return confirm('Are you sure you want to delete this registration?');">
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
    
    <div class="modal fade" id="addRegModal" tabindex="-1" aria-labelledby="addRegModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_workshop_reg.php" method="POST">
                    <div class="modal-header admin-navbar text-white">
                        <h5 class="modal-title" id="addRegModalLabel">Add New Registration</h5>
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
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-12">
                                <label for="workshop_title" class="form-label">Workshop Title</label>
                                <select id="workshop_title" name="workshop_title" class="form-select" required>
                                    <option value="" selected disabled>Choose...</option>
                                    <option value="Hobby Class">Hobby Class</option>
                                    <option value="Hand-tied Bouquet Course">Hand-tied Bouquet Course</option>
                                    <option value="Florist To Be 1">Florist To Be 1</option>
                                    <option value="Florist To Be 2">Florist To Be 2</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="col-md-4">
                                <label for="time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="time" name="time" required>
                            </div>
                            <div class="col-md-4">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="contact_number" name="contact_number">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_reg" class="btn btn-primary">Add Registration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editRegModal" tabindex="-1" aria-labelledby="editRegModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_workshop_reg.php" method="POST">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="modal-header admin-navbar text-white">
                        <h5 class="modal-title" id="editRegModalLabel">Edit Registration</h5>
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
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-12">
                                <label for="edit_workshop_title" class="form-label">Workshop Title</label>
                                <select id="edit_workshop_title" name="workshop_title" class="form-select" required>
                                    <option value="Hobby Class">Hobby Class</option>
                                    <option value="Hand-tied Bouquet Course">Hand-tied Bouquet Course</option>
                                    <option value="Florist To Be 1">Florist To Be 1</option>
                                    <option value="Florist To Be 2">Florist To Be 2</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="edit_date" name="date" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="edit_time" name="time" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_contact_number" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="edit_contact_number" name="contact_number">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_reg" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- EDIT MODAL SCRIPT ---
            const editRegModal = document.getElementById('editRegModal');
            editRegModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                
                const id = button.getAttribute('data-id');
                const fname = button.getAttribute('data-fname');
                const lname = button.getAttribute('data-lname');
                const email = button.getAttribute('data-email');
                const title = button.getAttribute('data-title');
                const date = button.getAttribute('data-date');
                const time = button.getAttribute('data-time');
                const contact = button.getAttribute('data-contact');

                const modal = this;
                modal.querySelector('#edit_id').value = id;
                modal.querySelector('#edit_first_name').value = fname;
                modal.querySelector('#edit_last_name').value = lname;
                modal.querySelector('#edit_email').value = email;
                modal.querySelector('#edit_workshop_title').value = title;
                modal.querySelector('#edit_date').value = date;
                modal.querySelector('#edit_time').value = time;
                modal.querySelector('#edit_contact_number').value = contact;
            });
        });
    </script>
</body>
</html>