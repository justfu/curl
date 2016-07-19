<?php
require 'Curl.class.php';
require 'SqlHelper.class.php';
$sqlHelper=new SqlHelper();
global $start_time;
global $book_id;
$book_id=2073;
function getData($next_id,$sqlHelper,$book_author){
    global $start_time;
    global $book_id;
    $curl = new Curl("http://m.xxsy.net/Service/ServiceMethod?requestData=%7B%22Method%22%3A%22chapter_getdetail%22%2C%22Parameters%22%3A%7B%22userid%22%3A0%2C%22chapterid%22%3A{$next_id}%2C%22notdefault%22%3A1%2C%22stat_page%22%3A%221_reading_p3%22%7D%7D");
    $curl->setGetOpt(true, '', '');
    $resData = json_decode($curl->exec());
    @$title = $resData->ChpterHttpDetail->Data->Title;
    @$content = $resData->ChpterHttpDetail->Data->Content;
    $len = mb_strlen($content, 'UTF-8');
    $addtime = date('Y-m-d H:i:s');
    $is_vip=0;
    @$next_id=$resData->ChpterHttpDetail->Data->NextId;
    $sql="insert into xs_book_list values (null,'$title','$book_author','$len','$content','$addtime','$is_vip','$book_id',DEFAULT)";
    $res=$sqlHelper->dml($sql);
    if($res){
        if($next_id==0){
            $use_time=microtime(true)-$start_time;
            echo 'use '.$use_time.'s'."\r\n";
            echo 'the one curl over!!!!!!! next ...'."\r\n";
            return;
        }
        getData($next_id,$sqlHelper,$book_author);
    }elseif($res==0){
        echo 'exception';
        echo $next_id;
        exit;
    }
}

function getDetail($sqlHelper,$xxbookid,$next_id){
//    global $next_id;
    global $book_id;
    $curl=new Curl("http://m.xxsy.net/Page/Info?stat_page=1_detail_p11&bookid={$xxbookid}");
    $curl->setGetOpt(true,'','');
    $data=$curl->exec();
    $pattern='/bookpic\">\s*<img\ssrc=\"http:\/\/[0-9a-z\.\/\-]+\/[0-9a-z\.\/\-]+\.([0-9a-z\.\/\-])+/u';
    preg_match($pattern,$data,$matches);
    $bookpic=substr(strrchr($matches[0],'"'),1);
//    echo $bookpic;

    $pattern='/bookname\">[\x{4e00}-\x{9fa5}，,：:"”；0-9]*/u';
    preg_match($pattern,$data,$matches);
    $bookname=substr(strrchr($matches[0],'>'),1);
//    echo $bookname;

    $pattern='/author\">\s*<span>[\x{4e00}-\x{9fa5}0-9]*/u';
    preg_match($pattern,$data,$matches);
    $book_author=substr(strrchr($matches[0],'>'),1);
//    echo $author;

    $pattern='/intro\">\s*[\x{4e00}-\x{9fa5}<p><\/p>？，“。：！]+/u';
    preg_match($pattern,$data,$matches);
    $book_desc=substr(strchr($matches[0],'>'),1);
//    echo $book_desc;

    $pattern='/label_col\">[\x{4e00}-\x{9fa5}]*/u';
    preg_match_all($pattern,$data,$matches);
    $book_sign1=null;
    foreach($matches[0] as $key => $value){
        $book_sign=substr(strchr($value,'>'),1);
        $sql="insert into xs_sign2book values (null,'$book_sign','$book_id')";
        $sqlHelper->dml($sql);
        $book_sign1.=$book_sign." ";
    }
//    echo $book_sign;

    $pattern='/workdetlist\">\\d+(\\.\\d+)?/u';
    preg_match($pattern,$data,$matches);
    $booklen=substr(strchr($matches[0],'>'),1)*10000;
//    echo $booklen*10000;
    $last_update=date('Y-m-d H:i:s');
    $sql="insert into xs_book values ($book_id,'$bookname','$bookpic','','$book_desc','$book_sign1','$book_author',2,'$last_update','$booklen','有目录',1)";
    $res=$sqlHelper->dml($sql);
    if($res){
        getData($next_id,$sqlHelper,$book_author);
    }else{
        echo 'error!!!!';
        exit;
    }
}
function getFirstId($xxbook_id,$sqlHelper){
    $curl=new Curl("http://m.xxsy.net/Page/Content?stat_page=1_reading_p3&bookid={$xxbook_id}");
    $curl->setGetOpt(true,'','');
    $data=$curl->exec();
    $pattern='/_cid\s=\sparseInt\([0-9]*/u';
    preg_match($pattern,$data,$matches);
    $firstId=substr(strrchr($matches[0],'('),1);
    if(!empty($firstId)){
        getDetail($sqlHelper,$xxbook_id,$firstId);
    }
}
function core_exec($xxbook_id,$sqlHelper){
     getFirstId($xxbook_id,$sqlHelper);
}
//foreach($xsbookIdArr as $key => $value){
//    core_exec($value,$sqlHelper);
//    $book_id++;
//}


function getXXbookId($index,$sqlHelper){
    global $book_id;
    global $start_time;
    $curl=new Curl("http://m.xxsy.net/InfoList/GetCategory?uid=102&type=0&index={$index}");
    $curl->setGetOpt(true,'','');
    $data=$curl->exec();
    $pattern='/bookid=[0-9]*\sid=""/u';
    preg_match_all($pattern,$data,$matches);
    var_dump($matches);
//    foreach($matches[0] as $key => $value){
//        $start_time=microtime(true);
//       core_exec(substr(strrchr($value,'='),1),$sqlHelper);
//       $book_id++;
//    }

}


function getOKXXbookId($index,$sqlHelper){
    global $book_id;
    global $start_time;
    $curl=new Curl("http://m.xxsy.net/InfoList/GetCategory?uid=102&type=0&index={$index}");
    $curl->setGetOpt(true,'','');
    $data=$curl->exec();
    $pattern='/simg\/[0-9]*.jpg\"\s\/>\s*<div\sclass=\"poplabel/u';
    preg_match_all($pattern,$data,$matches);
    foreach($matches[0] as $key => $value){
        $pattern='/[0-9]+/u';
        preg_match($pattern,$value,$matche);
        $start_time=microtime(true);
        core_exec($matche[0],$sqlHelper);
        $book_id++;
    }

}
for($i=10;$i<50;$i++){
    getOKXXbookId($i,$sqlHelper);
}
