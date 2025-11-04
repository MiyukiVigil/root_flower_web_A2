<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About This Assignment</title>
    <meta name="author" content="Ivan">
    <meta name="keywords" content="About, Assignment, PHP, Frameworks">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="./style/style.css" rel="stylesheet">
    <link rel="icon" href="./images/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="about-page">
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

    <main class="container my-5">
        <div class="about-card p-4 p-md-5 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-5">Assignment Details</h1>
                <p class="lead text-muted">A summary of the work completed for this project.</p>
            </div>

            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <h5><i class="bi bi-code-slash"></i> PHP Version Used</h5>
                    <p>This website is running on PHP version: <span class="php-version"><?= phpversion(); ?></span></p>

                    <h5><i class="bi bi-check2-square"></i> Completed Tasks</h5>
                    <ul>
                        <li><i class="bi bi-check"></i><b>Task 1-13:</b> All core assignment tasks have been completed as per the specification document.</li>
                    </ul>

                    <h5><i class="bi bi-x-square"></i> Unattempted or Incomplete Tasks</h5>
                     <ul>
                        <li><i class="bi bi-check"></i>All assigned tasks have been attempted and completed.</li>
                    </ul>

                    <h5><i class="bi bi-star-fill"></i> Extra Features & Initiative</h5>
                    <p>To demonstrate skills beyond the core requirements, the following advanced security features were implemented:</p>
                    <ul>
                        <li><i class="bi bi-shield-check"></i><div><b>OTP Verification System:</b> A One-Time Password system was integrated for new user registrations and password resets. This enhances security by verifying that the user owns the email address they claim.</div></li>
                        <li><i class="bi bi-key-fill"></i><div><b>Secure Password Reset Flow:</b> A complete "Forgot Password" feature was built, allowing users to securely reset their password via an OTP sent to their registered email.</div></li>
                        <li><i class="bi bi-envelope-check-fill"></i><div><b>Authenticated Email Sending:</b> Implemented <strong>PHPMailer</strong> with SMTP authentication to reliably and securely send system emails, a professional standard superior to PHP's basic `mail()` function.</div></li>
                    </ul>
                    <h5><i class="bi bi-box-seam"></i> Frameworks & 3rd Party Libraries</h5>
                    <ul>
                        <li><i class="bi bi-bootstrap-fill"></i><div><b>Bootstrap v5.3.3</b> - Used as the core CSS framework for layout and components.</div></li>
                        <li><i class="bi bi-person-bounding-box"></i><b><div>Bootstrap Icons v1.11.3</b> - Used for iconography throughout the site.</div></li>
                        <li><i class="bi bi-google"></i><div><b>Google Fonts</b> - Used for the 'Poppins' and 'Merriweather' typefaces.</div></li>
                        <li><i class="bi bi-box-arrow-down"></i><div><b>AOS (Animate on Scroll) v2.3.4</b> - Used for scroll-triggered animations on the home page.</div></li>
                        <li><i class="bi bi-envelope-paper-heart-fill"></i><div><b>PHPMailer v6.9.1</b> - Used for robust and secure SMTP-based email sending.</div></li>
                    </ul>

                    <h5><i class="bi bi-camera-video-fill"></i> Video Presentation</h5>
                    <p>The video presentation showcasing the website's functionalities can be viewed at the link below.</p>
                    <a href="https://youtu.be/jNE244BIx8A" class="btn btn-primary"><i class="bi bi-play-btn-fill me-2"></i>Watch Video</a>

                    <hr class="my-4">

                    <div class="text-center">
                        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-house-door-fill me-2"></i>Return to Home Page</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>