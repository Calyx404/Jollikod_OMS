<?php
class AuthController {
    public static function login($pdo, $post) {
        // Expect: type=customer|branch, email, password
        $type = $post['type'] ?? 'customer';
        $email = $post['email'] ?? '';
        $password = $post['password'] ?? '';

        if ($type === 'branch') {
            $stmt = $pdo->prepare("SELECT branch_id, email, password FROM branches WHERE email = ?");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, $row['password'])) {
                $_SESSION['branch_id'] = $row['branch_id'];
                Response::json(['status'=>'ok','role'=>'branch','id'=>$row['branch_id']]);
            }
        } else {
            $stmt = $pdo->prepare("SELECT customer_id, email, password FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, $row['password'])) {
                $_SESSION['customer_id'] = $row['customer_id'];
                Response::json(['status'=>'ok','role'=>'customer','id'=>$row['customer_id']]);
            }
        }
        Response::json(['status'=>'error','msg'=>'invalid credentials'], 401);
    }
}
