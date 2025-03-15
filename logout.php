<?php
// Include initialization file
require_once 'config/init.php';

// Logout user
logoutUser();

// Redirect to login page with logout message
redirect('login.php?logout=1');
?> 