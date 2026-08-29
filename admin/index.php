<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}
header("Content-Type: text/html; charset=UTF-8");
include __DIR__ . '/../admin.html';
