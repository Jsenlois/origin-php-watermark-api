<?php
declare (strict_types=1);

namespace Smalls\VideoTools\Logic;

use Smalls\VideoTools\Exception\ErrorVideoException;

/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/6/10 - 13:24
 **/
class KuaiShouLogic extends Base
{
    
    private $contents;

    /**
     * @throws ErrorVideoException
     */
    public function setContents()
    {
        $contents = $this->kuaishou($this->url);
        if (isset($contents['code']) && $contents['code'] != 200) {
            throw new ErrorVideoException($contents['error_msg']);
        }
        $this->contents = $contents['data'];
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

    public function getUserPic()
    {
        return '';

    }


    function kuaishou($url) {
        $locs = get_headers($url, true) ['Location'];
        preg_match('/photoId=(.*?)\&/', $locs, $matches);
        $headers = array('Cookie: did=web_985111db253c4bc289ebb2c9361e6; didv=167488'.time(),
            'Referer: ' . $locs, 'Content-Type: application/json');
        $post_data = '{"photoId": "' . str_replace(['video/', '?'], '', $matches[1]) . '","isLongVideo": false}';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://v.m.chenzhongtech.com/rest/wd/photo/info');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_NOBODY, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $data = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($data, true);
        if ($json) {
            $arr = [
                'code' => 200,
                'msg' => '解析成功',
                'data' => [
                    'avatar' => $json['photo']['headUrl'],
                    'author' => $json['photo']['userName'],
                    'time' => $json['photo']['timestamp'],
                    'title' => $json['photo']['caption'],
                    'cover' => $json['photo']['coverUrls'][key($json['photo']['coverUrls']) ]['url'],
                    'url' => $json['photo']['mainMvUrls'][key($json['photo']['mainMvUrls']) ]['url'],
                ]
            ];
            return $arr;
        }
    }
}