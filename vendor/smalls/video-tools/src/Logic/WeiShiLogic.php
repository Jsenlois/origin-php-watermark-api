<?php
declare (strict_types=1);

namespace Smalls\VideoTools\Logic;

use Smalls\VideoTools\Enumerates\UserGentType;
use Smalls\VideoTools\Exception\ErrorVideoException;
use Smalls\VideoTools\Utils\CommonUtil;

/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/6/10 - 14:13
 **/
class WeiShiLogic extends Base
{

    private $feedId;
    private $contents;


    public function setFeedId()
    {
        //https://isee.weishi.qq.com/ws/app-pages/share/index.html?wxplay=1&id=7jHcjnrq01PgeCvsy&spid=4111219036352816865&qua=v1_and_weishi_8.88.0_588_312027000_d&chid=100081014&pkg=3670&attach=cp_reserves3_1000370011
        preg_match('/&id=(.*?)&/', $this->url, $match);
        if (CommonUtil::checkEmptyMatch($match)) {
            throw new ErrorVideoException("feed_id参数获取失败");
        }
        $this->feedId = $match[1];
    }

    public function setContents()
    {
        $contents = $this->weishi($this->url);
        $this->contents = $contents['data'];
    }

    function weishi($url) {
        //&id=7jHcjnrq01PgeCvsy&
        preg_match('/&id=(.*?)&/', $url, $id);
        if (strpos($url, 'h5.weishi') != false) {
            $arr = json_decode($this->curl('https://h5.weishi.qq.com/webapp/json/weishi/WSH5GetPlayPage?feedid=' . $id[1]), true);
        } else {
            $arr = json_decode($this->curl('https://h5.weishi.qq.com/webapp/json/weishi/WSH5GetPlayPage?feedid=' . $id[1]), true);
        }
        $video_url = $arr['data']['feeds'][0]['video_url'];
        if ($video_url) {
            $arr = [
                'code' => 200,
                'msg' => '解析成功',
                'data' => [
                    'author' => $arr['data']['feeds'][0]['poster']['nick'],
                    'avatar' => $arr['data']['feeds'][0]['poster']['avatar'],
                    'time' => $arr['data']['feeds'][0]['poster']['createtime'],
                    'title' => $arr['data']['feeds'][0]['feed_desc_withat'],
                    'cover' => $arr['data']['feeds'][0]['images'][0]['url'],
                    'url' => $video_url
                ]
            ];
            return $arr;
        }
    }

    private function curl($url, $headers = []) {
        $header = ['User-Agent:Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1'];
        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
        if (!empty($headers)) {
            curl_setopt($con, CURLOPT_HTTPHEADER, $headers);
        } else {
            curl_setopt($con, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($con, CURLOPT_TIMEOUT, 5000);
        $result = curl_exec($con);
        return $result;
    }

    /**
     * @return mixed
     */
    public function getFeedId()
    {
        return $this->feedId;
    }

    /**
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getUserPic()
    {
        return '';
    }

    public function getVideoUrl()
    {
        return $this->contents['url'];
    }

    public function getVideoImage()
    {
        return $this->contents['cover'];
    }

    public function getVideoDesc()
    {
        return $this->contents['title'];
    }

    public function getUsername()
    {
        return $this->contents['author'];

    }


}