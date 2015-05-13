<?php

class Connection {
    private $pdo;

    public function __construct($dns, $user, $pass, $opts) {
        try {
            $this->pdo = new PDO($dns, $user, $pass, $opts);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function add($cols) {
        $sql = 'INSERT INTO Files (id, name, mime, thumb, size, date, comment) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $this->pdo->prepare($sql)->execute($cols);
    }

    public function get($id) {
        $sql = 'SELECT * FROM Files WHERE id=?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function search($search) {
        $sql = 'SELECT * FROM Files WHERE name LIKE ? ORDER BY date DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["%$search%"]);
        return $stmt->fetchAll();
    }
}