<?php
session_start();
require_once 'connection.php'; // Make sure $conn is your PDO connection

// Access control: Only logged-in users of type 'user'
if (!isset($_SESSION['user_email']) || ($_SESSION['user_type'] ?? '') !== 'user') {
    echo '<div class="text-center mt-5">';
    echo '<h2>Access Denied</h2>';
    echo '<p>You must be logged in as a user to register for a workshop. <a href="login.php">Login here</a>.</p>';
    echo '</div>';
    exit;
}

$errors = [];
$old = [];

// --- Pre-fill user data from database ---
$current_email = $_SESSION['user_email'];
$current_user_data = [];

$stmt = $conn->prepare("SELECT first_name, last_name, email FROM user_table WHERE email = ?");
$stmt->execute([$current_email]);
$current_user_data = $stmt->fetch(PDO::FETCH_ASSOC);

$first_name = $current_user_data['first_name'] ?? '';
$last_name = $current_user_data['last_name'] ?? '';
$email = $current_user_data['email'] ?? '';

// --- Pre-fill workshop title from URL parameter ---
$workshop_title = isset($_GET['title']) ? htmlspecialchars(urldecode($_GET['title'])) : '';

// --- Check for errors or old input in session ---
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    $old = $_SESSION['old'] ?? [];
    unset($_SESSION['errors'], $_SESSION['old']);
}
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workshop Registration</title>
    <meta name="author" content="Ivan">
    <meta name="keywords" content="Workshop, Registration, Flowers">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="./style/style.css" rel="stylesheet">
    <link rel="icon" href="./images/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="reg-body">
    <header>
        <?php include 'includes/navbar.inc'; ?>
    </header>

    <div class="reg-wrapper">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-7">
                    <div class="card register-card">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="text-center mb-4">🌸 Register for a Workshop</h2>
                            
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success text-center">
                                    <?= htmlspecialchars($success_message) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="process_workshop_reg.php" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($old['first_name'] ?? $first_name) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($old['last_name'] ?? $last_name) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['email'] ?? $email) ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback d-block"><?= $errors['email'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($old['contact_number'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Workshop Title</label>
                                    <input type="text" name="workshop_title" class="form-control" placeholder="e.g., Beginner's Bouquet Making" value="<?= htmlspecialchars($old['workshop_title'] ?? $workshop_title) ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Preferred Date & Time</label>
                                    <input type="datetime-local" name="workshop_datetime" class="form-control" value="<?= htmlspecialchars($old['workshop_datetime'] ?? '') ?>" required>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">Register Now</button>
                                </div>
                            </form>
                             <div class="mt-3 text-center">
                                <a href="main_menu.php" class="text-decoration-none"><i class="bi bi-arrow-left-circle"></i> Back to Main Menu</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.inc' ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

