<?php
namespace Repositories;
use Database\Connection;

/**
 * repositories/CustomerRepository.php
 *
 * Purpose:
 *  - Encapsulate DB access for the customer table.
 *
 * Flow:
 *  - findByEmail -> returns user row or false
 *  - create -> inserts a record and returns lastInsertId
 */
class CustomerRepository {
    public static function findByEmail($email) {
        $pdo = Connection::get();
        $stmt = $pdo->prepare('SELECT * FROM customer WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $pdo = Connection::get();
        $sql = 'INSERT INTO customer (customer_name,email,password,phone,lot_number,street,city,province,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['customer_name'],
            $data['email'],
            $data['password'],
            $data['phone'],
            $data['lot_number'],
            $data['street'],
            $data['city'],
            $data['province']
        ]);
        return $pdo->lastInsertId();
    }
}
