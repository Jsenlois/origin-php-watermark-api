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
        echo($this->feedId);
        $contents       = $this->post('https://h5.qzone.qq.com/webapp/json/weishi/WSH5GetPlayPage?t=0.4185745904612037&g_tk=', [
            'feedId' => $this->feedId,
        ], [
            'User-Agent' => UserGentType::ANDROID_USER_AGENT
        ]);
        echo (json_encode($contents));
        $this->contents = $contents;
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

    public function getVideoUrl()
    {
        return isset($this->contents['data']['feeds'][0]['video_url']) ? $this->contents['data']['feeds'][0]['video_url'] : '';
    }


    public function getVideoImage()
    {
        return isset($this->contents['data']['feeds'][0]['images'][0]['url']) ? $this->contents['data']['feeds'][0]['images'][0]['url'] : '';
    }

    public function getVideoDesc()
    {
        return isset($this->contents['data']['feeds'][0]['share_info']['body_map'][0]['desc']) ? $this->contents['data']['feeds'][0]['share_info']['body_map'][0]['desc'] : '';
    }

    public function getUsername()
    {
        return isset($this->contents['data']['feeds'][0]['poster']['nick']) ? $this->contents['data']['feeds'][0]['poster']['nick'] : '';
    }

    public function getUserPic()
    {
        return isset($this->contents['data']['feeds'][0]['poster']['avatar']) ? $this->contents['data']['feeds'][0]['poster']['avatar'] : '';
    }


}