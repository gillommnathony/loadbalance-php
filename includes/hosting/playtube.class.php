<?php
class playtube {
    public $name = 'PlayTube';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://playtube.ws/';
    private $ch;

    function __construct($id=''){
        if(!empty($id)){
            $id = explode('?', $id);
            $this->id = str_replace(['embed-', '.html'], '', $id[0]);
            $this->url .= 'embed-'. $this->id .'.html';
            
            session_write_close();
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_ENCODING, "");
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if(defined('CURLOPT_TCP_FASTOPEN')){
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR .'cookies/playtube~'. preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) .'.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: playtube.ws',
                'origin: https://playtube.ws'
            ));
        }
    }

    function get_sources(){
        if(!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                if($dom){
                    $source = '';
                    $scripts = $dom->find("script");
                    foreach($scripts as $script){
                        if(strpos($script->innertext, 'eval(function(p,a,c,k,e,d)') !== FALSE){
                            $source = $script->innertext;
                            break;
                        }
                    }
                    if(!empty($source)){
                        $unpacker = new \JavascriptUnpacker();
                        $decode = $unpacker->unpack($source);
                        if(preg_match('/file:"([^"]+)"/', $decode, $video)){
                            $this->status = 'ok';
                            $this->referer = $this->url;
                            
                            if(preg_match('/title:"([^"]+)"/', $decode, $title)){
                                $this->title = trim($title[1]);
                            }

                            if(preg_match('/image:"([^"]+)"/', $decode, $image)){
                                $this->image = trim($image[1]);
                            }

                            $result[] = [
                                'file' => $video[1],
                                'type' => 'hls',
                                'label'=> 'Original'
                            ];
                            return $result;
                        }
                    }
                }
            }
        }
        return [];
    }

    function get_status(){
        return $this->status;
    }

    function get_title(){
        return $this->title;
    }

    function get_image(){
        return $this->image;
    }

    function get_referer(){
        return $this->referer;
    }

    function get_id(){
        return $this->id;
    }

    function __destruct(){
        curl_close($this->ch);
    }
}

