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
 * Date：2020/6/10 - 16:41
 **/
class TaoBaoLogic extends Base
{

    private $contents;
    private $contentId;
    private $videoUrl;

    public function setContentId()
    {
        $html = $this->curl($this->url);
        preg_match('/contentId=(\d+)/', $html, $match);
        $this->contentId = $match[1];
    }

    public function setContents()
    {
        $contents = $this->taobao($this->url);
        $this->contents = $contents['data'];
    }

    function taobao($url)
    {
        $html = $this->curl($url);
        preg_match('/videoUrl=(.*?)&/', $html, $videoInfo);
        $this->setVideoUrl(urldecode($videoInfo[1]));
    }

    public function setVideoUrl($url)
    {
        $this->videoUrl = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getUsername()
    {
        return '';
    }

    public function getUserPic()
    {
        return '';
    }

    public function getVideoDesc()
    {
        return $this->contents['title'] == null ? '' : $this->contents['title'];
    }

    public function getVideoImage()
    {
        return $this->contents['cover'] == null ? '' : $this->contents['cover'];
    }

    public function getVideoUrl()
    {
        return $this->videoUrl;
    }

    private function curl($url, $headers = [])
    {
        $header = ['User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'];
        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
        if (!empty($headers)) {
            curl_setopt($con, CURLOPT_HTTPHEADER, $headers);
        } else {
            curl_setopt($con, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($con, CURLOPT_TIMEOUT, 10000);
        $result = curl_exec($con);
        return $result;
    }

    function getcookie()
    {
        $tmp_url = "https://market.m.taobao.com/app/tb-windmill-app/ishopping/index";//伪造来路
        $Browser = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36';//模拟UA  这里用的是浏览器的
        $cookie = "";//初始化cookie
        $headers = array('Content-type:application/x-www-form-urlencoded', 'Accept:application/json');//发送请求的header
        for ($j = 0; $j <= 2; $j++) {  //需要请求两次，因为第一次访问失败之后才会生成cookie
            $url = "https://h5api.m.taobao.com/h5/mtop.mediainteraction.video.detail/1.0/?appKey=12574478";//请求地址，必须带上这个默认的appkey
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 1);//输出头部信息，cookie就包含其中
            curl_setopt($ch, CURLOPT_REFERER, $tmp_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_USERAGENT, $Browser);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            $content = curl_exec($ch);
            echo $content;
            curl_close($ch);
            $_m_h5_tk = $this->get_word($content, '_m_h5_tk=', ';'); //取出_m_h5_tk
            $_m_h5_tk_enc = $this->get_word($content, '_m_h5_tk_enc=', ';');   //取出_m_h5_tk_enc
            if ($_m_h5_tk && $_m_h5_tk_enc) {
                return "_m_h5_tk_enc=" . $_m_h5_tk_enc . "; _m_h5_tk=" . $_m_h5_tk;
            }
        }
    }

    function get_word($html,$star,$end){
        $pat = '/'.$star.'(.*?)'.$end.'/s';
        if(!preg_match_all($pat, $html, $mat)) {
        }else{
            $wd= $mat[1][0];
        }
        return $wd;
    }

    function getjson(){
        $cookie = $this->getcookie();
        $tmp_url = "https://market.m.taobao.com/app/tb-windmill-app/ishopping/index";
        $Browser  = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36';
        $appKey= 12574478;
        $_m_h5_tk= get_word($cookie,'_m_h5_tk=', '_');//从cookie中取出_m_h5_tk，必须要去掉后面的部分
        $t =$this->getMillisecond();//生成时间戳
        $data ='{"contentId":" '.$this->contentId.'","source":"cainixihuansy_ng","extendParameters":"{\"page\":\"cainixihuansy_ng\",\"product_type\":\"videointeract\"}"}';//请求的数据
        $url_data = urlencode($data);//请求的数据编码后要拼接到地址上。
        $headers = array('Content-type:application/x-www-form-urlencoded','Accept:application/json');
        $sign=md5($_m_h5_tk."&".$t."&".$appKey."&".$data); //生成sign
        $url = "https://h5api.m.taobao.com/h5/mtop.taobao.content.detail.mix.detail.h5/1.0/?jsv=2.6.1&appKey=12574478&t=".$t."&sign=".$sign."&api=mtop.taobao.content.detail.mix.detail.h5&v=1.0&timeout=20000&data=".$url_data;
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_REFERER, $tmp_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $Browser);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
}