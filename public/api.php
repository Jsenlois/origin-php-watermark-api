<?php
/**
 * Created By 1
 * 努力努力再努力！！！！！
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/7/17 - 17:54
 **/
header("content:application/json; charset=uft-8");
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin:' . $origin);
header('Access-Control-Allow-Credentials:' . 'true');
require_once __DIR__ . '/../vendor/autoload.php';

use smalls\app\controller\WatermarkController;
$fileNameMap = [
    'video/mp4' => "video.mp4",
];
/**
 * @Author Smalls
 * 我这边做了很简单的一些制作
 * 只是很简单的操作，而且我们网站的根目录要设置到public里面，防止vendor、app直接暴露导致程序遭到非法攻击
 */
$output = $_GET['output'];
if ($output == "download") {
    $Video = MyRequest($_GET['url'], "", "GET", "", "", "PC");
    $filename = $fileNameMap[$Video['headers']['Content-Type']];
    if($filename == '') {
        $filename = "file";
    }
    header("Content-Disposition: attachment;filename=".$filename);
    echo $Video['body']; //输出视频
} else {
    $data = $_POST['data'];
    $url = getJustUrl($data);
    if ($url == '') {
        $url = 'https://v.douyin.com/k3FAT8D/';
    }
    $watermarkObj = new WatermarkController();
    $result = $watermarkObj->parseVideo($url);
    $response = [
        'status' => 0,
        'msg' => "ok",
        'data' => $result['data']
    ];
    echo json_encode($response);
}

function getJustUrl($inUrlText)
{
    $parttern = "/http[s]?:\/\/(?:[a-zA-Z]|[0-9]|[$-_@.&+]|[!*\(\),]|(?:%[0-9a-fA-F][0-9a-fA-F]))+/";
    preg_match($parttern, $inUrlText, $match);
    return count($match) > 0 ? $match[0] : '';
}

/*
 * @Description: Web请求函数
 * @param: url 必填
 * @param: header 请求头 为空时使用默认值
 * @param: type 请求类型
 * @param: data 请求数据
 * @param: DataType 数据类型 分为1,2 1为数据拼接传参 2为json传参
 * @param: HeaderType 请求头类型 默认为PC请求头 值为PE时请求头为手机
 * @return: result
*/
function MyRequest($url, $header, $type, $data, $DataType, $HeaderType = "PC")
{
    //常用header
    //$header = "user-agent:" . 1 . "\r\n" . "referer:" . 1 . "\r\n" . "AccessToken:" . 1 . "\r\n" . "cookie:" . 1 . "\r\n";
    if (empty($header)) {
        if ($HeaderType == "PC") {
            $header = "user-agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36 Edg/88.0.705.63\r\n";
        } else if ($HeaderType == "PE") {
            $header = "user-agent:Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1 Edg/88.0.4324.150\r\n";
        }
    }
    if (!empty($data)) {
        if ($DataType == 1) {
            $data = http_build_query($data); //数据拼接
        } else if ($DataType == 2) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE); //数据格式转换
        }
    }
    $options = array(
        'http' => array(
            'method' => $type,
            "header" => $header,
            'content' => $data,
            'timeout' => 15 * 60, // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $headers = get_headers($url, true); //获取请求返回的header
    $ReturnArr = array(
        'headers' => $headers,
        'body' => $result
    );
    return $ReturnArr;
}