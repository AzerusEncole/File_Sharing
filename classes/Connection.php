<?php

class Connection {
    private $pdo;
    private $sqls;

    public function __construct($dns, $user, $pass, $opts) {
        try {
            $this->pdo = new PDO($dns, $user, $pass, $opts);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        $this->sqls = [
            'add' => 'INSERT INTO Files (id, name, mime, thumb, size, date, comment) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'get' => 'SELECT * FROM Files WHERE id=?',
            'search' => 'SELECT * FROM Files WHERE name LIKE ? ORDER BY date DESC'
        ];
    }

    public function execute($sql, $params) {
        $stmt = $this->pdo->prepare($this->sqls[$sql]);
        $stmt->execute($params);
        return $stmt;
    }
}