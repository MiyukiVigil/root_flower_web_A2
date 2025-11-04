<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>My Profile - Root & Flowers</title>
        <meta name="author" content="Ivan Liang Jin Ngu">
        <meta name="keywords" content="Profile, Student, Assignment">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="./style/style.css" rel="stylesheet">
        <link rel="icon" href="./images/logo.svg">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    </head>
    <body id="profile_body">
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
        <main class="container py-5">
            <div class="profile-card text-center p-4 p-md-5 shadow-lg rounded-4" data-aos="fade-up">
                
                <img src="./images/ivan.jpg" alt="A photo of Ivan Liang Jin Ngu" class="profile-image img-fluid mb-4">

                <h1 class="profile_name display-5">Ivan Liang Jin Ngu</h1>
                <p class="profile_id lead"><strong>Student ID:</strong> 104381576</p>
                <p class="profile_email text-muted">104381576@students.swinburne.edu.my</p>

                <hr class="my-4">

                <blockquote class="blockquote bg-white p-4 rounded-3 my-4">
                    <p class="mb-0">I declare that this assignment is my individual work. I have not work collaboratively nor have I copied from any other student's work or from any other source. I have not engaged another party to complete this assignment. I am aware of the University’s policy with regards to plagiarism. I have not allowed, and will not allow, anyone to copy my work with the intention of passing it off as his or her own work.</p>
                </blockquote>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary rounded-pill px-4 py-2 m-2">
                        <i class="bi bi-house-door-fill me-2"></i>Home Page
                    </a>
                    <a href="about.php" class="btn btn-primary rounded-pill px-4 py-2 m-2">
                        <i class="bi bi-info-circle-fill me-2"></i>About Page
                    </a>
                    <a href="mailto:104381576@students.swinburne.edu.my" class="btn btn-primary rounded-pill px-4 py-2 m-2">
                        <i class="bi bi-envelope-fill me-2"></i>Email Me
                    </a>
                </div>

            </div>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
        <script>
            AOS.init({
                duration: 800,
                once: true
            });
        </script>
    </body>
</html>