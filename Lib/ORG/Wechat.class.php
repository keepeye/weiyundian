<?php
class Wechat
{
    private $data = array();
    public function __construct($token)
    {
        $this->auth($token) || exit;
        if (IS_GET) {
            echo ($_GET['echostr']);
            exit;
        } else {
            $xml = file_get_contents("php://input");
            $xml = new SimpleXMLElement($xml);
            $xml || exit;
            foreach ($xml as $key => $value) {
                $this->data[$key] = strval($value);
            }
        }
    }
    public function request()
    {
        return $this->data;
    }
    public function response($content, $type = 'text', $flag = 0)
    {
        $this->data = array(
            'ToUserName' => $this->data['FromUserName'],
            'FromUserName' => $this->data['ToUserName'],
            'CreateTime' => NOW_TIME,
            'MsgType' => $type
        );
        $this->$type($content);
        $this->data['FuncFlag'] = $flag;
        $xml                    = new SimpleXMLElement('<xml></xml>');
        $this->data2xml($xml, $this->data);
        exit($xml->asXML());
    }
    
    private function get_real_url($url){
    	if(strpos($url, "http://") === 0){
    		return $url;
    	}
    	else{
    		return C("site_url").$url;
    	}
    }
    
    private function text($content)
    {
        $this->data['Content'] = $content;
    }
    private function music($music)
    {
        list($music['Title'], $music['Description'], $music['MusicUrl'], $music['HQMusicUrl']) = $music;
        $music["MusicUrl"]=$this->get_real_url($music["MusicUrl"]);
        $music["HQMusicUrl"]=$this->get_real_url($music["HQMusicUrl"]);
        $this->data['Music'] = $music;
    }
    private function news($news)
    {
        $articles = array();
        foreach ($news as $key => $value) {
            list($articles[$key]['Title'], $articles[$key]['Description'], $articles[$key]['PicUrl'], $articles[$key]['Url']) = $value;
            $articles[$key]['PicUrl']=$this->get_real_url($articles[$key]['PicUrl']);
            $articles[$key]['Url']=$this->get_real_url($articles[$key]['Url']);
            if ($key >= 9) {
                break;
            }
        }
        $this->data['ArticleCount'] = count($articles);
        $this->data['Articles']     = $articles;
    }
    private function data2xml($xml, $data, $item = 'item')
    {
        foreach ($data as $key => $value) {
            is_numeric($key) && $key = $item;
            if (is_array($value) || is_object($value)) {
                $child = $xml->addChild($key);
                $this->data2xml($child, $value, $item);
            } else {
                if (is_numeric($value)) {
                    $child = $xml->addChild($key, $value);
                } else {
                    $child = $xml->addChild($key);
                    $node  = dom_import_simplexml($child);
                    $node->appendChild($node->ownerDocument->createCDATASection($value));
                }
            }
        }
    }
    private function auth($token)
    {
        $data = array(
            $token,
            $_GET['timestamp'],
            $_GET['nonce'],
            
        );
        $sign = $_GET['signature'];
        sort($data);
        $signature = sha1(implode($data));
        $debug = "sign1:$sign \n sign2:$signature \n".var_export($data,true)."\n";
        file_put_contents("./sign.txt", $debug,FILE_APPEND);
        return true;
        //return $signature == $sign;
    }
}
