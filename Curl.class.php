<?php

class Curl{
    private $ch;//抓取资源句柄


    /*
     * 初始化curl会话
     * @param string url 需要获取数据的url地址
     */
    public function __construct($url){
        $this->ch=curl_init($url);
    }


    /*
     * 设置curl传输选项
     * param boolean $returntransfer 返回数据方式
     * param string $cokie 传递过程的cookie
     * param array $post_data 需要提交的post数据
     * param string $referer 来访页面
     */
    public function setPostOpt($transfer=true,$cookie,$post_data=array(),$referer){
       $arr=array(
           'CURLOPT_POST'          => TRUE,
           'CURLOPT_POSTFIELDS'    => $post_data,
           'CURLOPT_RETURNTRANSFER'=> $transfer,
           'CURLOPT_COOKIE'        => $cookie,
           'CURLOPT_REFERER'       => $referer
       );
        curl_setopt_array($this->ch,$arr);
        curl_setopt($this->ch,'CURLOPT_POST',TRUE);
        curl_setopt($this->ch,'CURLOPT_POSTFIELDS',$post_data);
        curl_setopt($this->ch,'CURLOPT_RETURNTRANSFER',$transfer);
        curl_setopt($this->ch,'CURLOPT_COOKIE',$transfer);
    }

    /*
     * 设置curl传输选项
     * param boolean $returntransfer 返回数据方式
     * param string $cokie 传递过程的cookie
     * param int $delay_time 等待时间
     */
    public function setGetOpt($transfer=true,$cookie,$referer){
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    }


    public function exec(){
        $data=curl_exec($this->ch);
        return $data;
    }


    /*
     * 获得服务器返回的cookie
     * param array $cookieName cookie名称
     * param string $cookiesavePath cookie存储路径 默认存储当前目录
     * */
    public function getCookie($cookieName=array(),$cookieSavePath='./'){
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $data = $this->exec();
        $cookieArr=array();
        foreach($cookieName as $k => $v){
            preg_match_all("/Set-Cookie: {$v}=[a-zA-Z0-9]*/i", $data,$cookieArr[]);
        }
        $AlreadyGetCookie=null;
        foreach($cookieArr as $k => $v){
            if($AlreadyGetCookie!=null){
                  $AlreadyGetCookie=$AlreadyGetCookie.';'.$v;
            }
        }
        file_put_contents($cookieSavePath.'cookie.txt',$AlreadyGetCookie);
    }

}