<?php
	namespace scr;
	
	class MyQueryBuilder
	{
		private static $link;
        protected $config;
        protected $query;
		
		public function __construct($config)
		{
		    $this->config = $config;
		}

        protected function reset()
        {
            $this->query = new \stdClass();
        }

        public function select($columns =[])
        {
            $this->reset();
            $this->query->base = "SELECT " . implode(", ", $columns);
            if(!count($columns))
                $this->query->base = "SELECT *";
                $this->query->type = 'select';
            return $this;
        }

        public function insert($table, $arr)
        {
            $this->reset();
            $columns = [];
            $data = [];
            foreach ($arr as $key => $value) {
                $columns[] = $key;
                $data[] ="'$value'";
            }
            $this->query->base = "INSERT INTO " . $table . ' (' . implode(", ", $columns) . ') VALUES (' . implode(", ", $data) . ');';
            $this->query->type = 'insert';
            return $this;
        }

        public function update($table, $arr)
        {
            $this->reset();
            $data = [];
            foreach ($arr as $key => $value) {
                $data[] = "`$key` = '".trim($value)."'";
            }
            $this->query->base = "UPDATE " . $table . " SET " . implode(", ", $data);
            $this->query->type = 'update';
            return $this;
        }

        public function delete()
        {
            $this->reset();
            $this->query->base = "DELETE ";
            $this->query->type = 'delete';
            return $this;
        }

        public function from($table)
        {
            $this->query->from = " FROM " . $table;
            return $this;
        }

        public function join($ptable, $ctable, $pid, $cid, $typejoin = 'LEFT JOIN', $operator = '=')
        {
            if (!in_array($this->query->type, ['select'])) {
                throw new \Exception("Join can only be added to SELECT");
            }
            if(!$this->query->join) {
                $this->query->join =  ' '. $typejoin . ' '. $ctable . ' ON ' . $ptable . '.' . $pid . $operator . $ctable . '.' . $cid;
            } else {
                $this->query->join = $this->query->join . ' '. $typejoin . ' '. $ctable . ' ON ' . $ptable . '.' . $pid . $operator . $ctable . '.' . $cid;
            }
            return $this;
        }

        public function where($field, $value, $operator = '=')
        {
            if (!in_array($this->query->type, ['select', 'update', 'delete'])) {
                throw new \Exception("WHERE can only be added to SELECT, UPDATE OR DELETE");
            }
            $this->query->where[] = "$field $operator '$value'";

            return $this;
        }

        public function limit($offset, $start = 0)
        {
            if (!in_array($this->query->type, ['select'])) {
                throw new \Exception("LIMIT can only be added to SELECT");
            }
            if($this->config['type'] =='mysql')
                $this->limitMysql($offset,$start);
            if($this->config['type'] =='postgres')
                $this->limitPostgresql($offset,$start);
            return $this;
        }

        protected function limitMysql($offset, $start = 0)
        {
                $start > 0 ? $this->query->limit = " LIMIT " . $start . ", " . $offset : $this->query->limit = " LIMIT " . $offset;
        }

        protected function limitPostgresql($offset, $start = 0)
        {
                $start > 0 ? $this->query->limit = $this->query->limit = " LIMIT " . $start . " OFFSET " . $offset : $this->query->limit = " LIMIT " . $offset;
        }

        public function execute()
        {
            $query = $this->query;
            $sql = $query->base . $query->from;
            if (!empty($query->join)) {
                $sql .= $query->join;
            }
            if (!empty($query->where)) {
                $sql .= " WHERE " . implode(' AND ', $query->where);
            }
            if (isset($query->limit)) {
                $sql .= $query->limit;
            }
//            var_dump($sql);
//            var_dump($this->config['type']);
            $this->sql = $sql . ";";
            if($this->config['type'] =='mysql')
                $result = $this->requestMysql($sql);
            if($this->config['type'] =='postgres')
                $result = $this->requestPostgressql($sql);
            return $result;

        }

        protected function requestMysql($query)
        {
            $config=$this->config;
            if (!self::$link) {
                self::$link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['db']);
                mysqli_query(self::$link, "SET NAMES 'utf8'");
            }
            $result = mysqli_query(self::$link, $query) or die(mysqli_error(self::$link));
            return $result;
        }

        protected function requestPostgressql($query)
        {
            $config=$this->config;
            if (!self::$link) {
                $conn_string = "host=" .$config['host'] . " port=" . $config['port'] . " dbname=" . $config['db'] . " user=" . $config['user'] . " password=" . $config['pass'];
                self::$link = pg_pconnect($conn_string);
                pg_query(self::$link, "SET NAMES 'utf8'");
            }
            $result =  pg_query(self::$link, $query) or die(pg_result_error(self::$link));
            return $result;
        }
    }
