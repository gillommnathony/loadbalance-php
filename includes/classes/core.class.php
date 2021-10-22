<?php
class core
{
    private $query = [];
    private $bypass = ['gofile', 'uptobox', 'streamable', 'bayfiles', 'yadisk', 'anonfile', 'uploadsmobi', 'filesim', 'streamtape', 'dood', 'zippyshare', 'dropbox', 'fembed', 'filerio', 'gdrive', 'hxfile', 'mixdropto', 'playtube', 'vidoza', 'yourupload', 'upstream', 'vidlox', 'okru', 'mp4upload', 'userscloud', 'mediafire', 'okstream'];
    private $direct = ['ninjastream', 'viu', 'soundcloud', 'amazondrive', 'rumble', 'blogger', 'direct', 'facebook', 'onedrive', 'racaty', 'youtube', 'megaup', 'googlephotos', 'vidmoly', 'vupto', 'vimeo', 'filesfm', 'hexupload', 'streamsb', 'zplayer', 'videobin', 'indishare'];
    private $ipAuth = ['soundcloud'];
    private $email = '';
    private $remote_ip = '';
    private $useragent = '';

    function __construct($query = [], $remote_ip = '', $ua = '')
    {
        $this->query = $query;
        $this->remote_ip = $remote_ip;
        $this->useragent = $ua;

        if ((!empty($_SERVER['HTTP_ORIGIN']) && parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_SCHEME) === 'http') ||
            (!empty($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME) === 'http')
        ) {
            $this->direct[] = 'solidfiles';
            $this->direct[] = 'hexupload';
        } else {
            $this->bypass[] = 'solidfiles';
            $this->bypass[] = 'hexupload';
        }

        if (!empty($query['email'])) {
            $this->email = $query['email'];
        }

        $bypass = get_option('bypass_host');
        if (!empty($bypass)) {
            $hosts = array_unique(array_merge($this->bypass, $this->direct));
            $bypass = json_decode($bypass, TRUE);
            $direct = [];
            foreach ($hosts as $v) {
                if (!in_array($v, $bypass)) {
                    $direct[] = $v;
                }
            }
            $this->bypass = $bypass;
            $this->direct = $direct;
        }
    }

    function set_query($qry = [])
    {
        $this->query = $qry;
    }

    function set_ip($ip = '')
    {
        $this->remote_ip = $ip;
    }

    function set_ua($ua = '')
    {
        $this->ua = $ua;
    }

    function result()
    {
        $result = [
            'default' => [
                'title' => '',
                'image' => '',
                'sources' => []
            ],
            'alternative' => [
                'title' => '',
                'image' => '',
                'sources' => []
            ],
        ];

        $qry = $this->query;
        if (!empty($qry)) {
            // default sources
            if (!empty($qry['host']) && !empty($qry['id'])) {
                $result['default'] = $this->parseResult($qry['host'], $qry['id']);
            }

            // alternative sources
            if (!empty($qry['ahost']) && !empty($qry['aid'])) {
                $result['alternative'] = $this->parseResult($qry['ahost'], $qry['aid']);
            }
        }
        return $result;
    }

    function ipauth_host()
    {
        return $this->ipAuth;
    }

    function bypass_host()
    {
        return $this->bypass;
    }

    function direct_host()
    {
        return $this->direct;
    }

    function timeout($host = 'gdrive')
    {
        if ($host === 'okstream') {
            // 11 hours (original 12 hours)
            return 39600;
        } elseif ($host === 'viu') {
            // 190 hours (original 192 hours)
            return 684000;
        } elseif ($host === 'racaty' || $host === 'filesim' || $host === 'dood') {
            // 7 hours (original 8 hours)
            return 25200;
        } elseif ($host === 'indishare') {
            // 19 hours (original 20 hours)
            return 68400;
        } elseif ($host === 'streamsb') {
            // 3 hours
            return 10800;
        } elseif ($host === 'dropbox') {
            // 1 hour
            return 3600;
        } elseif (in_array($host, $this->ipAuth)) {
            // 30 seconds
            return 30;
        }
        // default 3 hours
        return 13320;
    }

    private function parseSources($key = '', $sources = [], $ref = '', $email = '')
    {
        if (!empty($key) && !empty($sources)) {
            $query = explode('~', $key);
            $host = trim($query[0]);
            $id = trim($query[1]);
            $bypass = $this->bypass;

            $result = [];
            foreach ($sources as $data) {
                $dt = [];
                $dt['label'] = $data['label'];
                $dt['type'] = $data['type'];
                if (in_array($host, $bypass) || strpos($data['file'], 'iijj.nl') !== FALSE || strpos($data['file'], 'clipwatching') !== FALSE || strpos($data['file'], 'zplayer.live') !== FALSE || strpos($data['file'], 'videobin') !== FALSE || strpos($data['file'], 'sbvideocdn') !== FALSE || strpos($data['file'], 'moly.cloud') !== FALSE) {
                    if ($data['type'] === 'hls' || $data['type'] === 'mpd') {
                        $file = rawurlencode($data['file']);
                        $dt['file'] =  BASE_URL . 'playlist/?url=' . encode($file) . '&ref=' . encode($ref);
                    } else {
                        $hash = encode('host=' . $host . '&id=' . $id . '&res=' . $data['label'] . '&email=' . $email);
                        $dt['file'] = BASE_URL . 'videoplayback/?' . $hash;
                    }
                } else {
                    $dt['file'] = $data['file'];
                }
                $result[] = $dt;
            }
            return $result;
        }
        return $sources;
    }

    private function parseResult($host = '', $id = '')
    {
        global $InstanceCache;

        if (!empty($host) && !empty($id)) {
            $disabled_hosts = !empty(get_option('disable_host')) ? json_decode(get_option('disable_host'), TRUE) : [];
            if (!in_array($host, $disabled_hosts)) {
                if (in_array($host, $this->ipAuth) && !empty($this->remote_ip) && !empty($this->useragent)) {
                    $key = $host . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $id) . '~' . md5($this->remote_ip) . '~' . md5($this->useragent);
                } else {
                    $key = $host . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $id);
                }
                // cek cache
                $cache = $InstanceCache->getItem($key);
                if (!$cache->isHit()) {
                    // cek class host
                    $className = strtolower($host);
                    // cek class
                    if ($host === 'gdrive') {
                        $class = new $className($id, $this->email);
                    } else {
                        $class = new $className($id);
                    }

                    // jika cache tidak ditemukan
                    // ambil data video dari hosting
                    $sources = $class->get_sources();

                    if (!empty($sources)) {
                        // sorting sources
                        usort($sources, function ($item1, $item2) {
                            $lb1 = rtrim($item1['label'], 'p');
                            $lb2 = rtrim($item2['label'], 'p');
                            if (is_numeric($lb1) && is_numeric($lb2)) {
                                return $lb1 < $lb2 ? -1 : 1;
                            } else {
                                return strcmp($item1['label'], $item2['label']);
                            }
                        });

                        $title = $class->get_title();
                        $image = $class->get_image();
                        $referer = $class->get_referer();
                        $email = method_exists($class, 'get_email') ? $class->get_email() : '';

                        // timeout
                        $exTimeout = $this->timeout($host);

                        // simpan data video ke tmp
                        $cache->set([
                            'sources'   => $sources,
                            'referer'   => $referer,
                            'title'     => $title,
                            'screenshot' => $image,
                            'email'     => $email,
                        ])->expiresAfter($exTimeout);
                        $cache->addTag($host);
                        $InstanceCache->save($cache);

                        // encode host file
                        $sources = $this->parseSources($key, $sources, $referer, $email);

                        return [
                            'sources'   => $sources,
                            'title'     => $title,
                            'image'     => $image,
                        ];
                    }
                } else {
                    // jika cache ditemukan
                    $data = $cache->get();
                    if (!empty($data['sources'])) {
                        $sources = $data['sources'];
                        $sources = $this->parseSources($key, $sources, $data['referer'], $data['email']);
                        return [
                            'sources'   => $sources,
                            'title'     => $data['title'],
                            'image'     => $data['screenshot'],
                        ];
                    }
                }
            }
        }
        return [
            'sources'   => [],
            'title'     => '',
            'image'     => ''
        ];
    }
}
