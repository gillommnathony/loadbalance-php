<?php
class okru
{
    public $name = 'OK.ru';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://ok.ru/videoembed/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = trim($id[0]);

            $this->url .= $this->id;
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_ENCODING, "");
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/okru~' . $this->id . '.txt');
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: ok.ru',
                'origin: https://ok.ru',
            ));
        }
    }

    function get_sources($getMP4 = false)
    {
        session_write_close();
        $response = curl_exec($this->ch);
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($status >= 200 && $status < 400) {
            $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
            $attr = 'data-options';
            $options = $dom->find('div[data-module="OKVideo"]', 0);
            if (!empty($options)) {
                $json = json_decode(html_entity_decode($options->$attr), TRUE);
                $this->image = $json['poster'];

                $json = json_decode($json['flashvars']['metadata'], TRUE);
                if ($getMP4 && !empty($json['videos'])) {
                    $this->status = 'ok';
                    $this->referer = $this->url;
                    $this->title = $json['movie']['title'];

                    $resOri = ['mobile', 'lowest', 'low', 'sd', 'hd', 'full'];
                    $resNew = ['144p', '240p', '360p', '480p', '720p', '1080p'];

                    $result = [];
                    foreach ($json['videos'] as $video) {
                        $result[] = [
                            'file' => $video['url'],
                            'type' => 'video/mp4',
                            'label' => strtr($video['name'], $resOri, $resNew),
                        ];
                    }
                    return $result;
                } elseif (!empty($json['hlsManifestUrl'])) {
                    $this->status = 'ok';
                    $this->referer = $this->url;
                    $this->title = $json['movie']['title'];

                    $result[] = [
                        'file' => $json['hlsManifestUrl'],
                        'type' => 'hls',
                        'label' => 'Original',
                    ];
                    return $result;
                }
            }
        }
        return [];
    }

    function get_status()
    {
        return $this->status;
    }

    function get_title()
    {
        return $this->title;
    }

    function get_image()
    {
        return $this->image;
    }

    function get_referer()
    {
        return $this->referer;
    }

    function get_id()
    {
        return $this->id;
    }

    function __destruct()
    {
        curl_close($this->ch);
    }
}
