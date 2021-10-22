<?php
class parse_sources
{
    private $db     = null;
    private $qry    = [];
    private $title  = '';
    private $poster = '';
    private $tracks = [];
    private $original_qry   = [];
    private $sources        = [];
    private $sources_alt    = [];
    private $vast_link      = '';
    private $vast_type      = 'vast';
    private $vast_offset    = 'pre';
    private $vast_skip      = 5;
    private $vast_ads       = [];
    private $is_lb      = FALSE;
    private $lb_link    = '';
    private $lb_host    = '';
    private $lb_method  = 'direct';
    private $base_host  = '';
    private $expired    = 0;
    private $player_config = [];
    private $core;
    var $remote_ip  = '';
    var $user_agent = '';
    var $qry_string = '';
    var $real_user_agent = '';

    function __construct($qry = [])
    {
        global $db;

        $this->db = $db;
        $this->core = new \core();

        if (!empty($qry['host'])) {
            $this->expired = $this->core->timeout($qry['host']);
            $this->direct_hosts = $this->core->direct_host();
        }

        if (!empty($qry['poster'])) {
            $this->poster = $qry['poster'];
            unset($qry['poster']);
        } else {
            $this->poster = get_option('poster');
        }

        $this->original_qry = $qry;
        if (!empty($qry['sub'])) {
            $subs = filter_var($qry['sub'], FILTER_VALIDATE_URL) ? $qry['sub'] : array_filter(explode('~', trim($qry['sub'], '~')));
            $langs = strpos($qry['lang'], '~') !== false ? array_filter(explode('~', trim($qry['lang'], '~'))) : $qry['lang'];
            if (filter_var($subs, FILTER_VALIDATE_URL)) {
                $lang = is_array($langs) ? $langs[0] : $langs;
                $this->tracks[] = [
                    'file' => $subs,
                    'label' => $lang,
                    'default' => true
                ];
            } else {
                $where = 'WHERE';
                foreach ($subs as $s) {
                    $where .= ' id = ? OR ';
                }
                $where = rtrim(trim($where), 'OR');
                $man = $db->prepare("SELECT `host`, `file_name`, `language` FROM `tb_subtitle_manager` $where");
                $man->execute($subs);
                $rows = $man->fetchAll(\PDO::FETCH_ASSOC);
                if ($rows) {
                    foreach ($rows as $row) {
                        $this->tracks[] = [
                            'file' => rtrim($row['host'], '/') . '/subtitles/' . $row['file_name'],
                            'label' => $row['language']
                        ];
                    }
                    if (!empty($this->tracks)) $this->tracks[0]['default'] = true;
                }
            }
        }

        // saring vast publik ke dalam variabel (khusus untuk admin)
        if (is_admin()) {
            $this->vast_link = !empty($qry['vast']) ? $qry['vast'] : '';
            $this->vast_type = !empty($qry['client']) ? $qry['client'] : '';
            $this->vast_ofset = !empty($qry['offset']) ? $qry['offset'] : '';
            $this->vast_skip = !empty($qry['adskip']) ? $qry['adskip'] : '';
        }

        // remove dari query agar tidak mengganggu proses lainnya
        unset($qry['vast']);
        unset($qry['client']);
        unset($qry['offset']);
        unset($qry['adskip']);

        $this->newquery = http_build_query($qry);

        // query baru
        $this->qry = $qry;

        $this->base_host = strtr(rtrim(BASE_URL, '/'), ['https:' => '', 'http:' => '', '//www.' => '']) . '/';
    }

    function is_load_balancer()
    {
        try {
            $host   = htmlspecialchars(parse_url(BASE_URL, PHP_URL_HOST));
            $lb     = $this->db->query("SELECT `id` FROM `tb_loadbalancers` WHERE `status` = 1 AND (`link` LIKE 'http://$host%' OR `link` LIKE 'https://$host%')");
            $rows   = $lb->fetchAll(\PDO::FETCH_ASSOC);
            return !empty($rows);
        } catch (\PDOException | \Exception $e) {
            error_log('is_load_balancer: ' . $e->getMessage());
        }
        return FALSE;
    }

    private function get_load_balancer()
    {
        try {
            $lb = $this->db->query("SELECT link FROM tb_loadbalancers WHERE `status` = 1");
            $rows = $lb->fetchAll(\PDO::FETCH_ASSOC);
            if ($rows) {
                $rows = array_column($rows, 'link');
                $key = array_rand($rows);
                return $rows[$key];
            }
        } catch (\PDOException  | \Exception $e) {
            error_log('get_load_balancer: ' . $e->getMessage());
        }
        return FALSE;
    }

    private function get_load_balancers()
    {
        try {
            $lb = $this->db->query("SELECT link FROM tb_loadbalancers WHERE `status` = 1");
            $rows = $lb->fetchAll(\PDO::FETCH_ASSOC);
            if ($rows) {
                $rows = array_column($rows, 'link');
                return $rows;
            }
        } catch (\PDOException  | \Exception $e) {
            error_log('get_load_balancer: ' . $e->getMessage());
        }
        return [];
    }

    private function get_database_qry()
    {
        $data = [];
        $vidClass = new \videos();
        $getVideo = $vidClass->get($this->qry['id']);
        if ($getVideo) {
            // ambil title
            $this->title = $getVideo['title'];

            // ambil subtitle
            if (empty($this->tracks)) {
                $tracks = [];
                $subtitles = $vidClass->get_subtitles($this->qry['id']);
                if (!empty($subtitles)) {
                    foreach ($subtitles as $st) {
                        $tracks[] = [
                            'file' => $st['file'],
                            'label' => htmlentities($st['label'])
                        ];
                    }
                    if (!empty($tracks)) {
                        $tracks[0]['default'] = true;
                    }
                }
                $this->tracks = $tracks;
            }

            // buat query baru hanya untuk host & id videonya
            $data = [
                'host'  => $getVideo['host'],
                'id'    => $getVideo['host_id'],
                'ahost' => $getVideo['ahost'],
                'aid'   => $getVideo['ahost_id']
            ];
            return $data;
        }
        return FALSE;
    }

    private function get_sources_load_balancer($download = false)
    {
        if (!empty($this->base_host)) {
            $apiQry = $this->original_qry;
            $apiQry['origin'] = $this->base_host;
            $apiQry['download'] = $download;

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https:' : 'http:';
            $hostLink = $scheme . $this->lb_host;
            $balancerLink = $hostLink . 'api.php?' . encode(http_build_query($apiQry));

            $host = parse_url($hostLink, PHP_URL_HOST);
            $port = parse_URL($hostLink, PHP_URL_PORT);
            if (empty($port)) {
                $port = parse_url($hostLink, PHP_URL_SCHEME) == 'https' ? 443 : 80;
            }
            $ipv4 = gethostbyname($host);
            $resolveHost = implode(":", array($host, $port, $ipv4));

            session_write_close();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $balancerLink);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RESOLVE, [$resolveHost]);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_USERAGENT, $this->real_user_agent);
            curl_setopt($ch, CURLOPT_REFERER, $scheme . '://' . $this->base_host);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if (!$err) {
                $json = json_decode($response, TRUE);
                if ($json['status'] === 'ok') {
                    if (empty($this->tracks)) $this->tracks = $json['tracks'];
                    if (empty($this->title)) $this->title = $json['title'];
                    if (empty($this->poster)) $this->poster = $json['poster'];
                    if (empty($this->sources)) $this->sources = $json['sources'];
                    if (empty($this->sources_alt)) $this->sources_alt = $json['sources_alt'];

                    $default = $json;
                    $alternative = $json;
                    $alternative['sources'] = $json['sources_alt'];
                    return array(
                        'default' => $default,
                        'alternative' => $alternative
                    );
                }
            }
        }
        return FALSE;
    }

    private function get_original_sources()
    {
        if (!empty($this->qry['sub']) && empty($this->tracks)) {
            $tracks[] = [
                'file' => $this->qry['sub'],
                'label' => htmlspecialchars($this->qry['lang']),
                'default' => TRUE
            ];
            $this->tracks = $tracks;
        }

        if (!empty($this->qry['id']) && !empty($this->qry['host'])) {
            $this->core->set_query($this->qry);
            $this->core->set_ip($this->remote_ip);
            $this->core->set_ua($this->user_agent);
            return $this->core->result();
        }
        return FALSE;
    }

    private function get_download_sources($qry = [], $alt = false)
    {
        global $InstanceCache;

        if ($alt && !empty($qry['aid'])) {
            $host = 'ahost';
            $id = 'aid';
        } else {
            $host = 'host';
            $id = 'id';
        }
        $cl = FALSE;
        $cl_cache = FALSE;
        if ($qry[$host] === 'streamsb') {
            $cl = new \streamsb($qry[$id]);
            $cl_cache = $InstanceCache->getItem('download_streamsb~' . $qry[$id]);
        } elseif ($qry[$host] === 'yadisk') {
            $cl = new \yadisk($qry[$id]);
            $cl_cache = $InstanceCache->getItem('download_yadisk~' . $qry[$id]);
        } elseif ($qry[$host] === 'okru') {
            $cl = new \okru($qry[$id]);
            $cl_cache = $InstanceCache->getItem('download_okru~' . $qry[$id]);
        } elseif ($qry[$host] === 'zplayer') {
            $cl = new \zplayer($qry[$id]);
            $cl_cache = $InstanceCache->getItem('download_zplayer~' . $qry[$id]);
        } elseif ($qry[$host] === 'aparat') {
            $cl = new \aparat($qry[$id]);
            $cl_cache = $InstanceCache->getItem('download_aparat~' . $qry[$id]);
        } elseif ($qry[$host] === 'dropbox') {
            $cl = new \dropbox($qry[$id]);
            $cl_cache = $InstanceCache->getItem('download_dropbox~' . $qry[$id]);
        } elseif ($qry[$host] === 'videobin') {
            $cl = new \videobin($qry[$id]);
            $cl_cache = $InstanceCache->getItem('download_videobin~' . $qry[$id]);
        } elseif ($qry[$host] === 'vidlox') {
            $cl = new \vidlox($qry[$id]);
            $cl_cache = $InstanceCache->getItem('download_vidlox~' . $qry[$id]);
        } elseif ($qry[$host] === 'vimeo') {
            $cl = new \vimeo($qry[$id]);
            $cl_cache = $InstanceCache->getItem('download_vimeo~' . $qry[$id]);
        }
        if ($cl) {
            $cl_bypass = ['streamsb', 'vidlox', 'zplayer'];
            if ($cl_cache->isHit()) {
                $cache_dl = $cl_cache->get();
                if (!empty($cache_dl['sources'])) {
                    if (in_array($qry[$host], $cl_bypass)) {
                        $sources = [];
                        $lb_link = !empty($this->lb_link) ? rtrim($this->lb_link, '/') : rtrim(BASE_URL, '/');
                        foreach ($cache_dl['sources'] as $sc) {
                            $hash = encode('host=' . $qry[$host] . '&id=' . $qry[$id] . '&res=' . $sc['label'] . '&email=');
                            $sources[] = [
                                'file' => $lb_link . '/videoplayback/?' . $hash,
                                'type' => 'video/mp4',
                                'label' => $sc['label']
                            ];
                        }
                        $cache_dl['sources'] = $sources;
                    }
                    return $cache_dl;
                }
            } else {
                $cl_results = [
                    'sources' => $cl->get_sources(true),
                    'title' => $cl->get_title(),
                    'image' => $cl->get_image(),
                    'tracks' => $this->tracks,
                    'referer' => $cl->get_referer()
                ];
                // save download cache
                $cl_cache->set($cl_results)->expiresAfter($this->expired);
                $cl_cache->addTag('downloads');
                $InstanceCache->save($cl_cache);

                if (!empty($cl_results['sources'])) {
                    if (in_array($qry[$host], $cl_bypass)) {
                        $sources = [];
                        $lb_link = !empty($this->lb_link) ? rtrim($this->lb_link, '/') : rtrim(BASE_URL, '/');
                        foreach ($cl_results['sources'] as $sc) {
                            $hash = encode('host=' . $qry[$host] . '&id=' . $qry[$id] . '&res=' . $sc['label'] . '&email=');
                            $sources[] = [
                                'file' => $lb_link . '/videoplayback/?' . $hash,
                                'type' => 'video/mp4',
                                'label' => $sc['label']
                            ];
                        }
                        $cl_results['sources'] = $sources;
                    }
                }

                return $cl_results;
            }
        }
        return FALSE;
    }

    function get_vast()
    {
        $vast_xml = get_option('vast_xml');
        if (empty($this->vast_link) && !filter_var($vast_xml, FILTER_VALIDATE_URL)) {
            // jika iklan vast ada
            // jika formatnya array
            $dbVast = @json_decode($vast_xml);
            $dbOffset = @json_decode(get_option('vast_offset'));
            $schedule = [];
            if (!empty($dbVast) && !empty($dbOffset)) {
                $cVast = count($dbVast);
                $cOffset = count($dbOffset);
                if ($cVast === $cOffset && $cVast > 1) {
                    for ($i = 0; $i < $cVast; $i++) {
                        if (!empty($dbVast[$i])) {
                            $schedule[] = [
                                'offset' => $dbOffset[$i],
                                'tag'    => $dbVast[$i]
                            ];
                        }
                    }
                    if (!empty($schedule)) {
                        $this->vast_ads = [
                            'client'    => get_option('vast_client'),
                            'schedule'    => $schedule,
                            'skipoffset' => intval(get_option('vast_skip')),
                            'skipmessage' => 'Skip XX',
                            'conditionaladoptout' => true,
                            'creativeTimeout' => 5000,
                            'loadVideoTimeout' => 5000,
                            'vastLoadTimeout' => 5000,
                            'placement' => 'interstitial',
                            'preloadAds' => true
                        ];
                        return $this->vast_ads;
                    }
                } else {
                    if (!empty($dbVast[0]) && filter_var($dbVast[0], FILTER_VALIDATE_URL)) {
                        $this->vast_ads = [
                            'client'    => get_option('vast_client'),
                            'schedule'  => [
                                [
                                    'tag'   => $dbVast[0],
                                    'offset' => $dbOffset[0],
                                ]
                            ],
                            'skipoffset'    => intval(get_option('vast_skip')),
                            'skipmessage'   => 'Skip XX',
                            'conditionaladoptout' => true,
                            'creativeTimeout' => 5000,
                            'loadVideoTimeout' => 5000,
                            'vastLoadTimeout' => 5000,
                            'placement' => 'interstitial',
                            'preloadAds' => true
                        ];
                        return $this->vast_ads;
                    }
                }
            }
        } else {
            // jika iklan vast dimasukkan pada generator publik
            if (!empty($this->vast_link) && filter_var($this->vast_link, FILTER_VALIDATE_URL)) {
                $this->vast_ads = [
                    'client'    => $this->vast_type,
                    'schedule'  => [
                        [
                            'tag'       => $this->vast_link,
                            'offset'    => $this->vast_offset
                        ]
                    ],
                    'skipoffset'    => $this->vast_skip,
                    'skipmessage'   => 'Skip XX'
                ];
                return $this->vast_ads;
            }
        }
        return FALSE;
    }

    private function save_public_video($update_db = true)
    {
        $save_public = filter_var(get_option('save_public_video'), FILTER_VALIDATE_BOOLEAN);
        if (!empty($this->qry['host']) && !empty($this->qry['id'])) {
            if (!empty($_SESSION['user'])) {
                $user_id = $_SESSION['user']['id'];
            } else {
                $user_id = get_option('public_video_user');
                $user_id = !empty($user_id) ? intval($user_id) : 1;
            }
            $videos = new \videos();
            $get = $videos->get_by('host_id', trim($this->qry['id']));
            if ($save_public && $update_db) {
                if (!empty($get['id'])) {
                    // update
                    $title = !empty($get['title']) ? $get['title'] : (!empty($this->player_config['title']) ? $this->player_config['title'] : '');
                    $title = trim(substr($title, 0, 100));
                    $videos->update([
                        'id' => $get['id'],
                        'host' => trim($this->qry['host']),
                        'host_id' => trim($this->qry['id']),
                        'ahost' => !empty($this->qry['ahost']) ? trim($this->qry['ahost']) : '',
                        'ahost_id' => !empty($this->qry['aid']) ? trim($this->qry['aid']) : '',
                        'title' => $title,
                        'user_id' => $user_id,
                        'subtitle' => array_column($this->tracks, 'file'),
                        'language' => array_column($this->tracks, 'label'),
                        'updated' => time(),
                    ]);
                } else {
                    // new
                    $title = !empty($get['title']) ? $get['title'] : (!empty($this->player_config['title']) ? $this->player_config['title'] : '');
                    $title = trim(substr($title, 0, 100));
                    $videos->insert_anonymous([
                        'host' => trim($this->qry['host']),
                        'host_id' => trim($this->qry['id']),
                        'ahost' => !empty($qry['ahost']) ? trim($qry['ahost']) : '',
                        'ahost_id' => !empty($qry['aid']) ? trim($qry['aid']) : '',
                        'title' => $title,
                        'user_id' => $user_id,
                        'subtitle' => array_column($this->tracks, 'file'),
                        'language' => array_column($this->tracks, 'label'),
                        'added' => time(),
                    ]);
                }
            }
        }
    }

    private function sort_sources($player_config = [])
    {
        if (!empty($player_config)) {
            // gdrive/gphotos sort label
            if (!empty($player_config['sources'])) {
                $ddKey = array_search('Default', array_column($player_config['sources'], 'label'));
                $ddData = $player_config['sources'][$ddKey];
                unset($player_config['sources'][$ddKey]);
                array_unshift($player_config['sources'], $ddData);
            }

            if (!empty($player_config['sources_alt'])) {
                $altKey = array_search('Default', array_column($player_config['sources_alt'], 'label'));
                $altData = $player_config['sources_alt'][$altKey];
                unset($player_config['sources_alt'][$altKey]);
                array_unshift($player_config['sources_alt'], $altData);
            }
        }
        return $player_config;
    }

    function get_config($download = false)
    {
        global $InstanceCache;

        $update_db = TRUE;
        $disable_hosts = !empty(get_option('disable_host')) ? json_decode(get_option('disable_host')) : [];
        if (!empty($this->qry['source']) && !empty($this->qry['id'])) {
            $cache = $InstanceCache->getItem('db~' . $this->qry['id']);
            if ($cache->isHit()) {
                $this->qry = $cache->get();
            } else {
                $this->qry = $this->get_database_qry();
            }
        }
        if (!empty($this->qry['host']) && in_array($this->qry['host'], $disable_hosts)) {
            $this->qry['id'] = '';
        }
        if (!empty($this->qry['ahost']) && in_array($this->qry['ahost'], $disable_hosts)) {
            $this->qry['aid'] = '';
        }
        if (!empty($this->qry['aid']) && empty($this->qry['id'])) {
            $this->qry['id'] = $this->qry['aid'];
            $this->qry['host'] = $this->qry['ahost'];
            $this->qry['aid'] = '';
            $this->qry['ahost'] = '';
            $update_db = FALSE;
        }

        if (!empty($this->qry['id']) || !empty($this->qry['aid'])) {
            $cacheDefault = !empty($this->qry['host']) ? $this->core->timeout($this->qry['host']) : 60;
            $cacheAlternative = !empty($this->qry['ahost']) ? $this->core->timeout($this->qry['ahost']) : 60;
            $cachetime = $cacheDefault >= $cacheAlternative ? $cacheDefault : $cacheAlternative;

            $qry = $this->qry;
            if (!empty($qry['subs'])) {
                $this->tracks = $qry['subs'];
            }
            unset($qry['subs']);
            ksort($qry);

            // ambil cache embed
            if (in_array($qry['host'], $this->core->ipauth_host()) || (!empty($qry['ahost']) && in_array($qry['ahost'], $this->core->ipauth_host()))) {
                $cacheName = substr(preg_replace('/[^a-zA-Z0-9]+/', '', implode('_', $qry)), 0, 200) . '~' . $this->remote_ip;
            } else {
                $cacheName = substr(preg_replace('/[^a-zA-Z0-9]+/', '', implode('_', $qry)), 0, 200);
            }
            $cacheFile = BASE_DIR . 'cache/embed/' . $cacheName . '.json';
            if (file_exists($cacheFile) && time() - $cachetime <= filemtime($cacheFile) && !$download) {
                $player_config = @file_get_contents($cacheFile);
                $player_config = json_decode($player_config, true);
                if (!empty($player_config['sources']) || !empty($player_config['sources_alt'])) {
                    $player_config = $this->sort_sources($player_config);
                    $this->player_config = $player_config;
                    if (!is_word_blacklisted($player_config['title'])) {
                        // save data
                        $this->save_public_video($update_db);
                        return $player_config;
                    } else {
                        return FALSE;
                    }
                }
            }

            // get load balancer
            $this->is_lb = $this->is_load_balancer();
            if (!$this->is_lb) {
                $this->lb_link = $this->get_load_balancer();
                $balancer_host = strtr(rtrim($this->lb_link, '/'), ['https:' => '', 'http:' => '', '//www.' => '']);
                $balancer_host = explode('embed.php', $balancer_host);
                $this->lb_host = rtrim($balancer_host[0], '/') . '/';
                if (!empty(get_option('load_balancer_methods'))) {
                    $this->lb_method = get_option('load_balancer_methods');
                }
            }

            $default = [
                'title' => '',
                'image' => '',
                'sources' => []
            ];
            $alternative = [
                'title' => '',
                'image' => '',
                'sources' => []
            ];

            // ambil data video
            $result = [];
            if (!empty($qry['host']) && !empty($qry['id'])) {
                if (!$this->is_lb && !empty($this->lb_link) && $this->base_host !== $this->lb_host) {
                    $result = $this->get_sources_load_balancer();
                } else {
                    $result = $this->get_original_sources();
                }
            }
            if ($result) {
                $default = $result['default'];
                $alternative = $result['alternative'];

                if (empty($this->title)) $this->title = !empty($default['title']) ? $default['title'] : $alternative['title'];
                if (empty($this->poster)) $this->poster = !empty($default['image']) ? $default['image'] : $alternative['image'];
                if (empty($this->sources)) $this->sources = $default['sources'];
                if (empty($this->sources_alt)) $this->sources_alt = $alternative['sources'];

                if (!is_word_blacklisted($this->title)) {
                    if (!empty($this->sources) || !empty($this->sources_alt)) {
                        $player_config = [
                            'loadbalancer'  => [
                                'method'    => $this->lb_method,
                                'host'      => $this->lb_host
                            ],
                            'query_array'   => $qry,
                            'query'         => $this->qry_string,
                            'remote_ip'     => $this->remote_ip,
                            'user_agent'    => $this->user_agent,
                            'title'         => $this->title,
                            'poster'        => $this->poster,
                            'sources'       => $this->sources,
                            'sources_alt'   => $this->sources_alt,
                            'tracks'        => $this->tracks
                        ];

                        $this->player_config = $player_config;
                    }

                    // save data
                    $this->save_public_video($update_db);

                    // download mode for hls/mpd
                    if ($download) {
                        $result = [];
                        $default = $this->get_download_sources($qry);
                        $alternative = $this->get_download_sources($qry, true);
                        $result['title'] = !empty($this->title) ? $this->title : $default['title'];
                        $result['tracks'] = !empty($this->tracks) ? $this->tracks : [];
                        $result['sources'] = $default ? $default['sources'] : $this->player_config['sources'];
                        $result['sources_alt'] = !empty($qry['aid']) && $alternative ? $alternative['sources'] : $this->player_config['sources_alt'];
                        $result = $this->sort_sources($result);
                        return $result;
                    } else {
                        @unlink($cacheFile);
                        if (!empty($this->player_config['sources']) || !empty($this->player_config['sources_alt'])) {
                            $defType = array_column($this->player_config['sources'], 'type');
                            $altType = array_column($this->player_config['sources_alt'], 'type');
                            if ((in_array('hls', $altType) || in_array('mpd', $altType)) && !in_array('hls', $defType) && !in_array('mpd', $defType)) {
                                $this->player_config['sources'] = $this->sources_alt;
                                $this->player_config['sources_alt'] = $this->sources;
                            }
                            $this->player_config = $this->sort_sources($this->player_config);
                            @file_put_contents($cacheFile, json_encode($this->player_config, true));
                            return $this->player_config;
                        }
                    }
                } else {
                    return FALSE;
                }
            }
        }
        return FALSE;
    }

    function get_javascript(string $qs = '')
    {
        if (!empty($this->player_config)) {
            $vplayer    = get_option('player');
            $skin       = get_option('player_skin');
            $p2p        = get_option('p2p');
            $logo_file  = get_option('small_logo_file');
            $ad_blocker = get_option('block_adblocker');
            $directAds  = get_option('direct_ads_link');
            $download_button = get_option('enable_download_button');
            $visitads_onplay = get_option('visitads_onplay');
            $production_mode = get_option('production_mode');

            $player         = new \player();
            $player->title  = $this->player_config['title'];
            $player->poster = $this->player_config['poster'];
            $player->tracks = $this->player_config['tracks'];
            $player->sources    = $this->player_config['sources'];
            $player->vast_ads   = $this->get_vast();
            $player->sharelink  = BASE_URL . 'embed/?' . encode($this->player_config['query']);
            $sources_alt        = $this->player_config['sources_alt'];

            $playerjs = '
                localStorage.removeItem("retry");
                var loadScript = function(url){
                    var sc = document.createElement("script");
                    sc.type = "text/javascript";
                    sc.async = false;
                    sc.src = url;
                    sc.onload = function(){};
                    document.head.appendChild(sc);
                };
                var ninjaDecode = function(t) {
                    var e = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : null;
                    return null == e && (e = "2"), Array.from(t, (function(t, n) {
                        return String.fromCharCode(t.charCodeAt() ^ e.charCodeAt(n % e.length))
                    })).join("")
                };
            ';

            if (!empty($sources_alt)) {
                $error_handle = 'var sca = ' . json_encode($sources_alt, TRUE) . ';';
                // ninjastream alternative
                if (!empty($this->qry['ahost']) && $this->qry['ahost'] === 'ninjastream') {
                    $error_handle .= '
                        var ninjaFile = sca[0].file;
                        var ninjaLink = ninjaFile.split("~");
                        sca[0].file = ninjaDecode(ninjaLink[0]) + ninjaLink[1];
                    ';
                }
                $error_handle .= '
                    vp.load(
                        {"sources": sca}, 
                        {"tracks": ' . json_encode($player->get_tracks(), TRUE) . '}
                    );
                    vp.play();
                ';
            } else {
                $newQry = $this->qry;
                $newQry['origin'] = parse_url(BASE_URL, PHP_URL_HOST);
                $newQS = encode(http_build_query($newQry));

                $servers = $this->get_load_balancers();
                if (empty($servers)) $servers[] = BASE_URL;

                $error_handle = '
                    var po = vp.getPosition();
                    var retry = Math.round(localStorage.getItem("retry"));
                    var res = {};
                    var servers = ' . json_encode($servers) . ';
                    var xhttp = new XMLHttpRequest();

                    if(retry == null) retry = 0;
                    if(retry < servers.length || vp.getDuration() > 0){
                        retry += 1;
                        localStorage.setItem("retry", retry);
                        ld.style.display="block";
                        vd.style.display="none";

                        for(var i=0; i<servers.length; i++){
                            xhttp.onreadystatechange = function() {
                                if (this.readyState === 4) {
                                    if(this.status === 200){
                                        res = JSON.parse(this.responseText);
                                        if(res.status === "ok"){
                                            var script = document.createElement("script");
                                            script.innerHTML = res.result;
                                            document.body.appendChild(script);
                                            vp.seek(po);
                                        } else {
                                            vp.stop();
                                        }
                                    } else {
                                        vp.stop();
                                    }
                                    ld.style.display="none";
                                    vd.style.display="block";
                                }
                            };
                            xhttp.open("GET", servers[i] + "clear_cache.php?' . $newQS . '", true);
                            xhttp.send();
                        }
                    }
                ';
            }

            $jsConfig = json_encode($player->config());
            $playerjs .= '
                var vp = jwplayer("videoContainer");
                var config = ' . $jsConfig . ';
            ';

            if (filter_var($p2p, FILTER_VALIDATE_BOOLEAN)) {
                $playerjs .= '
                    loadScript("https://cdn.jsdelivr.net/npm/fast-text-encoding@1.0.3/text.min.js");
                    loadScript("https://cdn.jsdelivr.net/npm/p2p-media-loader-core@latest/build/p2p-media-loader-core.min.js");
                    loadScript("https://cdn.jsdelivr.net/npm/p2p-media-loader-hlsjs@latest/build/p2p-media-loader-hlsjs.min.js");
                    if(typeof p2pml !== "undefined"){
                        if(p2pml.hlsjs.Engine.isSupported()){
                            var engine = new p2pml.hlsjs.Engine({
                                loader: {
                                    trackerAnnounce: [
                                        "wss://tracker.openwebtorrent.com",
                                        "wss://tracker.files.fm:7073/announce",
                                        "wss://tracker.sloppyta.co:443/announce",
                                        "ws://tracker.files.fm:7072/announce",
                                        "ws://tracker.sloppyta.co:80/announce",
                                    ]
                                }
                            });
                            config.hlsjsConfig = {
                                liveSyncDurationCount: 6,
                                loader: engine.createLoaderClass()
                            }
                        }
                    }
                ';
            }

            // ninjastream default
            if (!empty($this->qry['host']) && $this->qry['host'] === 'ninjastream') {
                $playerjs .= '
                    var ninjaFile = config.sources[0].file;
                    var ninjaLink = ninjaFile.split("~");
                    config.sources[0].file = ninjaDecode(ninjaLink[0]) + ninjaLink[1];
                ';
            }

            $playerjs .= '
                var ld = document.getElementById("mContainer");
                var vd = document.getElementById("videoContainer");
                var timeElapse = "latestplay.' . md5($this->player_config['query']) . '";
                var lastTime = localStorage.getItem(timeElapse);
                var dResume = document.getElementById("resume");
                var prettySecond = function (s) {
                    var sec_num = parseInt(s, 10);
                    var hours   = Math.floor(sec_num / 3600);
                    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                    var seconds = sec_num - (hours * 3600) - (minutes * 60);

                    if (hours   < 10) {hours   = "0"+hours;}
                    if (minutes < 10) {minutes = "0"+minutes;}
                    if (seconds < 10) {seconds = "0"+seconds;}
                    return hours+":"+minutes+":"+seconds;
                };
                var resumePlayback = function(){
                    dResume.style.display="none";
                    vp.seek(lastTime);
                };
                vp.setup(config);
                vp.on("playAttemptFailed", function(e){' . $error_handle . '});
                vp.on("error", function(e){' . $error_handle . '});
                vp.on("ready", function(){
                    ld.style.display="none";
                    vd.style.display="block";
                    var mt = localStorage.getItem("jwplayer.mute");
                    var vl = localStorage.getItem("jwplayer.volume");
                    if (mt !== null && (vl === null || vl === 0)) {
                        vp.setMute(true);
                    } else if(vl !== null && vl > 0) {
                        vp.setVolume(Math.round(vl));
                    }
                    if("' . $vplayer . '" === "jwplayer" && ("' . $skin . '" === "netflix" || "' . $skin . '" === "hotstar")){
                        var ts = document.querySelector(".jw-slider-time");
                        ts.prepend(document.querySelector(".jw-text-elapsed"));
                        ts.append(document.querySelector(".jw-text-duration"));
                    }
                });
                vp.on("firstFrame", function() {
                    var firstFrame = JSON.stringify(this.qoe().firstFrame);
                    console.log("The player took "+firstFrame+"ms to get to the first video frame.");
                });
                vp.on("time", function(e) {
                    localStorage.setItem(timeElapse, Math.round(e.position));
                });
                vp.on("beforePlay", function(){
                    var list = document.getElementsByClassName("jw-button-container");
                    var rewind = document.querySelector("[button=\"rewind\"]");
                    var forward = document.querySelector("[button=\"forward\"]");
                    if(list.length){
                        list[0].insertBefore(rewind, list[0].childNodes[2]);
                        list[0].insertBefore(forward, list[0].childNodes[4]);
                    }
                    if(lastTime !== null && lastTime > vp.getPosition() && lastTime < vp.getDuration()){
                        dResume.style.display="block";
                        document.getElementById("timez").innerHTML=prettySecond(lastTime);
                    }
                });
                vp.once("complete", function(e) {
                    localStorage.removeItem(timeElapse);
                    window.parent.postMessage(JSON.stringify({
                        playback: {
                            status: "complete"
                        }
                    }), "*");
                });
            ';

            if (filter_var($logo_file, FILTER_VALIDATE_URL)) {
                $logo_link = get_option('small_logo_link');
                if (filter_var($logo_link, FILTER_VALIDATE_URL)) {
                    $open_link = 'window.open("' . $logo_link . '", "_blank");';
                } else {
                    $open_link = 'window.open("#");';
                }
                $playerjs .= 'vp.addButton("' . $logo_file . '", "", function(){' . $open_link . 'return true;}, "logo");';
            }

            // block adblocker
            if (filter_var($ad_blocker, FILTER_VALIDATE_BOOLEAN)) {
                $playerjs .= '
                    var adBlocker = function(){
                        vp.remove();
                        vd.innerHTML = \'<div style="text-align:center;width:100%;height:100%;padding:0;margin:0;background:#000;position:absolute;color:#fff;"><div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:90%"><img src="' . BASE_URL . 'assets/img/stop-sign-hand.webp" width="150" height="150"><h1 style="line-height:1.4;margin:10px 0;">Disable AdBlock!</h1><p style="line-height:1.6;font-size:1.2em;word-break:break-word;margin:0;">Please supporting us by disabling your ad blocker.</p></div></div>\';
                        ld.style.display="none";
                        vd.style.display="block";
                    };
                    if(window.canRunAds === undefined){
                        adBlocker();
                    }
                    justDetectAdblock.detectAnyAdblocker().then(function(detected) {
                        if(detected) adBlocker();
                    });
                    vp.on("adBlock", function(){
                        adBlocker();
                    });
                ';
            }

            // icon download
            $download = '<svg xmlns="http://www.w3.org/2000/svg" class="jw-svg-icon jw-svg-icon-download" viewBox="0 0 512 512"><path d="M412.907 214.08C398.4 140.693 333.653 85.333 256 85.333c-61.653 0-115.093 34.987-141.867 86.08C50.027 178.347 0 232.64 0 298.667c0 70.72 57.28 128 128 128h277.333C464.213 426.667 512 378.88 512 320c0-56.32-43.84-101.973-99.093-105.92zM256 384L149.333 277.333h64V192h85.333v85.333h64L256 384z"/></svg>';
            if ($skin === 'hotstar') {
                // icon rewind
                $rewind = '<svg xmlns="http://www.w3.org/2000/svg" class="jw-svg-icon jw-svg-icon-rewind" viewBox="0 0 24 24" focusable="false"><path d="M12.436 18.191c.557.595.665 1.564.167 2.215-.522.687-1.469.794-2.114.238a1.52 1.52 0 01-.12-.115l-6.928-7.393a1.683 1.683 0 010-2.271l6.929-7.393a1.437 1.437 0 012.21.093c.521.65.419 1.645-.148 2.25l-5.448 5.814a.55.55 0 000 .743l5.453 5.82h-.001zm4.648-6.563a.553.553 0 000 .744l3.475 3.709a1.683 1.683 0 01-.115 2.382c-.61.532-1.519.418-2.075-.175l-4.828-5.152a1.683 1.683 0 010-2.27l4.888-5.218c.56-.599 1.46-.632 2.056-.074.664.621.632 1.751.007 2.418l-3.409 3.636z" fill-rule="evenodd" clip-rule="evenodd"/></svg>';
                // icon forward
                $forward = '<svg xmlns="http://www.w3.org/2000/svg" class="jw-svg-icon jw-svg-icon-forward" viewBox="0 0 24 24" focusable="false"><path d="M11.564 18.19l5.453-5.818a.55.55 0 000-.743l-5.448-5.815c-.567-.604-.67-1.598-.148-2.249.536-.673 1.483-.757 2.115-.186.033.03.065.06.095.093l6.928 7.392a1.683 1.683 0 010 2.272L13.63 20.53a1.439 1.439 0 01-2.125.005 1.588 1.588 0 01-.109-.128c-.498-.65-.39-1.62.166-2.215h.001zm-4.647-6.562L3.508 7.992c-.624-.667-.657-1.797.007-2.418a1.436 1.436 0 012.056.074l4.888 5.217a1.683 1.683 0 010 2.271l-4.827 5.151c-.558.594-1.466.708-2.075.177-.647-.56-.745-1.574-.218-2.262.032-.043.066-.083.103-.122l3.475-3.708a.553.553 0 000-.744z" fill-rule="evenodd" clip-rule="evenodd"/></svg>';
            } else {
                // icon rewind
                $rewind = '<svg xmlns="http://www.w3.org/2000/svg" class="jw-svg-icon jw-svg-icon-rewind" viewBox="0 0 1024 1024" focusable="false"><path d="M455.68 262.712889l-67.072 79.644444-206.904889-174.08 56.775111-38.627555a468.48 468.48 0 1 1-201.216 328.817778l103.310222 13.141333a364.487111 364.487111 0 0 0 713.614223 139.605333 364.373333 364.373333 0 0 0-479.971556-435.541333l-14.904889 5.973333 96.312889 81.066667zM329.955556 379.505778h61.610666v308.167111H329.955556zM564.167111 364.088889c61.269333 0 110.933333 45.511111 110.933333 101.717333v135.566222c0 56.149333-49.664 101.660444-110.933333 101.660445s-110.933333-45.511111-110.933333-101.660445V465.749333c0-56.149333 49.664-101.660444 110.933333-101.660444z m0 56.490667c-27.249778 0-49.322667 20.252444-49.322667 45.226666v135.566222c0 24.974222 22.072889 45.169778 49.322667 45.169778 27.192889 0 49.265778-20.195556 49.265778-45.169778V465.749333c0-24.917333-22.072889-45.169778-49.265778-45.169777z" p-id="7377"></path></svg>';
                // icon forward
                $forward = '<svg xmlns="http://www.w3.org/2000/svg" class="jw-svg-icon jw-svg-icon-forward" viewBox="0 0 1024 1024" focusable="false"><path d="M561.948444 262.712889l67.015112 79.644444 206.961777-174.08-56.832-38.627555a468.48 468.48 0 1 0 201.216 328.817778l-103.310222 13.141333a364.487111 364.487111 0 0 1-713.557333 139.605333 364.373333 364.373333 0 0 1 479.971555-435.541333l14.904889 5.973333-96.369778 81.066667zM329.955556 379.505778h61.610666v308.167111H329.955556zM564.167111 364.088889c61.269333 0 110.933333 45.511111 110.933333 101.717333v135.566222c0 56.149333-49.664 101.660444-110.933333 101.660445s-110.933333-45.511111-110.933333-101.660445V465.749333c0-56.149333 49.664-101.660444 110.933333-101.660444z m0 56.490667c-27.249778 0-49.322667 20.252444-49.322667 45.226666v135.566222c0 24.974222 22.072889 45.169778 49.322667 45.169778 27.192889 0 49.265778-20.195556 49.265778-45.169778V465.749333c0-24.917333-22.072889-45.169778-49.265778-45.169777z" p-id="7407"></path></svg>';
            }

            // tombol rewind & forward
            $playerjs .= '
                vp.addButton(\'' . $rewind . '\', "Rewind 10 Seconds", function() {
                    var seek = 0, time = vp.getPosition() - 10;
                    seek = time <= 0 ? 0 : time;
                    vp.seek(seek);
                    return true;
                }, "rewind");
                vp.addButton(\'' . $forward . '\', "Forward 10 Seconds", function() {
                    var seek = 0, time = vp.getPosition() + 10;
                    seek = time <= 0 ? 0 : time;
                    vp.seek(seek);
                    return true;
                }, "forward");
            ';

            // tombol download
            if (filter_var($download_button, FILTER_VALIDATE_BOOLEAN)) {
                $path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
                $key = !empty($path[1]) ? trim($path[1], '?') : '';
                $uri = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $key;
                $playerjs .= '
                    vp.addButton(\'' . $download . '\', "Download video", function() {
                        var link = "' . BASE_URL . 'download/' . $uri . '";
                        window.open(link, "_blank");
                        return true;
                    }, "download");
                ';
            }

            // buka direct ads saat pertama kali di play
            if (filter_var($visitads_onplay, FILTER_VALIDATE_BOOLEAN) && filter_var($directAds, FILTER_VALIDATE_URL)) {
                $playerjs .= '
                    vp.once("play", function(){
                        window.open("' . $directAds . '", "_blank");
                    });
                ';
            }

            // tampilkan player
            if (filter_var($production_mode, FILTER_VALIDATE_BOOLEAN)) {
                $directLink = !empty($directAds) ? $directAds : 'https://google.com/';
                // debugger
                $playerjs .= '
                    devtoolsDetector.addListener(function(isOpen, detail) {
                        if (isOpen) {
                            console.clear();
                            window.top.location = "'. $directLink .'";
                        }
                    });
                    devtoolsDetector.launch();
                ';
                return jsObfustator($playerjs);
            } else {
                return $playerjs;
            }
        }
    }

    function get_load_balancer_method()
    {
        return $this->lb_method;
    }

    function get_load_balancer_host()
    {
        return $this->lb_host;
    }

    function get_query()
    {
        return $this->qry;
    }

    function get_title()
    {
        return $this->title;
    }

    function get_poster()
    {
        return $this->poster;
    }

    function get_tracks()
    {
        return $this->tracks;
    }

    function get_sources()
    {
        return $this->sources;
    }

    function get_sources_alt()
    {
        return $this->sources_alt;
    }
}
