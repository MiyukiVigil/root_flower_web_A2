<?php
    session_start();
    $errors  = $_SESSION['errors'] ?? [];
    $old     = $_SESSION['old'] ?? [];
    $success = $_SESSION['success'] ?? '';
    unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success']);

    // Check if the user is already logged in by seeing if the 'user' session variable exists
    if (isset($_SESSION['user'])) {
        // If they are logged in, redirect them to the main menu page
        header("Location: main_menu.php");
        exit; // IMPORTANT: Stop the script from running further
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <meta name="author" content="Ivan">
    <meta name="keywords" content="Register">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
    <link href="./style/style.css" rel="stylesheet">
    <link rel="icon" href="./images/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="reg-body">
    <header>
            <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm py-2">
                <div class="container">
                    <a class="navbar-brand" href="index.php">
                        <img src="images/logo.svg" alt="Root Flowers Logo" class="navbar-logo">
                        <span class="brand-logo-text ms-2">Root Flowers</span>
                    </a>
                </div>
            </nav>
    </header>

    <div class="reg-wrapper">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                
                <div class="col-lg-6 register-info text-center text-lg-start mb-5 mb-lg-0">
                    <h1 class="display-4 text-white fw-bold">Create an account</h1>
                    <p class="lead text-white-75 mt-3">
                        Create an account to exclusive access features in this website such as workshop registration, access our students' work and more!
                    </p>
                    <img src="./images/logo.svg" alt="RootFlowers Logo" class="register-info-logo mt-4">
                </div>

                <div class="col-lg-6">
                    <div class="card register-card">
                        <div class="card-body p-4">
                            <h2 class="text-center mb-4">🌸 Register New Account</h2>

                            <?php if ($success): ?>
                                <div class="alert alert-success"><?= $success ?></div>
                            <?php endif; ?>

                            <form action="process_register.php" method="POST" novalidate>
                                
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" 
                                           name="first_name" 
                                           class="form-control <?= !empty($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" 
                                           required>
                                    <?php if (!empty($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" 
                                           name="last_name" 
                                           class="form-control <?= !empty($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" 
                                           required>
                                    <?php if (!empty($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" 
                                           name="dob" 
                                           class="form-control <?= !empty($errors['dob']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['dob'] ?? '') ?>" 
                                           required>
                                    <?php if (!empty($errors['dob'])): ?>
                                        <div class="invalid-feedback"><?= $errors['dob'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" 
                                            class="form-select <?= !empty($errors['gender']) ? 'is-invalid' : '' ?>" 
                                            required>
                                        <option value="Female" <?= (isset($old['gender']) && $old['gender']=="Female") ? "selected" : "" ?>>Female</option>
                                        <option value="Male" <?= (isset($old['gender']) && $old['gender']=="Male") ? "selected" : "" ?>>Male</option>
                                    </select>
                                    <?php if (!empty($errors['gender'])): ?>
                                        <div class="invalid-feedback"><?= $errors['gender'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="text" 
                                           name="email" 
                                           class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['email'] ?? '') ?>" 
                                           required>
                                    <?php if (!empty($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Hometown</label>
                                    <input type="text" 
                                           name="hometown" 
                                           class="form-control <?= !empty($errors['hometown']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($old['hometown'] ?? '') ?>" 
                                           required>
                                    <?php if (!empty($errors['hometown'])): ?>
                                        <div class="invalid-feedback"><?= $errors['hometown'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password
                                        <i class="bi bi-info-circle ms-2"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-title="Password must be at least 8 characters long and include a number and a letter.">
                                        </i>
                                    </label>
                                    <div class="password-wrapper">
                                        <input type="password"
                                            class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                                            id="password"
                                            name="password"
                                            required
                                            aria-describedby="passwordHelp">
                                        <i class="bi bi-eye-slash toggle-password-icon"></i>
                                    </div>
                                    <div id="password-strength-container" class="mt-1" style="height: 5px;">
                                        <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%; height: 5px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div id="password-strength-text" class="form-text"></div>
                                    <?php if (!empty($errors['password'])): ?>
                                        <div class="invalid-feedback" id="passwordHelp"><?= $errors['password'] ?></div>
                                    <?php endif; ?>
                                </div>


                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <i class="bi bi-eye-slash toggle-password-icon" id="toggleConfirmPassword"></i>
                                    </div>
                                    <?php if (!empty($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                    <button type="submit" class="btn btn-primary">Register</button>
                                </div>
                            </form>

                            <div class="mt-3 text-center">
                                <a href="index.php" class="text-decoration-none"><i class="bi bi-house-door-fill"></i> Back to Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        
            // Select all toggle icons on the page
            const toggleIcons = document.querySelectorAll('.toggle-password-icon');

            toggleIcons.forEach(icon => {
                icon.addEventListener('click', function () {
                // The input field is the element right before the icon
                const passwordInput = this.previousElementSibling;
                
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle the icon's class
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
                });
            });
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');

            /**
             * Calculates the strength score of a password.
             * Score is based on: length, presence of lower/upper case letters, numbers, and symbols.
             * @param {string} password The password string.
             * @returns {number} A score from 0 to 4.
             */
            function calculateStrength(password) {
                let score = 0;
                
                if (password.length >= 8) { // Good length
                    score++;
                }
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) { // Mixed case
                    score++;
                } else if (/[a-zA-Z]/.test(password)) { // Just letters
                    score += 0.5;
                }
                if (/\d/.test(password)) { // Numbers
                    score++;
                }
                if (/[^a-zA-Z0-9\s]/.test(password)) { // Symbols
                    score++;
                }
                
                return Math.floor(Math.min(score, 4)); // Max score is 4 for easy percentage conversion
            }

            function updateStrengthIndicator() {
                const password = passwordInput.value;
                const score = calculateStrength(password);
                let width = 0;
                let colorClass = 'bg-secondary'; // Default/Empty

                if (password.length > 0) {
                    switch (score) {
                        case 0:
                            width = 25;
                            colorClass = 'bg-danger';
                            strengthText.textContent = 'Weak';
                            break;
                        case 1:
                            width = 25;
                            colorClass = 'bg-danger';
                            strengthText.textContent = 'Very Poor';
                            break;
                        case 2:
                            width = 50;
                            colorClass = 'bg-warning';
                            strengthText.textContent = 'Fair';
                            break;
                        case 3:
                            width = 75;
                            colorClass = 'bg-primary';
                            strengthText.textContent = 'Good';
                            break;
                        case 4:
                            width = 100;
                            colorClass = 'bg-success';
                            strengthText.textContent = 'Excellent';
                            break;
                        default:
                            width = 0;
                            colorClass = 'bg-secondary';
                            strengthText.textContent = '';
                            break;
                    }
                } else {
                    width = 0;
                    strengthText.textContent = '';
                    colorClass = 'bg-secondary';
                }

                // Update the bar's appearance
                strengthBar.style.width = width + '%';
                strengthBar.setAttribute('aria-valuenow', width);

                // Remove old color classes and add the new one
                strengthBar.className = 'progress-bar';
                strengthBar.classList.add(colorClass);

                // Add 'is-invalid' class if password is present but strength is too low (e.g., Weak)
                if (password.length > 0 && score < 2) {
                    passwordInput.classList.add('is-invalid');
                    // Temporarily hide existing validation message if present, or create a new one for strength
                    const existingError = document.querySelector('#passwordHelp.invalid-feedback');
                    if (!existingError) {
                        // Or just let server-side handle the final validation
                    }
                } else {
                    // Only remove 'is-invalid' if no server-side error is present
                    if (!'<?= !empty($errors['password']) ?>') { // Simple PHP check
                        passwordInput.classList.remove('is-invalid');
                    }
                }
            }

            // Attach the update function to the input event of the password field
            if (passwordInput) {
                passwordInput.addEventListener('input', updateStrengthIndicator);
            }
        });
    </script>
    </body>
</html>