<?php
/**
 * Created By 1
 * 努力努力再努力！！！！！
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/7/17 - 17:54
 **/
header("content:application/json; charset=uft-8");
require_once __DIR__ . '/../vendor/autoload.php';

use smalls\app\controller\WatermarkController;

/**
 * @Author Smalls
 * 我这边做了很简单的一些制作
 * 只是很简单的操作，而且我们网站的根目录要设置到public里面，防止vendor、app直接暴露导致程序遭到非法攻击
 */
$url = $_POST['url'];
$url = getJustUrl($url);
if ($url == '') {
    $url = 'https://v.douyin.com/k3Jevf43/';
}
$watermarkObj = new WatermarkController();
$result       = $watermarkObj->parseVideo($url);

echo json_encode($result);

function getJustUrl($inUrlText)
{
    $parttern = "/http[s]?:\/\/(?:[a-zA-Z]|[0-9]|[$-_@.&+]|[!*\(\),]|(?:%[0-9a-fA-F][0-9a-fA-F]))+/";
    preg_match($parttern, $inUrlText, $match);
    return count($match) > 0 ? $match[0] : '';
}