<?php
session_start();

// 1. Check if the "return ticket" exists
if (isset($_SESSION['original_user_type']) && $_SESSION['original_user_type'] === 'admin') {
    
    // 2. Restore admin status
    $_SESSION['user_type'] = 'admin';
    
    // 3. Remove the ticket
    unset($_SESSION['original_user_type']);
    
    // 4. Redirect back to the admin dashboard
    header('Location: main_menu_admin.php');
    exit();
    
} else {
    // Not impersonating, send them away
    header('Location: login.php');
    exit();
}
?>