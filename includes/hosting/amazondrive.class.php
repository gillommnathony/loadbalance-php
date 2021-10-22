<?php
class amazondrive
{
    public $name = 'Amazon Drive';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://www.amazon.com/drive/v1/shares/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = $id[0];

            $this->url .= $this->id . '?shareId=' . $this->id . '&resourceVersion=V2&ContentType=JSON&_=' . time();

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
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/amazon~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: www.amazon.com',
                'origin: https://www.amazon.com',
            ));
        }
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);

            if (!$err) {
                $data = json_decode($response, TRUE);
                if ($data['statusCode'] === 200) {
                    curl_setopt($this->ch, CURLOPT_URL, 'https://www.amazon.com/drive/v1/nodes/' . $data['nodeInfo']['id'] . '/children?asset=ALL&limit=1&searchOnFamily=false&tempLink=true&shareId=' . $this->id . '&offset=0&resourceVersion=V2&ContentType=JSON&_=' . time());
                    session_write_close();
                    $response = curl_exec($this->ch);
                    $err = curl_error($this->ch);

                    if (!$err) {
                        $data = json_decode($response, TRUE);
                        if ($data['count'] > 0) {
                            $this->status = 'ok';
                            $this->title = $data['data'][0]['name'];

                            $result = [];
                            if (!empty($data['data'][0]['assets'])) {
                                foreach ($data['data'][0]['assets'] as $vid) {
                                    if ($vid['status'] === 'AVAILABLE' && strpos($vid['contentProperties']['contentType'], 'video/') !== FALSE) {
                                        $result[] = [
                                            'file' => $vid['tempLink'] . '?ownerId=' . $vid['ownerId'],
                                            'type' => $vid['contentProperties']['contentType'],
                                            'label' => $vid['contentProperties']['video']['height'] . 'p'
                                        ];
                                    } elseif (strpos($vid['contentProperties']['contentType'], 'image/') !== FALSE) {
                                        $this->image = $vid['tempLink'] . '?ownerId=' . $vid['ownerId'];
                                    }
                                }
                            } else {
                                $result[] = [
                                    'file' => $data['data'][0]['tempLink'] . '?ownerId=' . $data['data'][0]['ownerId'],
                                    'type' => $data['data'][0]['contentProperties']['contentType'],
                                    'label' => $data['data'][0]['contentProperties']['video']['height'] . 'p'
                                ];
                            }
                            return $result;
                        }
                    }
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
