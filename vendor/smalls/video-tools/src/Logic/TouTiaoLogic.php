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
 * Date：2020/6/10 - 14:00
 **/
class TouTiaoLogic extends Base
{

    protected $itemId;
    private $contents;


    public function setItemId()
    {
        //[https://m.toutiao.com/is/B1Y6ak7/](https://m.toutiao.com/is/B1Y6ak7/)
        if(strpos($this->url, "toutiao.com")) {
            $url = $this->redirects($this->url, [], [
                'User-Agent' => UserGentType::ANDROID_USER_AGENT,
            ]);
            preg_match('/video\/([0-9]+)\//i', $url, $match);
        } else {
            preg_match('/a([0-9]+)\/?/i', $this->url, $match);
        }
        if (CommonUtil::checkEmptyMatch($match)) {
            throw new ErrorVideoException("获取不到item_id参数信息");
        }
        $this->itemId = $match[1];
    }

    public function setContents()
    {
        $getContentUrl = 'https://m.365yg.com/i' . $this->itemId . '/info/';
        $contents = $this->get($getContentUrl, ['i' => $this->itemId], [
            'Referer'    => $getContentUrl,
            'User-Agent' => UserGentType::ANDROID_USER_AGENT
        ]);
        if (empty($contents['data']['video_id'])) {
            throw new ErrorVideoException("获取不到指定的内容信息");
        }
        $this->contents = $contents;
    }



    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->itemId;
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

    public function getVideoUrl(): string
    {
        if (empty($this->contents['data']['video_id'])) {
            return '';
        }

        if(strpos($this->url, "toutiao.com")) {
            $getContentUrl = 'http://i.snssdk.com/video/urls/1/toutiao/mp4/'.$this->contents['data']['video_id'];
            $contents = $this->get($getContentUrl, [], [
                'Referer'    => $getContentUrl,
                'User-Agent' => UserGentType::ANDROID_USER_AGENT
            ]);
            return base64_decode($contents['data']['video_list']['video_1']['main_url']);
        }

        return $this->redirects('http://hotsoon.snssdk.com/hotsoon/item/video/_playback/', [
            'video_id' => $this->contents['data']['video_id'],
        ], [
            'User-Agent' => UserGentType::ANDROID_USER_AGENT
        ]);
    }

    public function getVideoImage()
    {
        return isset($this->contents['data']['poster_url']) ? $this->contents['data']['poster_url'] : '';
    }

    public function getVideoDesc()
    {
        return isset($this->contents['data']['title']) ? $this->contents['data']['title'] : '';
    }

    public function getUsername()
    {
        return isset($this->contents['data']['media_user']['screen_name']) ? $this->contents['data']['media_user']['screen_name'] : '';
    }

    public function getUserPic()
    {
        return isset($this->contents['data']['media_user']['avatar_url']) ? $this->contents['data']['media_user']['avatar_url'] : '';
    }
}