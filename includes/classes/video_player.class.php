<?php
class video_player
{
    private $videoHost  = '';
    private $videoId    = '';
    private $videoURL   = '';
    private $videoRes   = '';
    private $videoEmail = '';   // khusus untuk gdrive 
    private $videoTitle = '';
    private $cacheKey   = '';
    private $cacheExpires   = 0;
    private $cookieFile     = '';
    private $productionMode = FALSE;
    private $memoryFriendly = FALSE;
    private $host       = '';
    private $origin     = '';
    private $referer    = '';
    private $headers    = [];
    private $downloadwithHLS = ['streamsb', 'vidlox', 'zplayer'];
    private $ch;

    function __construct($query = '')
    {
        if (!empty($query)) {
            $decode = decode($query);
            parse_str($decode, $qry);

            $this->videoId      = preg_replace('/[^A-Za-z0-9\-]/', '', $qry['id']);
            $this->videoHost    = in_array($qry['host'], $this->downloadwithHLS) ? 'download_' . $qry['host'] : $qry['host'];
            $this->videoRes     = $qry['res'];
            if (!empty($qry['email'])) {
                $this->videoEmail   = $qry['email'];
            }
            $this->cacheKey     = $this->videoHost . '~' . $this->videoId;
            $this->cookieFile   = BASE_DIR . 'cookies/' . $this->cacheKey . '.txt';
            $this->productionMode = filter_var(get_option('production_mode'), FILTER_VALIDATE_BOOLEAN);
            $this->memoryFriendly = !empty(get_option('memory_friendly'));

            $core = new \core();
            $this->cacheExpires = $core->timeout($qry['host']);

            $this->get_cache_info();

            session_write_close();
            $this->ch = curl_init();
            if (!$this->productionMode) {
                $cacheStream = BASE_DIR . 'cache/streaming/curl~' . $this->cacheKey . '.txt';
                curl_setopt($this->ch, CURLOPT_STDERR, fopen($cacheStream, 'w+'));
                curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
            }
            curl_setopt($this->ch, CURLOPT_URL, $this->videoURL);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
            curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($this->ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_KEEPALIVE, 1);
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
        }
    }

    private function mimeType($ext = '')
    {
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'mkv' => 'video/x-matroska',
            'flv' => 'video/flash',
            '3gpp' => 'video/3gp',
            'ogg' => 'video/ogg',
            'quicktime' => 'video/quicktime',
            'raw' => 'video/raw',
            'VP8' => 'video/VP8',
            'webm' => 'video/webm'
        ];
        return !empty($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'video/mp4';
    }

    private function gdrive_auth()
    {
        $gda = new \gdrive_auth();
        if (empty($this->videoEmail)) {
            $users  = $gda->get_accounts();
            $key    = array_rand($users);
            $user   = $users[$key];
            $this->videoEmail = $user['email'];
        }
        $gda->set_email($this->videoEmail);
        $token = $gda->get_access_token();
        if ($token) {
            return 'authorization: ' . $token['token_type'] . ' ' . $token['access_token'];
        }
        return FALSE;
    }

    private function get_cache_info()
    {
        global $InstanceCache;

        $cache = $InstanceCache->getItem($this->cacheKey);
        if ($cache->isHit()) {
            // ambil data dari cache
            $data = $cache->get();

            // ambil direct link video
            $key = array_search($this->videoRes, array_column($data['sources'], 'label'));
            $url = $data['sources'][$key]['file'];

            $this->videoURL = $url;
            if (!empty($data['title'])) {
                $this->videoTitle = $data['title'];
            } elseif (strpos($this->videoURL, 'www.googleapis.com') === FALSE) {
                $title = explode('/', rtrim($url, '/'));
                $this->videoTitle = end($title);
            }

            // parse direct link
            if (!empty($url)) {
                $scheme = parse_url($url, PHP_URL_SCHEME);

                // ambil host
                $this->host = parse_url($url, PHP_URL_HOST);

                // ambil referer
                if (!empty($data['referer'])) {
                    $this->referer = $data['referer'];
                }

                // ambil origin
                if (!empty($data['referer'])) {
                    $this->origin = parse_url($this->referer, PHP_URL_SCHEME) . '://' . parse_url($this->referer, PHP_URL_HOST);
                } else {
                    $this->origin = $scheme . '://' . $this->host;
                }

                $gdrive = $this->gdrive_auth();
                if ($gdrive && strpos($this->videoURL, 'googleapis.com') !== FALSE) {
                    $headers[] = $gdrive;
                }

                // masukkan ke dalam headers
                $headers[] = 'Accept: */*';
                $headers[] = 'Accept-Encoding: identity;q=1, *;q=0';
                $headers[] = 'Accept-Language: id,id-ID;q=0.9,en;q=0.8';
                $headers[] = 'Cache-Control: no-cache';
                $headers[] = 'Connection: keep-alive';
                $headers[] = 'Host: ' . $this->host;
                $headers[] = 'Referer: ' . strtr($this->referer, ['vemax20' => 'femax20']);
                $headers[] = 'Origin: ' . strtr($this->origin, ['vemax20' => 'femax20']);
                // atur header
                $this->headers = $headers;
            }
        }
        return FALSE;
    }

    private function get_video_info()
    {
        global $InstanceCache;

        $key = 'video~' . $this->cacheKey . '~' . $this->videoRes;
        $cache = $InstanceCache->getItem($key);
        if ($cache->isHit()) {
            return $cache->get();
        } else {
            // ambil video info
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
            curl_setopt($this->ch, CURLOPT_HEADER, true);
            curl_setopt($this->ch, CURLOPT_NOBODY, true);
            session_write_close();
            curl_exec($this->ch);
            $type = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);
            $size = curl_getinfo($this->ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            // jika file video/*
            if ($status >= 200 && $status < 400 && $size > (5 * 1024)) {
                if (strpos($type, 'video/') !== FALSE || strpos($type, '/octet-stream') !== FALSE || strpos($type, '/x-download') !== FALSE) {
                    $data = [
                        'filesize' => $size,
                        'contentType' => $type
                    ];

                    // simpan data
                    $cache->set($data)->expiresAfter($this->cacheExpires);
                    $cache->addTag('mime_info');
                    $InstanceCache->save($cache);

                    return $data;
                }
            } else {
                $InstanceCache->deleteItem($key);
                return [
                    'filesize' => $size,
                    'contentType' => $type
                ];
            }
        }
        return FALSE;
    }

    function send($range = '')
    {
        $response = [];
        $size = 0;
        // ambil info video
        $file = $this->get_video_info();
        if ($file) {
            $size = $file['filesize'];
            $parts = pathinfo($this->videoTitle);
            if (strpos($this->videoURL, 'googleapis.com') !== FALSE && !empty($parts['extension'])) {
                $response['content-type'] = $this->mimeType($parts['extension']);
            } else {
                $response['content-type'] = $file['contentType'];
            }
        }

        if (!empty($range)) {
            // Parse field value
            list($specifier, $value) = explode('=', $range);

            // Can only handle bytes range specifier
            if ($specifier !== 'bytes') {
                $response['status'] = 400;
                return;
            }

            // Set start/finish bytes
            list($from, $to) = explode('-', $value);
            $to = !empty($to) ? $to : $size - 1;

            $response['status'] = 206;
            $response['Content-Range'] = sprintf('bytes %d-%d/%d', $from, $to, $size);
            $response['Content-Length'] = ($to - $from) + 1;

            curl_setopt($this->ch, CURLOPT_RANGE, $from . '-' . $to);
        } else {
            $response['status'] = 200;
            $response['Content-Length'] = $size;
        }

        if (!empty($this->videoTitle)) {
            $response['Content-Disposition'] = 'attachment; filename="' . trim(html_entity_decode(strtr($this->videoTitle, ['(cloned)' => '']))) . '"';
        } else {
            $response['Content-Disposition'] = 'attachment; filename="video.mp4"';
        }

        return $response;
    }

    function stream()
    {
        session_write_close();
        // Maximum buffer size of 20MB to help stabilize the connection (this is uncertain)
        curl_setopt($this->ch, CURLOPT_BUFFERSIZE, 2097152);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_NOBODY, false);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        if ($this->memoryFriendly) {
            // Don't load this file into memory, stream it to the browser instead
            curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, function ($curl, $body) {
                session_write_close();
                echo $body;
                return strlen($body);
            });
        }
        curl_exec($this->ch);
        if ($this->memoryFriendly) curl_close($this->ch);
    }

    function __destruct()
    {
        if (!$this->memoryFriendly) curl_close($this->ch);
    }
}
