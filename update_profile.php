<?php
    session_start();

    // Check if the user is logged in. If not, redirect to the login page.
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

    $users_file = __DIR__ . "/../../data/User/user.txt";
    $current_user_data = null;
    $feedback_message = '';
    $feedback_type = 'success';

    // Function to parse a line from specific user.txt format
    function parse_user_line($line) {
        $user_data = [];
        $parts = explode('|', $line);
        foreach ($parts as $part) {
            list($key, $value) = explode(':', $part, 2);
            $user_data[trim($key)] = $value;
        }
        return $user_data;
    }
    
    // Function to find user data by their email address
    function find_user_by_email($file_path, $email) {
        if (!file_exists($file_path)) return null;
        
        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $user_data = parse_user_line($line);
            if (isset($user_data['Email']) && $user_data['Email'] === $email) {
                return $user_data;
            }
        }
        return null;
    }

    // --- HANDLE FORM SUBMISSION (UPDATE LOGIC) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $logged_in_email = $_SESSION['user']; // Use the correct session variable
        
        // Sanitize and retrieve form data
        $updated_first_name = htmlspecialchars(trim($_POST['first_name']));
        $updated_last_name = htmlspecialchars(trim($_POST['last_name']));
        $updated_email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $updated_gender = htmlspecialchars(trim($_POST['gender']));
        $updated_hometown = htmlspecialchars(trim($_POST['hometown']));

        $lines = file($users_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $updated_lines = [];
        $user_found = false;

        foreach ($lines as $line) {
            $user_data = parse_user_line($line);
            if (isset($user_data['Email']) && $user_data['Email'] === $logged_in_email) {
                $user_found = true;
                $dob = $user_data['DOB'] ?? '';
                $password = $user_data['Password'] ?? '';

                $new_line_parts = [
                    "First Name:" . $updated_first_name,
                    "Last Name:" . $updated_last_name,
                    "DOB:" . $dob,
                    "Gender:" . $updated_gender,
                    "Email:" . $updated_email,
                    "Hometown:" . $updated_hometown,
                    "Password:" . $password
                ];
                $updated_lines[] = implode('|', $new_line_parts);
                
                // If the email was changed, update the session variable
                $_SESSION['user'] = $updated_email;

            } else {
                $updated_lines[] = $line;
            }
        }

        if ($user_found) {
            file_put_contents($users_file, implode("\n", $updated_lines) . "\n");
            $feedback_message = 'Profile updated successfully!';
            $feedback_type = 'success';
        } else {
            $feedback_message = 'Error: Could not find your user record to update.';
            $feedback_type = 'danger';
        }
    }

    // --- FETCH CURRENT USER DATA TO DISPLAY (READ LOGIC) ---
    // Use the session variable set by login.php
    $current_user_data = find_user_by_email($users_file, $_SESSION['user']);

    // Determine profile image based on gender
    if ($current_user_data && isset($current_user_data['Gender'])) {
        if (strtolower($current_user_data['Gender']) == 'male') {
            $profile_image = './images/profile_pic/boys.jpg';
        } elseif (strtolower($current_user_data['Gender']) == 'female') {
            $profile_image = './images/profile_pic/girl.png';
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
            
            <?php if (!$current_user_data): ?>
                <div class="text-center">
                    <h2 class="text-danger">Error Loading Profile</h2>
                    <p class="lead">Could not find your profile data. Please ensure you are logged in.</p>
                    <a href="login.php" class="btn btn-primary mt-3">Go to Login</a>
                </div>
            <?php else: ?>

                <div class="text-center mb-4">
                    <h1 class="profile_name">Edit Your Profile</h1>
                    <p class="text-muted">Keep your information up to date!</p>
                </div>

                <?php if ($feedback_message): ?>
                    <div class="alert alert-<?= $feedback_type ?> text-center">
                        <i class="bi bi-info-circle-fill me-2"></i><?= $feedback_message ?>
                    </div>
                <?php endif; ?>

                <form action="update_profile.php" method="POST" novalidate>
                    <div class="row g-5 align-items-center">
                        <!-- Profile Image Column -->
                        <div class="col-lg-4 text-center">
                            <img src="<?= htmlspecialchars($profile_image) ?>" class="profile-image mb-3" alt="Profile Picture">
                            <h4 class="profile_name"><?= htmlspecialchars($current_user_data['First Name'] . ' ' . $current_user_data['Last Name']) ?></h4>
                            <p class="text-muted profile_id"><?= htmlspecialchars($current_user_data['Email']) ?></p>
                        </div>

                        <!-- Form Fields Column -->
                        <div class="col-lg-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label fw-bold">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($current_user_data['First Name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label fw-bold">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($current_user_data['Last Name']) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($current_user_data['Email']) ?>" required>
                            </div>
                             <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label fw-bold">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="Male" <?= (strtolower($current_user_data['Gender']) == 'male') ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= (strtolower($current_user_data['Gender']) == 'female') ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hometown" class="form-label fw-bold">Hometown</label>
                                    <input type="text" class="form-control" id="hometown" name="hometown" value="<?= htmlspecialchars($current_user_data['Hometown']) ?>" required>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="main_menu.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update Profile</button>
                            </div>
                        </div>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.inc'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>