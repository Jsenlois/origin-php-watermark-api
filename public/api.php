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
$debugUrl = [
    'douyin'=>'https://v.douyin.com/k3FAT8D/',
    'huoshan' => 'https://share.huoshan.com/hotsoon/s/U4ZOtvklnz8/',
    'toutiao'=>'https://m.toutiao.com/is/B1Y6ak7/',
    'kuaishou'=>'https://v.kuaishou.com/Jtf8rr',
    'lsp' => 'https://www.pearvideo.com/detail_1777629?st=7',
    'meipai'=>'http://www.meipai.com/video/676/7014896128064769016?client_id=1089857302&utm_media_id=7014896128064769016&utm_source=meipai_share&utm_term=meipai_android&gnum=2882924031&utm_content=9457&utm_share_type=3',
    'ppgx'=>'https://h5.pipigx.com/pp/post/710734416345?zy_to=copy_link&share_count=1&m=18240bb6450e4f0f06477b14471ab5c6&app=&type=post&did=92aa19b72d41d2fa&mid=8104200827611&pid=710734416345',
    'ppx'=>'https://h5.pipix.com/s/ko7RnJD/',
    'weishi'=>'https://isee.weishi.qq.com/ws/app-pages/share/index.html?wxplay=1&id=7jHcjnrq01PgeCvsy&spid=4111219036352816865&qua=v1_and_weishi_8.88.0_588_312027000_d&chid=100081014&pkg=3670&attach=cp_reserves3_1000370011',
    'bz'=>'https://b23.tv/Ws7DFzw',
    'wb'=>'https://m.weibo.cn/6403692491/4862604331189245',
    'tb'=>'https://m.tb.cn/h.Um0RIEN?tk=tq3hdgq64jZ'
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
        $url = $debugUrl[$_GET['dp']];
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