<?php
session_start();

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function requireLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized']);
    }
}

function requireBranch() {
    requireLogin();
    if ($_SESSION['user_type'] !== 'branch') {
        jsonResponse(['success' => false, 'message' => 'Branch access only']);
    }
}

function requireCustomer() {
    requireLogin();
    if ($_SESSION['user_type'] !== 'customer') {
        jsonResponse(['success' => false, 'message' => 'Customer access only']);
    }
}
?>