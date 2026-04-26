<?php
// index.php - main entry point → always redirect to login or dashboard

require_once 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
} else {
    header("Location: login.php");
    exit;
}