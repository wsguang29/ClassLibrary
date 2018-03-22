<?php

    class Model
    {
        //主机名
        protected $host;
        //用户名
        protected $user;
        //密码
        protected $pwd;
        //数据库名
        protected $name;
        //字符集
        protected $charset;
        //表前缀
        protected $prefix;
        //表名
        protected $table = 'user';
        //字段名
        protected $fields;
        //链接
        protected $link;
        //选项
        protected $options;

        //初始化一批成员属性
        public function __construct($config){
            $this->host = $config['DB_HOST'];
            $this->user = $config['DB_USER'];
            $this->pwd = $config['DB_PWD'];
            $this->name = $config['DB_NAME'];
            $this->charset = $config['DB_CHARSET'];
            $this->prefix = $config['DB_PREFIX'];

            $this->link = $this->connect();
            $this->table = $this->getTable();
            $this->fields = $this->getFields();
            $this->getOptions();
        }

        //处理链接
        protected function connect()
        {
            $link = mysqli_connect($this->host,$this->user,$this->pwd);

            if(!$link){
                exit('数据库链接失败！');
            }

            mysqli_select_db($link,$this->name);

            mysqli_set_charset($link,$this->charset);

            return $link;
        }

        //处理表名
        protected function getTable()
        {
            if(!empty($this->table)){

                return $this->prefix.$this->table;
            }else{

                return $this->prefix.strtolower(substr(get_class($this),0,-5));
            }
        }

        //处理缓存字段
        protected function getFields()
        {
            $cacheFile = './cache/' . $this->table . '.php';
            if(file_exists($cacheFile)){

                return include $cacheFile;
            }else{

                $sql = 'desc ' . $this->table;
                $result = $this->query($sql);
                foreach($result as $key => $value){
                    $fileds[] = $value['Field'];

                    if($value['Key'] == 'PRI'){
                        $fileds['PRI'] = $value['Field'];
                    }
                }
                $str = var_export($fileds,true);
                $str = "<?php\n return " . $str . ';';
                file_put_contents($cacheFile , $str);
                return $fileds;
            }
        }

        //处理query
        protected function query($sql)
        {
            $result = mysqli_query($this->link, $sql);
            if($result && mysqli_affected_rows($this->link)){
                while($data = mysqli_fetch_assoc($result)){
                    $newData[] = $data;
                }
                return $newData;
            }
            return false;
        }

        //处理选项
        protected function getOptions()
        {
            $arr = ['fields' , 'table' , 'where' , 'group' , 'having' , 'order' , 'limit'];
            foreach($arr as $key => $value){
                $this->options[$value] = '';
                if($value == 'fields'){
                    $this->options[$value] = join(',', array_unique($this->fields));
                }else if($value == 'table'){
                    $this->options[$value] = $this->table;
                }
            }
        }

        //where条件
        public function where($where)
        {
            if(!empty($where)){
                $this->options['where'] = 'where ' . $where;
            }
            return $this;
        }

        //table函数
        public function table($table)
        {
            if(!empty($table)){
                $this->options['table'] = $table;
            }
            return $this;
        }

        //fields条件
        public function fields($fields)
        {
            if(!empty($fields)){
                if(is_string($fields)){
                    $this->options['fields'] = $fields;
                }else if(is_array($fields)){
                    $this->options['fields'] = join(',',$fields);
                }
            }
            return $this;
        }

        //group条件
        public function group($group)
        {
            if(!empty($group)){
                $this->options['group'] = 'group by ' . $group;
            }
            return $this;
        }

        //having条件
        public function having($having)
        {
            if(!empty($having)){
                $this->options['having'] = 'having ' . $having;
            }
            return $this;
        }

        //order条件
        public function order($order)
        {
            if(!empty($order)){
                $this->options['order'] = 'order by ' . $order;
            }
            return $this;
        }

        //limit条件
        public function limit($limit)
        {
            if(!empty($limit)){
                if(is_string($limit)){
                    $this->options['limit'] = 'limit ' . $limit;
                }else if(is_array($limit)){
                    $this->options['limit'] = 'limit' . join(',',$limit);
                }
            }
            return $this;
        }

        //查询函数
        public function select()
        {
            $sql = 'select %FIELDS% from %TABLE% %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT%';
            $sql = str_replace(
                ['%FIELDS%', '%TABLE%', '%WHERE%','%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%'],
                [$this->options['fields'], $this->options['table'], $this->options['where'], $this->options['group'],$this->options['having'],$this->options['order'],$this->options['limit']],
                $sql
                );
            $this->sql = $sql;
            return $this->query($sql);
        }

        //增删改执行函数
        protected function exec($sql,$insertId = false)
        {
            $result = mysqli_query($this->link,$sql);
            if($result && mysqli_affected_rows($this->link)){
                if($insertId){
                    return mysqli_insert_id($this->link);
                }else{
                    return mysqli_affected_rows($this->link);
                }
            }
            return false;
        }

        //添加函数
        public function insert($data)
        {
            $data = $this->parseValue($data);

            $keys = array_keys($data);

            $values = array_values($data);

            $sql = 'insert into %TABLE%(%FIELDS%) values(%VALUES%)';
            $sql = str_replace(
            ['%TABLE%','%FIELDS%','%VALUES%'],
            [$this->options['table'], join(',', $keys), join(',', $values)],
            $sql);
        $this->sql = $sql;
        return $this->exec($sql, true);
        }

        //处理parseValue
        protected function parseValue($data)
        {
            foreach($data as $key => $value){
                if(is_string($value)){
                    $value = '"' . $value . '"';
                }
                $newData[$key] = $value;
            }
            return $newData;
        }

        //删除函数
        public function del()
        {
            $sql = 'delete from %TABLE% %WHERE%';
            $sql = str_replace(['%TABLE%','%WHERE%'],
                [$this->options['table'], $this->options['where']],
                $sql);
            $this->sql = $sql;
            return $this->exec($sql);
        }

        //修改函数
        public function update($data)
        {
            $data = $this->parseValue($data);
            $value = $this->parseUpdate($data);
            $sql = 'update %TABLE% set %VALUE% %WHERE%';
            $sql = str_replace(
                ['%TABLE%', '%VALUE%', '%WHERE%'],
                [$this->options['table'], $value, $this->options['where']],
                $sql);
            $this->sql = $sql;
            return $this->exec($sql);
        }

        //处理parseUpdate
        protected function parseUpdate($data)
        {
            foreach($data as $key => $value){
                $guang[] = $key . '=' . $value;
            }
            return join(',',$guang);
        }

    }
