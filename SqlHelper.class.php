<?php
class SqlHelper{
    private $host;
    private $port;
    private $username;
    private $password;
    private $db;
    private $link;

    public function __construct(){
        $ini_path='e:/xampp/htdocs/xiaoshuocurl/db.ini';
        $db_ini_arr=parse_ini_file($ini_path);//从配置文件夹读取数据库配置信息
        $this->host=$db_ini_arr['host'];
        $this->port=$db_ini_arr['port'];
        $this->username=$db_ini_arr['username'];
        $this->password=$db_ini_arr['password'];
        $this->db=$db_ini_arr['db'];
        $dsn="mysql:dbname={$this->db};host={$this->host};port={$this->port}";
        try {
            $this->link = new PDO($dsn, $this->username,$this->password);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
        $this->link->exec("set names utf8");
    }

    //执行查询语句,并以二维数组的形式进行返回
    public function dml($sql){
        $res=$this->link->exec($sql);
        if($res){
            return 1;//执行成功
        }else{
            if($res==0){
                return 2;//影响行数为0
            }
            return 0;//执行失败
        }
    }
    public function dql_arr($sql){
        $arr=array();
        $res=$this->link->query($sql);
        while($row=$res->fetch()){
            $arr[]=$row;//把得到的结果保存到数组里面
        }
        $res=null;
        $this->close_connect();//关闭连接
        return $arr;//返回数据
    }

    public function close_connect(){
        $this->link=null;//关闭数据库连接
    }

}