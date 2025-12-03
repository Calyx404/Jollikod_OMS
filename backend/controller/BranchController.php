<?php
require_once '../database/connection.php';
require_once '../utils/session.php';

class BranchController {

    public static function getBranchInfo() {
        checkAuth('branch');
        global $pdo;
        $branchId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT branch_id, name, email, phone, address, created_at FROM branches WHERE branch_id = ?");
        $stmt->execute([$branchId]);
        $branch = $stmt->fetch();

        echo json_encode($branch);
    }

    public static function updateBranch() {
        checkAuth('branch');
        global $pdo;
        $branchId = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $pdo->prepare("UPDATE branches SET name = ?, phone = ?, address = ? WHERE branch_id = ?");
        $stmt->execute([
            $data['branch_name'] ?? null,
            $data['contact_number'] ?? null,
            $data['address'] ?? null,
            $branchId
        ]);

        echo json_encode(['message' => 'Branch updated successfully']);
    }
}
