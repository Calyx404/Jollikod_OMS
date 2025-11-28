<?php
namespace Controllers;

use Core\Response;
use Requests\RegisterCustomerRequest;
use Requests\RegisterBranchRequest;
use Requests\LoginRequest;
use Services\AuthService;

/**
 * controllers/AuthController.php
 *
 * Purpose:
 *  - Provide separate flows for customer and branch register/login/logout.
 *
 * Flow:
 *  - Validate request -> call AuthService -> set session keys -> return JSON
 */
class AuthController {
    public function registerCustomer($request) {
        $data = RegisterCustomerRequest::validate($request->body);
        $result = AuthService::registerCustomer($data);
        if (isset($result['error'])) return Response::json($result, 422);
        return Response::json($result, 201);
    }

    public function loginCustomer($request) {
        $data = LoginRequest::validate($request->body);
        $result = AuthService::loginCustomer($data['email'], $data['password']);
        if (isset($result['error'])) return Response::json($result, 401);
        return Response::json($result, 200);
    }

    public function logoutCustomer($request) {
        $result = AuthService::logoutCustomer();
        return Response::json($result, 200);
    }

    public function registerBranch($request) {
        $data = RegisterBranchRequest::validate($request->body);
        $result = AuthService::registerBranch($data);
        if (isset($result['error'])) return Response::json($result, 422);
        return Response::json($result, 201);
    }

    public function loginBranch($request) {
        $data = LoginRequest::validate($request->body);
        $result = AuthService::loginBranch($data['email'], $data['password']);
        if (isset($result['error'])) return Response::json($result, 401);
        return Response::json($result, 200);
    }

    public function logoutBranch($request) {
        $result = AuthService::logoutBranch();
        return Response::json($result, 200);
    }
}
