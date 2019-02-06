<?php

require_once(__DIR__ . '/AbstractDb.php');

class MySqlPdo extends AbstractDb
{
    public function __construct(
        string $db,
        string $hostName = null,
        string $userName = null,
        string $password = null,
        string $prefix = ''
    ) {
        $this->prefix = $prefix;

        $dsn = "mysql:host={$hostName};dbname={$db};port=3306";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // note that not all drivers support this; remove if in error
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->db = new PDO($dsn, $userName, $password, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    private function prepareStmt(string $sql, array $params)
    {
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        foreach ($params as $p) {
            $type = gettype($p);
            switch ($type) {
                case 'integer':
                    $paramType = \PDO::PARAM_INT;
                    break;
                case 'double':
                case 'float':
                    // have to use string for floats
                    $paramType = \PDO::PARAM_STR;
                    break;
                default:
                case 'string':
                    $paramType = \PDO::PARAM_STR;
                    break;
            }
            $stmt->bindParam($p, $paramType);
        }

        return $stmt;
    }

    protected function query(string $sql, array $params): array
    {
        $stmt = $this->prepareStmt($sql, $params);
        if (!$stmt) {
            return false; // should be exception or empty array
        }
        if (!$stmt->execute()) {
            return false; // should be exception or empty array
        }

        return $stmt->fetchAll();
    }

    protected function execute(string $sql, array $params): bool
    {
        $stmt = $this->prepareStmt($sql, $params);
        if (!$stmt) {
            return false;
        }
        $result = $stmt->execute();

        return $result;
    }
}
