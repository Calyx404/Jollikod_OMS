<?php

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    // CUSTOMER
    public function registerCustomer(Request $req, Response $res)
    {
        $data = $req->body();

        $result = $this->authService->registerCustomer($data);
        return $this->json($res, $result, $result['status']);
    }

    public function loginCustomer(Request $req, Response $res)
    {
        $data = $req->body();

        $result = $this->authService->loginCustomer($data);
        return $this->json($res, $result, $result['status']);
    }

    public function logoutCustomer(Request $req, Response $res)
    {
        session_unset();
        session_destroy();

        return $this->json($res, ['message' => 'Customer logged out successfully']);
    }

    // BRANCH
    public function registerBranch(Request $req, Response $res)
    {
        $data = $req->body();

        $result = $this->authService->registerBranch($data);
        return $this->json($res, $result, $result['status']);
    }

    public function loginBranch(Request $req, Response $res)
    {
        $data = $req->body();

        $result = $this->authService->loginBranch($data);
        return $this->json($res, $result, $result['status']);
    }

    public function logoutBranch(Request $req, Response $res)
    {
        session_unset();
        session_destroy();

        return $this->json($res, ['message' => 'Branch logged out successfully']);
    }
}
