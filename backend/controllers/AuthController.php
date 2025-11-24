<?php
// AuthController.php
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class AuthController {

    public static function login($pdo, $post) {
        $type = $post['type'] ?? 'customer';
        $email = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';

        if (!$email || !$password) {
            Response::json(['status'=>'error','msg'=>'email and password required'], 400);
        }

        if ($type === 'branch') {
            $stmt = $pdo->prepare("SELECT branch_id, email, password FROM branches WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, $row['password'])) {
                // set session
                session_regenerate_id(true);
                $_SESSION['role'] = 'branch';
                $_SESSION['branch_id'] = $row['branch_id'];
                Response::json(['status'=>'ok','role'=>'branch','id'=>$row['branch_id']]);
            }
        } else {
            $stmt = $pdo->prepare("SELECT customer_id, email, password FROM customers WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['role'] = 'customer';
                $_SESSION['customer_id'] = $row['customer_id'];
                Response::json(['status'=>'ok','role'=>'customer','id'=>$row['customer_id']]);
            }
        }

        Response::json(['status'=>'error','msg'=>'invalid credentials'], 401);
    }

    public static function registerCustomer($pdo, $post) {
        // required fields: customer_name, email, password, phone, lot_number, street, city, province
        $required = ['customer_name','email','password','phone','lot_number','street','city','province'];
        $errors = Validator::required($required, $post);
        if (!empty($errors)) {
            Response::json(['status'=>'error','msg'=>'validation failed','errors'=>$errors], 422);
        }

        $email = trim($post['email']);
        // check exists
        $stmt = $pdo->prepare("SELECT customer_id FROM customers WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Response::json(['status'=>'error','msg'=>'email already registered'], 409);
        }

        $hash = password_hash($post['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO customers (customer_name,email,password,phone,lot_number,street,city,province,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
        $stmt->execute([
            $post['customer_name'],
            $email,
            $hash,
            $post['phone'],
            $post['lot_number'],
            $post['street'],
            $post['city'],
            $post['province']
        ]);
        $id = $pdo->lastInsertId();

        // auto-login
        session_regenerate_id(true);
        $_SESSION['role'] = 'customer';
        $_SESSION['customer_id'] = $id;

        Response::json(['status'=>'ok','id'=>$id]);
    }

    public static function registerBranch($pdo, $post) {
        // required fields: owner_name, email, password, phone, lot_number, street, city, province, franchise_number
        $required = ['owner_name','email','password','phone','lot_number','street','city','province','franchise_number'];
        $errors = Validator::required($required, $post);
        if (!empty($errors)) {
            Response::json(['status'=>'error','msg'=>'validation failed','errors'=>$errors], 422);
        }

        $email = trim($post['email']);
        // check exists
        $stmt = $pdo->prepare("SELECT branch_id FROM branches WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Response::json(['status'=>'error','msg'=>'email already registered'], 409);
        }

        $hash = password_hash($post['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO branches (owner_name,email,franchise_number,phone,password,lot_number,street,city,province,created_at,branch_verified) VALUES (?,?,?,?,?,?,?,?,?,NOW(),0)");
        $stmt->execute([
            $post['owner_name'],
            $email,
            $post['franchise_number'],
            $post['phone'],
            $hash,
            $post['lot_number'],
            $post['street'],
            $post['city'],
            $post['province']
        ]);
        $branchId = $pdo->lastInsertId();

        // create inventory row for this branch
        $stmt = $pdo->prepare("INSERT INTO inventories (branch_id, created_at) VALUES (?, NOW())");
        $stmt->execute([$branchId]);

        // NOTE: branch_verified = 0; verification process exists as later step (email/manual)
        session_regenerate_id(true);
        $_SESSION['role'] = 'branch';
        $_SESSION['branch_id'] = $branchId;

        Response::json(['status'=>'ok','branch_id'=>$branchId]);
    }
}
