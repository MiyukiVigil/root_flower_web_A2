<?php
session_start();
// require DB connection (must set $conn as PDO)
require_once 'connection.php';

// --- Access control: must be logged in as user ---
if (!isset($_SESSION['user_email']) || ($_SESSION['user_type'] ?? '') !== 'user') {
    header("Location: login.php");
    exit;
}

$currentEmail = $_SESSION['user_email'];

$feedback_message = '';
$feedback_type = 'success';
$errors = [];

// Ensure upload directories exist
$profileDir = __DIR__ . '/profile_images';
$resumeDir = __DIR__ . '/resume';
if (!is_dir($profileDir)) mkdir($profileDir, 0755, true);
if (!is_dir($resumeDir)) mkdir($resumeDir, 0755, true);

// --- Fetch current user data from DB ---
$stmt = $conn->prepare("SELECT email, first_name, last_name, dob, gender, hometown, profile_image FROM user_table WHERE email = ?");
$stmt->execute([$currentEmail]);
$current_user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current_user_data) {
    // Shouldn't happen unless DB inconsistency; log out user and redirect to login
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Helper: sanitize filename (keeps extension)
function safe_filename($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    // keep only alnum, dash, underscore for name
    $name = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
    return $name . ($ext ? '.' . $ext : '');
}

// --- Handle POST (update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and validate form inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $hometown = trim($_POST['hometown'] ?? '');

    // Basic validations
    if ($first_name === '' || !preg_match("/^[a-zA-Z ]+$/", $first_name)) {
        $errors[] = 'First name is required and must contain only letters and spaces.';
    }
    if ($last_name === '' || !preg_match("/^[a-zA-Z ]+$/", $last_name)) {
        $errors[] = 'Last name is required and must contain only letters and spaces.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($gender === '' || !in_array(strtolower($gender), ['male', 'female'])) {
        $errors[] = 'Please select a valid gender.';
    }
    if ($hometown === '') {
        $errors[] = 'Hometown is required.';
    }

    // If email changed, check duplicates in user_table
    $emailChanged = (strcasecmp($email, $current_user_data['email']) !== 0);
    if ($emailChanged) {
        // Update user_table email first
        $updateEmail = $conn->prepare("UPDATE user_table SET email = ? WHERE email = ?");
        $updateEmail->execute([$email, $current_user_data['email']]);

        // Then update account_table email
        $updateAcct = $conn->prepare("UPDATE account_table SET email = ? WHERE email = ?");
        $updateAcct->execute([$email, $current_user_data['email']]);

        // Update session
        $_SESSION['user_email'] = $email;
        $current_user_data['email'] = $email;
    }


    // --- Handle profile photo upload (optional) ---
    $newProfilePath = $current_user_data['profile_image']; // default keep existing
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $pf = $_FILES['profile_photo'];

        if ($pf['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading profile photo.';
        } else {
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($pf['type'], $allowedMimes)) {
                $errors[] = 'Profile photo must be JPG, JPEG, or PNG.';
            }
            if ($pf['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Profile photo must be 5MB or smaller.';
            }

            if (empty($errors)) {
                $safe = safe_filename($pf['name']);
                $uniq = uniqid('pf_') . '_' . $safe;
                $dest = $profileDir . '/' . $uniq;
                if (move_uploaded_file($pf['tmp_name'], $dest)) {
                    // Store relative path for DB (use forward slashes)
                    $newProfilePath = 'profile_images/' . $uniq;
                } else {
                    $errors[] = 'Failed to move uploaded profile photo.';
                }
            }
        }
    }

    // --- Handle resume upload (optional) ---
    $newResumePath = $current_user_data['resume'] ?? null;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
        $rf = $_FILES['resume'];

        if ($rf['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading resume.';
        } else {
            $allowedResume = ['application/pdf'];
            // Some browsers may send other mime types; check extension too
            $ext = strtolower(pathinfo($rf['name'], PATHINFO_EXTENSION));
            if (!in_array($rf['type'], $allowedResume) && $ext !== 'pdf') {
                $errors[] = 'Resume must be a PDF file.';
            }
            if ($rf['size'] > 7 * 1024 * 1024) {
                $errors[] = 'Resume must be 7MB or smaller.';
            }

            if (empty($errors)) {
                $safe = safe_filename($rf['name']);
                $uniq = uniqid('resume_') . '_' . $safe;
                $dest = $resumeDir . '/' . $uniq;
                if (move_uploaded_file($rf['tmp_name'], $dest)) {
                    $newResumePath = 'resume/' . $uniq;
                } else {
                    $errors[] = 'Failed to move uploaded resume.';
                }
            }
        }
    }

    // If no errors, commit updates
    if (empty($errors)) {
        try {
            // Begin transaction
            $conn->beginTransaction();

            // Update user_table
            $updateUser = $conn->prepare("
                UPDATE user_table
                SET first_name = ?, last_name = ?, gender = ?, hometown = ?, profile_image = ?
                WHERE email = ?
            ");
            $updateUser->execute([
                $first_name,
                $last_name,
                $gender,
                $hometown,
                $newProfilePath,
                $current_user_data['email']
            ]);

            // If resume column exists in your schema, update it.
            // Try to update resume if column exists (defensive)
            $columns = $conn->query("SHOW COLUMNS FROM user_table LIKE 'resume'")->fetch();
            if ($newResumePath && $columns) {
                $stmtResume = $conn->prepare("UPDATE user_table SET resume = ? WHERE email = ?");
                $stmtResume->execute([$newResumePath, $current_user_data['email']]);
            }

            // If email changed, update both tables
            if ($emailChanged) {
                // Update account_table email -> maintain FK relationship
                $updateAcct = $conn->prepare("UPDATE account_table SET email = ? WHERE email = ?");
                $updateAcct->execute([$email, $current_user_data['email']]);

                // Update user_table email too
                $updateEmail = $conn->prepare("UPDATE user_table SET email = ? WHERE email = ?");
                $updateEmail->execute([$email, $current_user_data['email']]);

                // Update session
                $_SESSION['user_email'] = $email;
                $current_user_data['email'] = $email;
            }

            // Commit transaction
            $conn->commit();

            // Refresh current_user_data for display
            $stmt = $conn->prepare("SELECT email, first_name, last_name, dob, gender, hometown, profile_image, resume FROM user_table WHERE email = ?");
            $stmt->execute([$_SESSION['user_email']]);
            $current_user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $feedback_message = 'Profile updated successfully.';
            $feedback_type = 'success';
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
            $feedback_type = 'danger';
        }
    } else {
        $feedback_type = 'danger';
    }
}

// Determine profile image to display - prefer uploaded path, fall back to gender placeholder
$displayProfileImage = './images/profile_pic/default.png';
if (!empty($current_user_data['profile_image']) && file_exists(__DIR__ . '/' . $current_user_data['profile_image'])) {
    $displayProfileImage = $current_user_data['profile_image'];
} elseif (isset($current_user_data['gender'])) {
    if (strtolower($current_user_data['gender']) === 'male') {
        $displayProfileImage = './images/profile_pic/boys.jpg';
    } elseif (strtolower($current_user_data['gender']) === 'female') {
        $displayProfileImage = './images/profile_pic/girl.png';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Your Profile</title>
    <meta name="author" content="Ivan">
    <meta name="keywords" content="User Profile, Update, Account">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="./style/style.css" rel="stylesheet">
    <link rel="icon" href="./images/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="update-profile-page">

<header>
    <?php include 'includes/navbar.inc'; ?>
</header>

<main class="container my-5">
    <div class="update-profile-card p-4 p-md-5 mx-auto rounded-4 shadow-lg">
        <div class="text-center mb-4">
            <h1 class="profile_name">Edit Your Profile</h1>
            <p class="text-muted">Keep your information up to date!</p>
        </div>

        <?php if ($feedback_message): ?>
            <div class="alert alert-<?= htmlspecialchars($feedback_type) ?> text-center">
                <i class="bi bi-info-circle-fill me-2"></i><?= htmlspecialchars($feedback_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="update_profile.php" method="POST" enctype="multipart/form-data" novalidate>
            <div class="row g-5 align-items-center">
                <!-- Profile Image and Resume Column -->
                <div class="col-lg-4 text-center">
                    <img src="<?= htmlspecialchars($displayProfileImage) ?>" class="profile-image mb-3" alt="Profile Picture" style="max-width:220px;">
                    <h4 class="profile_name"><?= htmlspecialchars($current_user_data['first_name'] . ' ' . $current_user_data['last_name']) ?></h4>
                    <p class="text-muted profile_id"><?= htmlspecialchars($current_user_data['email']) ?></p>

                </div>

                <!-- Form Fields Column -->
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label fw-bold">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($current_user_data['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label fw-bold">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($current_user_data['last_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($current_user_data['email'] ?? '') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label fw-bold">Gender</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="Male" <?= (strtolower($current_user_data['gender'] ?? '') == 'male') ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= (strtolower($current_user_data['gender'] ?? '') == 'female') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hometown" class="form-label fw-bold">Hometown</label>
                            <input type="text" class="form-control" id="hometown" name="hometown" value="<?= htmlspecialchars($current_user_data['hometown'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <label for="profile_photo" class="form-label fw-bold">Profile Photo (jpg, jpeg, png | ≤ 5MB)</label>
                            <input type="file" class="form-control file-input-fix" id="profile_photo" name="profile_photo" accept=".jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="resume" class="form-label fw-bold">Upload Resume (PDF | ≤ 7MB)</label>
                            <input type="file" class="form-control file-input-fix" id="resume" name="resume" accept=".pdf">
                            <?php if (!empty($current_user_data['resume'])): ?>
                                <p class="small mt-2">Current resume: <a href="<?= htmlspecialchars($current_user_data['resume']) ?>" target="_blank">View</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr class="mt-4 mb-3"> <div class="d-flex justify-content-end gap-2"> <a href="main_menu.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update Profile</button>
                    </div>
                </div>
        </form>

    </div>
</main>

<?php include 'includes/footer.inc'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
