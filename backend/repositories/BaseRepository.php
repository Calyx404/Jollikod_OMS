<?php
namespace Repositories;
use Database\Connection;
use PDO;


abstract class BaseRepository {
protected static $table;


public static function find($id) {
$pdo = Connection::get();
$stmt = $pdo->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
$stmt->execute([$id]);
return $stmt->fetch(PDO::FETCH_ASSOC);
}
}