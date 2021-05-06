<?php
if (!defined('DXMA_VERSION')) {
    die();
}

class DatabaseConnection
{
    protected $connection;
    protected $query;
    protected $query_open = false;

    public function __construct($dbhost, $dbuser, $dbpass, $dbname)
    {
        $this->connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
        if ($this->connection->connect_error) {
            throw new Exception('MySQL connection failed: ' . $this->connection->connect_error);
        }
        $this->connection->set_charset('utf8');
    }
    
    public static function get()
    {
        return new DatabaseConnection(DBHOST, DBUSER, DBPASS, DBNAME);
    }

    private function type_($var)
    {
        if (is_string($var)) {
            return 's';
        } elseif (is_float($var)) {
            return 'd';
        } elseif (is_int($var)) {
            return 'i';
        } elseif (is_null($var)) {
            return 's';
        } else {
            return 'b';
        }
    }

    public function migrateQuery(string $stmts)
    {
        $this->connection->multi_query($stmts);
        if ($this->connection->errno !== 0) {
            throw new Exception('MySQL migrate failed: ' . $this->connection->error);
        }
        while ($this->connection->more_results() && $this->connection->next_result()) {
        }
        if ($this->connection->errno !== 0) {
            throw new Exception('MySQL migrate failed: ' . $this->connection->error);
        }
    }

    public function query(string $query, ...$params)
    {
        if ($this->query_open) {
            $this->query->close();
        }
        if ($this->query = $this->connection->prepare($query)) {
            if (func_num_args() > 1) {
                $types = '';
                $values = array();
                foreach ($params as $k => &$arg) {
                    if (is_array($params[$k])) {
                        foreach ($params[$k] as $index => &$a) {
                            $types .= $this->type_($params[$k][$index]);
                            $values[] = &$a;
                        }
                    } else {
                        $types .= $this->type_($params[$k]);
                        $values[] = &$arg;
                    }
                }
                array_unshift($values, $types);
                call_user_func_array(array($this->query, 'bind_param'), $values);
            }
            $this->query->execute();
            if ($this->query->errno) {
                throw new Exception('MySQL query failed: ' . $this->query->error);
            }
            $this->query_open = true;
        } else {
            throw new Exception('MySQL prepare failed: ' . $this->connection->error);
        }
        return $this;
    }

    public function execute(string $query, ...$params)
    {
        if ($this->query_open) {
            $this->query->close();
        }
        if ($this->query = $this->connection->prepare($query)) {
            if (func_num_args() > 1) {
                $types = '';
                $values = array();
                foreach ($params as $k => &$arg) {
                    if (is_array($params[$k])) {
                        foreach ($params[$k] as $index => &$a) {
                            $types .= $this->type_($params[$k][$index]);
                            $values[] = &$a;
                        }
                    } else {
                        $types .= $this->type_($params[$k]);
                        $values[] = &$arg;
                    }
                }
                array_unshift($values, $types);
                call_user_func_array(array($this->query, 'bind_param'), $values);
            }
            $ok = $this->query->execute();
            $this->query_open = $ok;
            return $ok;
        } else {
            throw new Exception('MySQL prepare failed: ' . $this->connection->error);
        }
        return $this;
    }

    public function exists()
    {
        return $this->one() !== null;
    }

    public function one()
    {
        $result = $this->query->get_result();
        $this->query->close();
        $this->query_open = false;
        return $result->fetch_assoc();
    }

    public function all(int $limit = PHP_INT_MAX)
    {
        $result = $this->query->get_result();
        $array = array();
        while ($row = $result->fetch_assoc()) {
            $array[] = $row;
        }
        $this->query->close();
        $this->query_open = false;
        return $array;
    }
    
    public function newId()
    {
        return $this->connection->insert_id;
    }
    
    public function total()
    {
        $this->query->store_result();
        return $this->query->num_rows;
    }
    
    public function begin()
    {
        if (!$this->connection->begin_transaction()) {
            throw new Exception('MySQL transaction begin failed: ' . $this->connection->error);
        }
        $this->connection->autocommit(false);
    }
    
    public function commit()
    {
        if (!$this->connection->commit()) {
            throw new Exception('MySQL transaction commit failed: ' . $this->connection->error);
        }
        $this->connection->autocommit(true);
    }
    
    public function abort()
    {
        $this->connection->rollback();
        $this->connection->autocommit(true);
    }
    
    public function close()
    {
        return $this->connection->close();
    }
}
