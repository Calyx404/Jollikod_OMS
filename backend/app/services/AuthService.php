<?php

class AuthService
{
    private Customer $customerModel;
    private Branch $branchModel;

    public function __construct()
    {
        $this->customerModel = new Customer();
        $this->branchModel   = new Branch();
    }

    // -----------------------------
    // CUSTOMER
    // -----------------------------
    public function registerCustomer(array $data): array
    {
        $errors = Validator::required(['name', 'email', 'password'], $data);

        if (!empty($errors)) {
            return ['status' => 422, 'errors' => $errors];
        }

        if ($this->customerModel->findByEmail($data['email'])) {
            return ['status' => 409, 'message' => 'Email already registered'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $customer_id = $this->customerModel->create($data);

        $_SESSION['customer_id'] = $customer_id;

        return ['status' => 201, 'message' => 'Customer registered', 'customer_id' => $customer_id];
    }

    public function loginCustomer(array $data): array
    {
        $errors = Validator::required(['email', 'password'], $data);

        if (!empty($errors)) {
            return ['status' => 422, 'errors' => $errors];
        }

        $customer = $this->customerModel->findByEmail($data['email']);
        if (!$customer || !password_verify($data['password'], $customer['password'])) {
            return ['status' => 401, 'message' => 'Invalid credentials'];
        }

        $_SESSION['customer_id'] = $customer['customer_id'];

        return ['status' => 200, 'message' => 'Customer logged in', 'customer_id' => $customer['customer_id']];
    }

    // -----------------------------
    // BRANCH
    // -----------------------------
    public function registerBranch(array $data): array
    {
        $errors = Validator::required(['name', 'email', 'password'], $data);

        if (!empty($errors)) {
            return ['status' => 422, 'errors' => $errors];
        }

        if ($this->branchModel->findByEmail($data['email'])) {
            return ['status' => 409, 'message' => 'Email already registered'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $branch_id = $this->branchModel->create($data);

        $_SESSION['branch_id'] = $branch_id;

        return ['status' => 201, 'message' => 'Branch registered', 'branch_id' => $branch_id];
    }

    public function loginBranch(array $data): array
    {
        $errors = Validator::required(['email', 'password'], $data);

        if (!empty($errors)) {
            return ['status' => 422, 'errors' => $errors];
        }

        $branch = $this->branchModel->findByEmail($data['email']);
        if (!$branch || !password_verify($data['password'], $branch['password'])) {
            return ['status' => 401, 'message' => 'Invalid credentials'];
        }

        $_SESSION['branch_id'] = $branch['branch_id'];

        return ['status' => 200, 'message' => 'Branch logged in', 'branch_id' => $branch['branch_id']];
    }
}
