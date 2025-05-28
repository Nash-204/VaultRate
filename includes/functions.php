<?php

// Basic security function
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Redirect helper
function redirect($location) {
    header("Location: $location");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}