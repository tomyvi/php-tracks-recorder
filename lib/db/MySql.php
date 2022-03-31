<?php

require_once(__DIR__ . '/AbstractDb.php');

class MySql extends AbstractDb
{
    public function __construct(string $db, string $hostname = null, string $username = null, string $password = null, string $prefix = '')
    {
        try {
            $this->db = new \mysqli($hostname, $username, $password, $db);
            $this->prefix = $prefix;
        } catch (Exception $ex) {
            _log("mysql error:".$ex->getMessage()." | ".$mysqli->error);
        }
    }    

    private function prepareStmt(string $sql, array $params)
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $typestr = '';
        foreach ($params as $p) {
            $type = gettype($p);
            switch ($type) {
                case 'integer':
                    $typestr .= 'i';
                    break;
                case 'double':
                case 'float':
                    $typestr .= 'd';
                    break;
                default:
                case 'string':
                    $typestr .= 's';
                    break;
            }
        }
        // Splat operator needs PHP 5.6+
        $stmt->bind_param($typestr, ...$params);
        return $stmt;
    }

    protected function query(string $sql, array $params): array
    {
        $stmt = $this->prepareStmt($sql, $params);
        if (!$stmt) {
            return false;
        }
        if (!$stmt->execute()) {
            return false;
        }
        $dbresult = $stmt->get_result();

        $result = array();
        while ($data = $dbresult->fetch_assoc()) {
            // Loop through results here $data[]
            $result[] = $data;
        }

        $stmt->close();
        return $result;
    }

    protected function execute(string $sql, array $params): bool
    {
        $stmt = $this->prepareStmt($sql, $params);
        if (!$stmt) {
            return false;
        }
        $result = $stmt->execute();
        if ($result) {
            $stmt->close();
        }
        return $result;
    }
}
