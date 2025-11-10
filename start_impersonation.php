<?php
session_start();

// 1. Check if the user is actually an admin
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    
    // 2. Store the "return ticket"
    $_SESSION['original_user_type'] = 'admin';
    
    // 3. "Demote" the current session to a user
    $_SESSION['user_type'] = 'user';
    
    // 4. Redirect to the main user page
    //    (NOTE: Change 'main_menu.php' if your main user page is index.php or something else)
    header('Location: main_menu.php');
    exit();
    
} else {
    // Not an admin, send them away
    header('Location: login.php');
    exit();
}
?>