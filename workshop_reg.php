<?php
    session_start();

    // If the user isn't logged in, redirect them.
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }

    // Tells the browser and any proxies not to cache the page.
    header("Cache-Control: no-cache, no-store, must-revalidate");
    // For older HTTP/1.0 clients.
    header("Pragma: no-cache");
    // For proxies and old browsers, sets the expiration date to the past.
    header("Expires: 0");

    // --- Pre-fill user data ---
    $users_file = __DIR__ . '/../../data/User/user.txt';
    $current_user_data = null;
    $first_name = '';
    $last_name = '';
    $email = $_SESSION['user']; // The email is the session key

    function parse_user_line($line) {
        $user_data = [];
        $parts = explode('|', $line);
        foreach ($parts as $part) {
            list($key, $value) = explode(':', $part, 2);
            $user_data[trim($key)] = $value;
        }
        return $user_data;
    }

    if (file_exists($users_file)) {
        $lines = file($users_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $user_data = parse_user_line($line);
            if (isset($user_data['Email']) && $user_data['Email'] === $email) {
                $first_name = $user_data['First Name'] ?? '';
                $last_name = $user_data['Last Name'] ?? '';
                break;
            }
        }
    }
    // --- End of pre-fill logic ---

    // --- Pre-fill workshop title from URL parameter ---
    $workshop_title = '';
    if (isset($_GET['title'])) {
        // Sanitize the title received from the URL
        $workshop_title = htmlspecialchars(urldecode($_GET['title']));
    }
    // --- End of title pre-fill ---

    // Retrieve errors and old input from session if they exist
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    unset($_SESSION['errors'], $_SESSION['old']); // Clear them after use
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
                            
                            <?php if (isset($_SESSION['success_message'])): ?>
                                <div class="alert alert-success text-center">
                                    <?= $_SESSION['success_message'] ?>
                                </div>
                                <?php unset($_SESSION['success_message']); ?>
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

