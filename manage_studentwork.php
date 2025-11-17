<?php
require_once 'connection.php';
session_start();

// Task 4.3 Requirement: Admin Access Check
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Disable caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$page_title = "Manage Student Work";
$message = '';
$error = '';

// Define the upload directory
$upload_dir = "uploads/student_work/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// --- HANDLE FILE UPLOAD (Helper Function) ---
function handleFileUpload($file_key, $existing_image = '') {
    global $error, $upload_dir;

    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$file_key];
        $file_name = basename($file['name']);
        $target_file = $upload_dir . uniqid() . '-' . $file_name;
        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file type (using PDF's profile image rules as a guide [cite: 117])
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (!in_array($image_type, $allowed_types)) {
            $error = "Error: Only JPG, JPEG, & PNG files are allowed.";
            return false;
        }

        // Check file size (using PDF's 5MB rule as a guide [cite: 117])
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            $error = "Error: File is too large. Maximum size is 5MB.";
            return false;
        }

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return $target_file; // Return the new file path
        } else {
            $error = "Error: There was an issue uploading the file.";
            return false;
        }
    } elseif (!empty($existing_image)) {
        return $existing_image; // Keep the old image
    }
    
    return false; // No file uploaded or error
}

// --- HANDLE DELETE ---
if (isset($_GET['delete_id'])) {
    try {
        $id = $_GET['delete_id'];
        
        // Optional: Delete the image file from the server
        $stmt_find = $conn->prepare("SELECT workshop_image FROM studentwork_table WHERE id = ?");
        $stmt_find->execute([$id]);
        $image_path = $stmt_find->fetchColumn();
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }

        $stmt = $conn->prepare("DELETE FROM studentwork_table WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Student work entry deleted successfully.";
    } catch (PDOException $e) {
        $error = "Error deleting entry: " . $e->getMessage();
    }
}

// --- HANDLE STATUS CHANGE (Approve/Reject) ---
if (isset($_POST['update_status'])) {
    try {
        $id = $_POST['id'];
        $new_status = isset($_POST['approve']) ? 'approved' : 'rejected';
        
        $stmt = $conn->prepare("UPDATE studentwork_table SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        $message = "Status updated to '$new_status'.";
    } catch (PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// --- HANDLE ADD WORK ---
if (isset($_POST['add_work'])) {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $title = $_POST['workshop_title'];
    $desc = $_POST['description'];

    $image_path = handleFileUpload('workshop_image');

    if ($image_path) {
        try {
            $sql = "INSERT INTO studentwork_table (first_name, last_name, workshop_title, workshop_image, description, status) 
                    VALUES (?, ?, ?, ?, ?, 'pending')";
            $conn->prepare($sql)->execute([$fname, $lname, $title, $image_path, $desc]);
            $message = "New student work added successfully. It is pending approval.";
        } catch (PDOException $e) {
            $error = "Error adding work: " . $e->getMessage();
        }
    } elseif (!$error) {
        $error = "An image is required to add new work.";
    }
}

// --- HANDLE EDIT WORK ---
if (isset($_POST['edit_work'])) {
    $id = $_POST['id'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $title = $_POST['workshop_title'];
    $desc = $_POST['description'];
    $existing_image = $_POST['existing_image_path'];

    $image_path = handleFileUpload('workshop_image', $existing_image);

    if ($image_path) {
        try {
            $sql = "UPDATE studentwork_table SET first_name = ?, last_name = ?, workshop_title = ?, workshop_image = ?, description = ? WHERE id = ?";
            $conn->prepare($sql)->execute([$fname, $lname, $title, $image_path, $desc, $id]);
            
            // Delete old image if a new one was uploaded
            if ($image_path !== $existing_image && file_exists($existing_image)) {
                unlink($existing_image);
            }
            $message = "Student work updated successfully.";
        } catch (PDOException $e) {
            $error = "Error updating work: " . $e->getMessage();
        }
    }
}

// --- FETCH ALL DATA ---
$stmt = $conn->prepare("SELECT * FROM studentwork_table ORDER BY id DESC");
$stmt->execute();
$works = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <h1 class="display-4 fw-bold text-white">Manage Student Work</h1>
                <p class="lead">Add, edit, delete, and approve student-submitted work.</p>
            </div>
    
            <div class="container admin-card-container">
                <div class="row justify-content-center">
                    <div class="col-lg-12"> <div class="card h-100 shadow-lg admin-card" style="border: none;">
                            <div class="card-body p-4 p-md-5">
                                
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title" style="font-size: 1.5rem; margin-bottom: 0;">All Submissions (<?php echo count($works); ?>)</h5>
                                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addWorkModal">
                                        <i class="fas fa-plus me-2"></i> Add New Work
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
                                                <th>Image</th>
                                                <th>Student</th>
                                                <th>Workshop</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th class="text-end" style="min-width: 250px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($works)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No student work found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($works as $work): 
                                                    // Handle image path logic
                                                    $image_path = htmlspecialchars($work['workshop_image']);
                                                    // Check if it's a legacy path (like 'work1.jpg') or new path
                                                    if (!str_starts_with($image_path, 'uploads/')) {
                                                        // Fallback for original dummy data
                                                        $image_path = "images/student_work/" . $image_path;
                                                    }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <img src="<?php echo $image_path; ?>" alt="Workshop Image" class="profile-thumb">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($work['first_name'] . ' ' . $work['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($work['workshop_title']); ?></td>
                                                    <td><?php echo htmlspecialchars(substr($work['description'], 0, 50)) . '...'; ?></td>
                                                    <td>
                                                        <?php
                                                            $status = htmlspecialchars($work['status']);
                                                            $badge_class = 'text-bg-secondary';
                                                            if ($status === 'approved') $badge_class = 'text-bg-success';
                                                            if ($status === 'pending') $badge_class = 'text-bg-warning';
                                                            if ($status === 'rejected') $badge_class = 'text-bg-danger';
                                                        ?>
                                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                                    </td>
                                                    <td class="text-end">
                                                        <form action="manage_studentwork.php" method="POST" class="d-inline-block">
                                                            <input type="hidden" name="id" value="<?php echo $work['id']; ?>">
                                                            
                                                            <button type="submit" name="approve" class="btn btn-success btn-sm" title="Approve" 
                                                                <?php if ($work['status'] === 'approved') echo 'disabled'; ?>>
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            
                                                            <button type="submit" name="reject" class="btn btn-warning btn-sm" title="Reject" 
                                                                <?php if ($work['status'] === 'rejected') echo 'disabled'; ?>>
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                            
                                                            <input type="hidden" name="update_status" value="1">
                                                        </form>
                                                        
                                                        <button class="btn btn-primary btn-sm edit-btn" 
                                                                title="Edit Work"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editWorkModal"
                                                                data-id="<?php echo $work['id']; ?>"
                                                                data-fname="<?php echo htmlspecialchars($work['first_name']); ?>"
                                                                data-lname="<?php echo htmlspecialchars($work['last_name']); ?>"
                                                                data-title="<?php echo htmlspecialchars($work['workshop_title']); ?>"
                                                                data-desc="<?php echo htmlspecialchars($work['description']); ?>"
                                                                data-image="<?php echo htmlspecialchars($work['workshop_image']); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <a href="manage_studentwork.php?delete_id=<?php echo $work['id']; ?>" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Delete Work"
                                                        onclick="return confirm('Are you sure you want to delete this entry? This action cannot be undone.');">
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
    
    <div class="modal fade" id="addWorkModal" tabindex="-1" aria-labelledby="addWorkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_studentwork.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header admin-navbar text-white">
                        <h5 class="modal-title" id="addWorkModalLabel">Add New Student Work</h5>
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
                                <label for="workshop_title" class="form-label">Workshop Title</label>
                                <input type="text" class="form-control" id="workshop_title" name="workshop_title" required>
                            </div>
                            <div class="col-md-12">
                                <label for="workshop_image" class="form-label">Workshop Image</label>
                                <input type="file" class="form-control" id="workshop_image" name="workshop_image" accept="image/png, image/jpeg, image/jpg" required>
                            </div>
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_work" class="btn btn-primary">Add Work</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editWorkModal" tabindex="-1" aria-labelledby="editWorkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_studentwork.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id" name="id">
                    <input type="hidden" id="edit_existing_image_path" name="existing_image_path">
                    
                    <div class="modal-header admin-navbar text-white">
                        <h5 class="modal-title" id="editWorkModalLabel">Edit Student Work</h5>
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
                                <label for="edit_workshop_title" class="form-label">Workshop Title</label>
                                <input type="text" class="form-control" id="edit_workshop_title" name="workshop_title" required>
                            </div>
                            <div class="col-md-12">
                                <label for="edit_workshop_image" class="form-label">New Workshop Image</label>
                                <input type="file" class="form-control" id="edit_workshop_image" name="workshop_image" accept="image/png, image/jpeg, image/jpg">
                                <div class="form-text">Leave blank to keep the current image.</div>
                            </div>
                            <div class="col-md-12">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="4" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_work" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- EDIT MODAL SCRIPT ---
            const editWorkModal = document.getElementById('editWorkModal');
            editWorkModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                
                const id = button.getAttribute('data-id');
                const fname = button.getAttribute('data-fname');
                const lname = button.getAttribute('data-lname');
                const title = button.getAttribute('data-title');
                const desc = button.getAttribute('data-desc');
                const image = button.getAttribute('data-image');

                const modal = this;
                modal.querySelector('#edit_id').value = id;
                modal.querySelector('#edit_first_name').value = fname;
                modal.querySelector('#edit_last_name').value = lname;
                modal.querySelector('#edit_workshop_title').value = title;
                modal.querySelector('#edit_description').value = desc;
                modal.querySelector('#edit_existing_image_path').value = image;
            });
        });
    </script>
</body>
</html>