<?php
namespace Repositories;
use Database\Connection;

/**
 * repositories/BranchRepository.php
 *
 * Purpose:
 *  - Encapsulate DB access for the branches table.
 */
class BranchRepository {
    public static function findByEmail($email) {
        $pdo = Connection::get();
        $stmt = $pdo->prepare('SELECT * FROM branches WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $pdo = Connection::get();
        $sql = 'INSERT INTO branches (owner_name,email,franchise_number,password,phone,lot_number,street,city,province,created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['owner_name'],
            $data['email'],
            $data['franchise_number'],
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
