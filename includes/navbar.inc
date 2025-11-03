<?php
// 2. Prepare the dynamic authentication button based on session state
if (isset($_SESSION['user'])) {
    // User is logged in, show Logout button
    $authButton = '<a class="btn btn-light rounded-pill ms-lg-3 px-3 py-2" href="logout.php">Logout</a>';
} else {
    // User is NOT logged in, show Login button
    $authButton = '<a class="btn btn-light rounded-pill ms-lg-3 px-3 py-2" href="login.php">Login</a>';
}

// 3. Start the Heredoc block, incorporating the $authButton variable
echo <<<NAVBAR
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm py-2">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="images/logo.svg" alt="Root Flowers Logo" class="navbar-logo">
            <span class="brand-logo-text ms-2">Root Flowers</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarRoot" aria-controls="navbarRoot" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarRoot">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item"><a class="nav-link <?= (\$currentPage == 'main_menu.php') ? 'active' : '' ?>" href="main_menu.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= (\$currentPage == 'products.php') ? 'active' : '' ?>" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link <?= (\$currentPage == 'workshops.php') ? 'active' : '' ?>" href="workshops.php">Workshops</a></li>
                <li class="nav-item"><a class="nav-link <?= (\$currentPage == 'studentworks.php') ? 'active' : '' ?>" href="studentworks.php">Student Works</a></li>
                <li class="nav-item"><a class="nav-link disabled" href="#">Flower Name</a></li>
                
                <li class="nav-item">$authButton</li>
            </ul>
        </div>
    </div>
</nav>
NAVBAR;
?>