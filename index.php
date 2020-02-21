<?php
require_once './vendor/autoload.php';
require_once './common.php';
$act = isset($_GET['act']) && !empty($_GET['act']) ? $_GET['act'] : 'login';
$http = new \tegic\Http();

if ($act == 'login') {
    $http->set(CURLOPT_REFERER, 'http://cet-bm.neea.cn/Home/QueryTestTicket');
    $http->connect('http://cet-ks.neea.edu.cn/cetset/welcome.jsp');
    $header = $http->getHeader();
    $cookie = $http->substr($header, 'JSESSIONID=', '; Path');
    require_once './template/login.html.php';
} elseif ($act == 'code') {
    header("Expires: Mon, 26 Jul 2012 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pramga: no-cache");
    header('Content-Type: image/jpeg');
    $http->header('Cookie', 'JSESSIONID=' . $_REQUEST['ck']);
    $result = $http->connect("http://cet-ks.neea.edu.cn/cetset/rdcode.bmp");
    echo $result;
} elseif ($act == 'query') {

    $data = [
        'ks_sfz' => $_POST['num'],
        'ks_xm' => iconv("UTF-8", "gbk//TRANSLIT", $_POST['username']),
        'randsn' => $_POST['code'],
    ];
    $http->header('Cookie', 'JSESSIONID=' . $_POST['ck']);
    $http->post($data);
    $result = $http->connect('http://cet-ks.neea.edu.cn/cetset/app/student.do?method=zkzcx');
    //校验码输入错误
    if (strpos($result, '校验码输入错误') !== false) {
        error('验证码错误');
    }
    //身份证或姓名不正确
    if (strpos($result, '身份证或姓名不正确') !== false) {
        error('身份证或姓名不正确');
    }
    //你的准考证号为
    if (strpos($result, '你的准考证号为') !== false) {
        $num = $http->substr($result, '你的准考证号为:', '</h2>');
        success(trim($num));
    }
    error('查询出错，请联系管理');
}

