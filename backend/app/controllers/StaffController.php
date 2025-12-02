<?php

class StaffController extends BaseController
{
    private Staff $staffModel;

    public function __construct()
    {
        parent::__construct();
        $this->staffModel = new Staff();
    }

    // POST /api/branch/staff/create
    public function create(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();
        // Optional: require manager role
        RoleMiddleware::requireRole(['manager']);

        $data = $req->body();
        $errors = Validator::required(['name', 'email', 'password'], $data);
        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        // check duplicate email in branch
        $existing = $this->staffModel->findByEmailAndBranch($data['email'], $_SESSION['branch_id']);
        if ($existing) {
            return $res->json(['status' => 409, 'message' => 'Staff email already exists for this branch'], 409);
        }

        $payload = [
            'name' => Sanitizer::cleanString($data['name']),
            'branch_id' => $_SESSION['branch_id'],
            'email' => Sanitizer::cleanString($data['email']),
            'phone' => $data['phone'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'staff'
        ];

        $staff_id = $this->staffModel->create($payload);

        return $res->json(['status' => 201, 'message' => 'Staff created', 'staff_id' => $staff_id]);
    }

    // PUT /api/branch/staff/update
    public function update(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();
        RoleMiddleware::requireRole(['manager']);

        $data = $req->body();
        $errors = Validator::required(['staff_id'], $data);
        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $staff_id = (int)$data['staff_id'];
        $staff = $this->staffModel->findById($staff_id);
        if (!$staff || (int)$staff['branch_id'] !== (int)$_SESSION['branch_id']) {
            return $res->json(['status' => 404, 'message' => 'Staff not found'], 404);
        }

        $updateData = [];
        if (isset($data['name'])) $updateData['name'] = Sanitizer::cleanString($data['name']);
        if (isset($data['email'])) $updateData['email'] = Sanitizer::cleanString($data['email']);
        if (isset($data['phone'])) $updateData['phone'] = $data['phone'];
        if (isset($data['password']) && $data['password'] !== '') $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        if (isset($data['role'])) $updateData['role'] = $data['role'];

        $ok = $this->staffModel->update($staff_id, $updateData);
        if (!$ok) {
            return $res->json(['status' => 500, 'message' => 'No changes made or update failed'], 500);
        }

        return $res->json(['status' => 200, 'message' => 'Staff updated']);
    }

    // GET /api/branch/staff/list
    public function list(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();
        // managers and staff can view
        $branch_id = $_SESSION['branch_id'];
        $list = $this->staffModel->listByBranch($branch_id);
        return $res->json(['status' => 200, 'staff' => $list]);
    }

    // POST /api/branch/staff/deactivate
    public function deactivate(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();
        RoleMiddleware::requireRole(['manager']);

        $data = $req->body();
        $errors = Validator::required(['staff_id'], $data);
        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $staff_id = (int)$data['staff_id'];
        $staff = $this->staffModel->findById($staff_id);
        if (!$staff || (int)$staff['branch_id'] !== (int)$_SESSION['branch_id']) {
            return $res->json(['status' => 404, 'message' => 'Staff not found'], 404);
        }

        $ok = $this->staffModel->deactivate($staff_id);
        if (!$ok) {
            return $res->json(['status' => 500, 'message' => 'Failed to deactivate staff'], 500);
        }

        return $res->json(['status' => 200, 'message' => 'Staff deactivated']);
    }

    // POST /api/branch/staff/activate
    public function activate(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();
        RoleMiddleware::requireRole(['manager']);

        $data = $req->body();
        $errors = Validator::required(['staff_id'], $data);
        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $staff_id = (int)$data['staff_id'];
        $staff = $this->staffModel->findById($staff_id);
        if (!$staff || (int)$staff['branch_id'] !== (int)$_SESSION['branch_id']) {
            return $res->json(['status' => 404, 'message' => 'Staff not found'], 404);
        }

        $ok = $this->staffModel->activate($staff_id);
        if (!$ok) {
            return $res->json(['status' => 500, 'message' => 'Failed to activate staff'], 500);
        }

        return $res->json(['status' => 200, 'message' => 'Staff activated']);
    }

    // POST /api/branch/staff/change-role
    public function changeRole(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();
        RoleMiddleware::requireRole(['manager']);

        $data = $req->body();
        $errors = Validator::required(['staff_id', 'role'], $data);
        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $staff_id = (int)$data['staff_id'];
        $role = Sanitizer::cleanString($data['role']);
        $allowed = ['staff', 'cashier', 'chef', 'manager'];

        if (!in_array($role, $allowed)) {
            return $res->json(['status' => 400, 'message' => 'Invalid role'], 400);
        }

        $staff = $this->staffModel->findById($staff_id);
        if (!$staff || (int)$staff['branch_id'] !== (int)$_SESSION['branch_id']) {
            return $res->json(['status' => 404, 'message' => 'Staff not found'], 404);
        }

        $ok = $this->staffModel->changeRole($staff_id, $role);
        if (!$ok) {
            return $res->json(['status' => 500, 'message' => 'Failed to change role'], 500);
        }

        return $res->json(['status' => 200, 'message' => 'Role updated']);
    }
}
