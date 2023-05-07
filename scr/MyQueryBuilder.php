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
			if (!self::$link) {
				self::$link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['db']);
				mysqli_query(self::$link, "SET NAMES 'utf8'");
			}
		}
        protected function reset()
        {
            $this->query = new \stdClass();
        }
        public function select($columns)
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
            $data = [];
            foreach ($arr as $key => $value) {
                $data[] = "`$key` = '".trim($value)."'";
            }
            $this->query->base = "INSERT INTO " . $table . " SET " . implode(", ", $data);
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
            if($this->config['type'] =='postgresql')
                $this->limitPostgresql($offset,$start);
            return $this;
        }
        public function limitMysql($offset, $start = 0)
        {
            if($this->config['type'] =='mysql')
                $start > 0 ? $this->query->limit = " LIMIT " . $start . ", " . $offset : $this->query->limit = " LIMIT " . $offset;
        }
        public function limitPostgresql($offset, $start = 0)
        {
            if($this->config['type'] =='mysql')
                $start > 0 ? $this->query->limit = $this->query->limit = " LIMIT " . $start . " OFFSET " . $offset : $this->query->limit = " LIMIT " . $offset;
        }
        public function execute()
        {
            $query = $this->query;
//            ($query->type =='select'or $query->type =='delete') ? $sql = $query->base . $query->from : $sql = $query->base;
            $sql = $query->base . $query->from;
            if (!empty($query->where)) {
                $sql .= " WHERE " . implode(' AND ', $query->where);
            }
            if (isset($query->limit)) {
                $sql .= $query->limit;
            }
            var_dump($sql);
            var_dump($this->config['type']);
            $this->sql = $sql . ";";
            if($this->config['type'] =='mysql') {
                $result = $this->requestMysql($sql);
                return $result;
            }
        }
        public function findMany($query)
        {
            $result = mysqli_query(self::$link, $query) or die(mysqli_error(self::$link));
            for ($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);
            return $data;
        }
        protected function requestMysql($query)
        {
            $result = mysqli_query(self::$link, $query) or die(mysqli_error(self::$link));
            return $result;
        }

    }
