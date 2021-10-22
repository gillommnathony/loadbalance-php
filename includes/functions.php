<?php
require_once BASE_DIR . 'administrator/includes/conn.php';
require_once BASE_DIR . 'administrator/includes/functions.php';

session_write_close();

function autoloadClasses($class)
{
    $classFile = BASE_DIR . "includes/classes/$class.class.php";
    if (file_exists($classFile)) {
        require_once $classFile;
    }
}
function autoloadHosting($class)
{
    $classFile = BASE_DIR . "includes/hosting/$class.class.php";
    if (file_exists($classFile)) {
        require_once $classFile;
    }
}
function autoloadAdmin($class)
{
    $classFile = BASE_DIR . "administrator/includes/classes/$class.class.php";
    if (file_exists($classFile)) {
        require_once $classFile;
    }
}
spl_autoload_register("autoloadClasses");
spl_autoload_register("autoloadHosting");
spl_autoload_register("autoloadAdmin");

// buat direktori subtitles
if (!is_dir(BASE_DIR . 'subtitles')) {
    mkdir(BASE_DIR . 'subtitles', 0755, true);
    chmod(BASE_DIR . 'subtitles', 0755);
}

// buat direktori cookies
if (!is_dir(BASE_DIR . 'cookies')) {
    mkdir(BASE_DIR . 'cookies', 0755, true);
    chmod(BASE_DIR . 'cookies', 0755);
}

// buat direktori cache
if (!is_dir(BASE_DIR . 'cache')) {
    mkdir(BASE_DIR . 'cache', 0755, true);
    chmod(BASE_DIR . 'cache', 0755);
}

// buat direktori cache/streaming
if (!is_dir(BASE_DIR . 'cache/streaming')) {
    mkdir(BASE_DIR . 'cache/streaming', 0755, true);
    chmod(BASE_DIR . 'cache/streaming', 0755);
}

// buat direktori cache/embed
if (!is_dir(BASE_DIR . 'cache/embed')) {
    mkdir(BASE_DIR . 'cache/embed', 0755, true);
    chmod(BASE_DIR . 'cache/embed', 0755);
}

// buat direktori cache/playlist
if (!is_dir(BASE_DIR . 'cache/playlist')) {
    mkdir(BASE_DIR . 'cache/playlist', 0755, true);
    chmod(BASE_DIR . 'cache/playlist', 0755);
}

// phpfastcache
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;

try {
    if (extension_loaded('redis')) {
        $CacheInstance = 'redis';
        $CacheConfig = new RedisConfig([
            'host' => '127.0.0.1',
            'port' => 6379
        ]);
    } else {
        // buat direktori tmp
        if (!is_dir(BASE_DIR . 'tmp')) {
            mkdir(BASE_DIR . 'tmp', 0755, true);
            chmod(BASE_DIR . 'tmp', 0755);
        }
        $CacheInstance = extension_loaded('pdo_sqlite') ? 'sqlite' : 'files';
        $CacheConfig = new ConfigurationOption([
            'path' => BASE_DIR . 'tmp',
            'preventCacheSlams' => true,
            'cacheSlamsTimeout' => 30
        ]);
    }
    $InstanceCache = CacheManager::getInstance($CacheInstance, $CacheConfig);
} catch (\Exception $e) {
    // buat direktori tmp
    if (!is_dir(BASE_DIR . 'tmp')) {
        mkdir(BASE_DIR . 'tmp', 0755, true);
        chmod(BASE_DIR . 'tmp', 0755);
    }
    $CacheInstance = extension_loaded('pdo_sqlite') ? 'sqlite' : 'files';
    $InstanceCache = CacheManager::getInstance($CacheInstance, new ConfigurationOption([
        'path' => BASE_DIR . 'tmp',
        'preventCacheSlams' => true,
        'cacheSlamsTimeout' => 30
    ]));
}


function show_public_balancer()
{
    global $db;
    if (!is_null($db)) {
        $host = strtr(BASE_URL, ['https:' => '', 'http:' => '']);
        $lb = $db->prepare("SELECT `public` FROM tb_loadbalancers WHERE `status`=1 AND `link` LIKE '%$host'");
        $lb->execute();
        $data = $lb->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            if ($data[0]['public'] == 1) {
                return TRUE;
            }
        } else {
            return TRUE;
        }
    }
    return FALSE;
}

function domain_whitelisted()
{
    $domains = get_option('domain_whitelisted');
    if (!empty($domains)) {
        return explode("\n", strtr(trim($domains), ['www.' => '', "\r\n" => "\n"]));
    }
    return [];
}

function is_domain_whitelisted($ref = '')
{
    $referer = !empty($ref) ? ltrim(parse_url($ref, PHP_URL_HOST), 'www.') : '';
    if (!empty($referer)) {
        $domains = domain_whitelisted();
        if (!empty($domains)) return in_array($referer, $domains);
    }
    return TRUE;
}

function is_domain_blacklisted($ref = '')
{
    $referer = !empty($ref) ? ltrim(parse_url($ref, PHP_URL_HOST), 'www.') : '';

    if (!empty($referer)) {
        $domains = get_option('domain_blacklisted');
        if ($domains) {
            $domains = explode("\n", strtr(trim($domains), ['www.' => '', "\r\n" => "\n"]));
            return in_array($referer, $domains);
        }
    }
    return FALSE;
}

function is_referer_blacklisted($ref = '')
{
    if (!empty($ref)) {
        $trans = ['https://' => '', 'http://' => '', 'www.' => '', "\r\n" => "\n"];
        $referer = strtr(trim(rawurldecode($ref), '/'), $trans);
        $links = get_option('link_blacklisted');
        if ($links) {
            $links = explode("\n", strtr(trim($links, '/'), $trans));
            return in_array($referer, $links);
        }
    }
    return FALSE;
}

function is_word_blacklisted($str = '')
{
    if (!empty($str)) {
        $str = strtolower($str);
        $words = strtr(trim(strtolower(get_option('word_blacklisted'))), ["\r\n" => "\n"]);
        $words = explode("\n", $words);
        $words = array_unique($words);
        $words = array_filter($words, function ($a) {
            return !empty(trim($a));
        });
        $result = false;
        foreach ($words as $word) {
            if (strpos($str, $word) !== FALSE) {
                $result = true;
                break;
            }
        }
        return $result;
    }
    return FALSE;
}

function sitename()
{
    $dbsitename = get_option('site_name');
    if ($dbsitename) {
        return $dbsitename;
    }
    return 'GDPlayer';
}

function get_option($key = '')
{
    $opt = new \settings();
    if (!empty($key)) {
        return $opt->get($key);
    } else {
        return $opt->get();
    }
}

function set_option($key = '', $value = null)
{
    $opt = new \settings();
    if (!empty($key) && !is_null($value)) {
        return $opt->insert($key, $value);
    }
}

function current_user()
{
    $login = new \login();
    return $login->cek_login();
}

function is_admin()
{
    $user = current_user();
    return $user && intval($user['role']) === 0;
}

function is_anonymous()
{
    $anonymous = get_option('anonymous_generator');
    return filter_var($anonymous, FILTER_VALIDATE_BOOLEAN);
}

function error($status = 404)
{
    switch ($status) {
        case 403:
            return '<div class="row">
                <div class="col-12 text-center">
                    <div class="my-5">
                        <h1 class="h3 text-danger"><strong>402</strong> Unauthorized!</h1>
                        <h3 class="h4 text-secondary">This page can only be accessed by certain users.</h3>
                    </div>
                </div>
            </div>';
            break;
        default:
            return '<div class="row">
                <div class="col-12 text-center">
                    <div class="my-5">
                        <h1 class="h3 text-danger"><strong>404</strong> Page not found!</h1>
                        <h3 class="h4 text-secondary">The page you want to access was not found.</h3>
                    </div>
                </div>
            </div>';
            break;
    }
}

function get_host_status($host = '', $html = FALSE)
{
    $disabled_hosts = !empty(get_option('disable_host')) ? json_decode(get_option('disable_host'), TRUE) : [];
    if (!empty($host)) {
        $disabled = in_array($host, $disabled_hosts);
        if ($html) {
            if ($disabled) {
                return '<span class="text-danger"><i class="fa fa-ban fa-lg"></i><span class="ml-1">Disabled</span></span>';
            } else {
                return '<span class="text-success"><i class="fa fa-check-circle fa-lg"></i><span class="ml-1">Working</span></span>';
            }
        } else {
            return $disabled;
        }
    }
    return FALSE;
}

function hls_referer($url = '')
{
    if (strpos($url, 'upstreamcdn.co') !== FALSE) {
        return 'https://upstream.to/';
    } elseif (strpos($url, 'moly.cloud') !== FALSE) {
        return 'https://vidmoly.to/';
    } elseif (strpos($url, 'mycdn.me') !== FALSE) {
        return 'https://ok.ru/';
    } elseif (strpos($url, 'yandex.net') !== FALSE) {
        return 'https://yastatic.net/';
    } elseif (strpos($url, 'dropboxusercontent.com') !== FALSE) {
        return 'https://www.dropbox.com/';
    } elseif (strpos($url, 'sbvideocdn.com') !== FALSE) {
        return 'https://streamsb.net/';
    } elseif (strpos($url, 'videobin.co') !== FALSE) {
        return 'https://videobin.co/';
    } elseif (strpos($url, 'vidlox.me') !== FALSE) {
        return 'https://vidlox.me/';
    } elseif (strpos($url, 'akamaized.net') !== FALSE) {
        return 'https://vimeo.com/';
    } elseif (strpos($url, 'megaupload.to') !== FALSE) {
        return 'https://vupload.com/';
    } elseif (strpos($url, 'zplayer.live') !== FALSE) {
        return 'https://v2.zplayer.live/';
    }
    return $url;
}

function parse_hls($content = '', $ref = '')
{
    $fileName = basename($ref);
    $ref = strtr($ref, [$fileName => '']);
    if (!empty($content)) {
        $ex = explode("\n", strtr($content, ["\r\n" => "\n"]));

        $result = [];
        foreach ($ex as $val) {
            if (strpos($val, '.m3u') !== FALSE || (strpos($val, 'mycdn.me') !== FALSE && strpos($val, '/video') !== FALSE)) {
                if (filter_var($val, FILTER_VALIDATE_URL)) {
                    $result[] = BASE_URL . 'playlist/?url=' . encode($val);
                } elseif (preg_match('/URI="([^"]+)"/', $val, $uri)) {
                    $result[] = strtr($val, [$uri[1] => BASE_URL . 'playlist/?url=' . encode($uri[1])]);
                } else {
                    $result[] = $val;
                }
            } elseif (strpos($val, '.ts') !== FALSE) {
                if (filter_var($val, FILTER_VALIDATE_URL)) {
                    $result[] = BASE_URL . 'hls/?url=' . encode($val);
                } elseif (!empty($ref)) {
                    $result[] = BASE_URL . 'hls/?url=' . encode($ref . $val);
                } else {
                    $result[] = $val;
                }
            } else {
                $result[] = $val;
            }
        }
        return trim(implode("\n", $result), '1');
    }
    return trim($content, '1');
}

function encode($data = '')
{
    if (!empty($data)) {
        try {
            // Remove the base64 encoding from our key
            $encryption_key = @base64_decode(SECURE_SALT);
            // Generate an initialization vector
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc'));
            // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
            $encrypted = @openssl_encrypt($data, 'aes-128-cbc', $encryption_key, 0, $iv);
            // The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
            $encode = rawurlencode(base64_encode($encrypted . '::' . $iv));
            return $encode;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return FALSE;
        }
    }
    return FALSE;
}

function decode($data = '')
{
    if (!empty($data)) {
        try {
            // Remove the base64 encoding from our key
            $encryption_key = @base64_decode(SECURE_SALT);
            // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
            list($encrypted_data, $iv) = explode('::', base64_decode(rawurldecode($data)), 2);
            $decode = @openssl_decrypt($encrypted_data, 'aes-128-cbc', $encryption_key, 0, $iv);
            if ($decode) {
                return $decode;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return FALSE;
        }
    }
    return FALSE;
}

function proxy_rotator($newKey = 0, $for = 'gdrive', $proxyList = [])
{
    $proxyType = ['socks4', 'socks4a', 'socks5'];
    $proxyReplace = [CURLPROXY_SOCKS4, CURLPROXY_SOCKS4A, CURLPROXY_SOCKS5];
    $realProxyType = CURLPROXY_HTTP;
    $realProxyUsrPwd = '';
    $disableProxy = get_option('disable_proxy');
    if (!filter_var($disableProxy, FILTER_VALIDATE_BOOLEAN)) {
        $proxyList = !empty($proxyList) ? $proxyList : explode("\n", strtr(trim(get_option('proxy_list')), ["\r\n" => "\n"]));
        $key = $newKey > 0 ? $newKey : 0;
        if (!empty($proxyList[$key])) {
            $proxy = $proxyList[$key];
            $pr = explode(',', $proxy);

            if ($for === 'streamable') {
                $url = 'https://streamable.com/nqfrzj';
            } elseif ($for === 'upstream') {
                $url = 'https://upstream.to/embed-d1fl3fks6nos.html';
            } elseif ($for === 'vidlox') {
                $url = 'https://vidlox.me/embed-9blkt2mq56td.html';
            } elseif ($for === 'googlephotos') {
                $url = 'https://photos.google.com/share/AF1QipNwnU5Lz8_VS0rj9NB9HU5suC0tNqawYe6wOA2E1_YcIyC-EvfSsCrwB5db3f8Zfw?key=eGswTGNLU2o0UUtkMVJLdUEwNTVLaUhueEdTNVpB';
            } elseif ($for === 'viu') {
                $url = 'https://www.viu.com/ott/id/id/all/video-japanese-drama-tv_shows-detective_conan_episode_520-1165827214?containerId=playlist-26270622';
            } elseif ($for === 'dood') {
                $url = 'https://dood.to/d/jq7gd6p2mo9b';
            } else {
                $url = 'https://drive.google.com/file/d/1cwus-hJ4iWy_-KSMd3y78yvMzvFYMjEu/view';
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            if (!empty($pr)) {
                $proxy = $pr[0];
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
                if (!empty($pr[1])) {
                    if (in_array(strtolower($pr[1]), $proxyType)) {
                        $key = array_search(strtolower($pr[1]), $proxyType);
                        $realProxyType = $proxyReplace[$key];
                        curl_setopt($ch, CURLOPT_PROXYTYPE, $realProxyType);
                    } else {
                        $realProxyUsrPwd = $pr[1];
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $realProxyUsrPwd);
                    }
                }
                if (!empty($pr[2])) {
                    $key = array_search(strtolower($pr[2]), $proxyType);
                    $realProxyType = $proxyReplace[$key];
                    curl_setopt($ch, CURLOPT_PROXYTYPE, $realProxyType);
                }
            }

            session_write_close();
            $response = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (($status >= 200 && $status < 400) && strpos($response, 'recaptcha') == FALSE) {
                error_log("Proxy $proxy work => Status $status");
                return [
                    'proxy' => $proxy,
                    'type'  => $realProxyType,
                    'usrpwd' => $realProxyUsrPwd
                ];
            } else {
                error_log("Proxy $proxy doesn't work => Status $status");
                if (filter_var(get_option('delete_unused_proxy'), FILTER_VALIDATE_BOOLEAN)) {
                    unset($proxyList[$key]);
                    $proxyList = array_values($proxyList);
                    // save new proxies
                    set_option('proxy_list', implode("\n", $proxyList));
                    $proxyList = explode("\n", strtr(trim(get_option('proxy_list')), ["\r\n" => "\n"]));
                } else {
                    unset($proxyList[$key]);
                    $proxyList = array_values($proxyList);
                }
                $cp = count($proxyList);
                if ($cp > 1) {
                    $randKey = array_rand($proxyList);
                    return proxy_rotator($randKey, $for, $proxyList);
                }
            }
        }
    }
    return FALSE;
}

function vidhost_supported($id = '', $selected = '', $required = false, $json = false)
{
    $videoSupported = [
        'uptobox' => 'Uptobox',
        'hexupload' => 'Hexupload',
        'okstream' => 'Okstream',
        'amazon' => 'Amazon Drive',
        'viu' => 'VIU',
        'ninjastream' => 'NinjaStream',
        'dood' => 'DoodStream',
        'filesfm' => 'Files.fm',
        'soundcloud' => 'Soundcloud',
        'rumble' => 'Rumble',
        'vimeo' => 'Vimeo',
        'zplayer' => 'zPlayer.live',
        'zippyshare' => 'Zippyshare',
        'mediafire' => 'MediaFire',
        'gdrive' => 'Google Drive',
        "youtube" => "Youtube",
        "facebook" => "Facebook",
        "googlephotos" => "Google Photos",
        "fembed" => "Fembed",
        "blogger" => "Blogger",
        "streamable" => "Streamable",
        "videobin" => "Videobin",
        "vupto" => "VUP.to",
        "bayfiles" => "BayFiles",
        "filesim" => "Files.im",
        "onedrive" => "OneDrive",
        "solidfiles" => "Solidfiles",
        "gofile" => "Gofile",
        "racaty" => "Racaty",
        "indishare" => "Indishare",
        "anonfile" => "AnonFile",
        "mixdropto" => "MixDrop",
        "vidmoly" => "Vidmoly",
        "vidlox" => "Vidlox",
        "hxfile" => "HxFile",
        "filerio" => "Filerio",
        "vidoza" => "Vidoza",
        "uploadsmobi" => "Uploads.mobi",
        "okru" => "OK.ru",
        "direct" => "Direct Link",
        "streamtape" => "Streamtape",
        "upstream" => "UpStream",
        'dropbox' => 'Dropbox',
        "mp4upload" => "mp4upload",
        "userscloud" => "Userscloud",
        "yadisk" => "Yandex Disk",
        'streamsb' => 'StreamSB',
        'yourupload' => 'YourUpload',
        'megaup' => 'MegaUp',
    ];
    ksort($videoSupported);
    if (!$json) {
        $disabled = !empty(get_option('disable_host')) ? json_decode(get_option('disable_host'), TRUE) : [];
        $html = '<select name="' . $id . '" class="form-control" ' . ($required ? 'required' : '') . '>';
        $html .= '<option value="">-- Select Host --</option>';
        foreach ($videoSupported as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($selected === $key ? 'selected' : '') . (in_array($key, $disabled) ? ' disabled' : '') . '>' . $value . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
    return json_encode($videoSupported);
}

function getDriveId($url = '')
{
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        if (preg_match('/d\/([^"]+)\//', $url, $fileid)) {
            $fileid = $fileid[1];
        } elseif (preg_match('/files\/([^"]+)/', $url, $fileid)) {
            $fileid = explode('?', $fileid[1]);
            $fileid = $fileid[0];
        } else {
            $query = parse_url($url, PHP_URL_QUERY);
            parse_str($query, $fileid);
            $fileid = !empty($fileid['id']) ? $fileid['id'] : FALSE;
        }
        return $fileid;
    }
    return FALSE;
}

function getHostId($url = '')
{
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $replaceUrl = ['www.' => '', 'sbembed.com' => 'streamsb.com', 'v2.zplayer.live' => 'zplayer.live', 'player.vimeo.com' => 'vimeo.com', 'mixdrop.co' => 'mixdrop.to', 'dl.indishare.cc' => 'indishare.org', 'streamtape.xyz' => 'streamtape.com', 'strtape.cloud' => 'streamtape.com', 'dood.so' => 'dood.to', 'doodstream.com' => 'dood.to', 'streamta.pe' => 'streamtape.com', 'uptostream.com' => 'uptobox.com', 'vupload.com' => 'vup.to', 'disk.yandex.ru' => 'yadi.sk', 'anonfiles.com' => 'anonfile.com', 'embed-' => '', '.html' => ''];
        $replaceHost = ['vup.to' => 'vupto', 'uploads.mobi' => 'uploadsmobi', 'mixdrop.to' => 'mixdropto', 'files.im' => 'filesim', 'files.fm' => 'filesfm', 'ok.ru' => 'okru', 'yadi.sk' => 'yadisk', 'amazon.com' => 'amazondrive', '.com' => '', '.net' => '', '.cam' => '', '.to' => '', '.in' => '', '.io' => '', '.org' => '', '.cc' => '', '.xyz' => '', '.tv' => '', '.co' => '', '.me' => '', '.live' => ''];
        $url    = strtr($url, $replaceUrl);
        $host   = parse_url($url, PHP_URL_HOST);
        $path   = parse_url($url, PHP_URL_PATH);
        $query  = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $qry);
        if (strpos($url, 'drive.google.com') !== FALSE || strpos($url, 'googleapis.com') !== FALSE) {
            return [
                'host' => 'gdrive',
                'host_id' => getDriveId($url)
            ];
        } elseif (strpos($url, 'photos.google.com') !== FALSE) {
            list($host, $id) = explode('/share/', $url, 2);
            return [
                'host' => 'googlephotos',
                'host_id' => $id
            ];
        } elseif (strpos($url, 'blogger') !== FALSE) {
            return [
                'host' => 'blogger',
                'host_id' => $qry['token']
            ];
        } elseif (strpos($url, 'youtube') !== FALSE || strpos($url, 'youtu.be') !== FALSE) {
            if (!empty($qry['v'])) $id = $qry['v'];
            else list($host, $id) = explode('/', trim(strtr($url, ['https://' => '', 'http://' => '']), '/'), 2);
            return [
                'host' => 'youtube',
                'host_id' => $id
            ];
        } elseif (strpos($url, 'facebook') !== FALSE) {
            return [
                'host' => 'facebook',
                'host_id' => trim($path, '/')
            ];
        } elseif (strpos($url, 'live.com') !== FALSE) {
            $id = !empty($qry['resid']) ? $qry['resid'] : (!empty($qry['id']) ? $qry['id'] : '');
            return [
                'host' => 'onedrive',
                'host_id' => $id
            ];
        } elseif (strpos($url, 'soundcloud.com') !== FALSE) {
            return [
                'host' => 'soundcloud',
                'host_id' => $url
            ];
        } elseif ((strpos($url, '/v/') !== FALSE || strpos($url, '/f/') !== FALSE) && strpos($url, 'streamtape') === FALSE && strpos($url, 'mixdrop') === FALSE && strpos($url, 'solidfiles') === FALSE && strpos($url, 'zippyshare') === FALSE && strpos($url, 'files.fm') === FALSE && strpos($url, 'vup') === FALSE) {
            return [
                'host' => 'fembed',
                'host_id' => $url
            ];
        } else {
            $core = new \core();
            $host = strtr($host, $replaceHost);
            $path = explode('/', trim($path, '/'));
            if (strpos($url, 'files.fm') === FALSE && strpos($url, 'viu.com') === FALSE) {
                if ((strlen($path[0]) > 5 && $path[0] !== 'videoembed' && $path[0] !== 'clouddrive' && $path[0] !== 'download') || $host === 'megaup') {
                    $id = $path[0];
                } elseif (strlen($path[1]) > 5) {
                    $id = $path[1];
                } else {
                    $id = end($path);
                }
            } else {
                $id = $url;
            }
            //return $path;
            if (!in_array($host, $core->bypass_host()) && !in_array($host, $core->direct_host())) {
                $host = strpos($url, 'zippyshare') !== FALSE ? 'zippyshare' : 'direct';
                $id = $url;
            }
            return [
                'host' => $host,
                'host_id' => trim($id, '/')
            ];
        }
    }
    return FALSE;
}

function getDownloadLink($host = '', $id = '')
{
    if ($host === 'uptobox') {
        return "https://uptobox.com/$id";
    } elseif ($host === 'hexupload') {
        return "https://hexupload.net/$id";
    } elseif ($host === 'okstream') {
        return "https://www.okstream.cc/$id/";
    } elseif ($host === 'amazon') {
        return "https://www.amazon.com/clouddrive/share/$id";
    } elseif ($host === 'amazondrive') {
        return "https://www.amazon.com/clouddrive/share/$id";
    } elseif ($host === 'ninjastream') {
        return "https://ninjastream.to/watch/$id";
    } elseif ($host === 'dood') {
        return "https://dood.to/d/$id";
    } elseif ($host === 'vimeo') {
        return "https://vimeo.com/$id";
    } elseif ($host === 'zplayer') {
        return "https://v2.zplayer.live/video/$id";
    } elseif ($host === 'mediafire') {
        return "https://www.mediafire.com/file/$id";
    } elseif ($host === 'yourupload') {
        return "https://yourupload.com/watch/$id";
    } elseif ($host === 'streamsb') {
        return "https://streamsb.net/$id.html";
    } elseif ($host === 'megaup') {
        return "https://megaup.net/$id";
    } elseif ($host === 'yadisk') {
        return "https://yadi.sk/i/$id";
    } elseif ($host === 'facebook') {
        return "https://www.facebook.com/$id";
    } elseif ($host === 'dropbox') {
        return "https://www.dropbox.com/s/$id/";
    } elseif ($host === 'gdrive') {
        return "https://drive.google.com/file/d/$id/view";
    } elseif ($host === 'googlephotos') {
        return "https://photos.google.com/share/$id";
    } elseif ($host === 'youtube') {
        return "https://youtube.com/watch?v=$id";
    } elseif ($host === 'blogger') {
        return "https://www.blogger.com/video.g?token=$id";
    } elseif ($host === 'anonfile') {
        return "https://anonfiles.com/$id";
    } elseif ($host === 'bayfiles') {
        return "https://bayfiles.com/$id";
    } elseif ($host === 'filerio') {
        return "https://filerio.in/$id.html";
    } elseif ($host === 'filesim') {
        return "https://files.im/$id";
    } elseif ($host === 'gofile') {
        return "https://gofile/d/$id";
    } elseif ($host === 'hxfile') {
        return "https://hxfile.co/$id";
    } elseif ($host === 'indishare') {
        return "https://www.indishare.org/$id";
    } elseif ($host === 'mixdropto') {
        return "https://mixdrop.co/f/$id";
    } elseif ($host === 'mp4upload') {
        return "https://mp4upload.com/$id.html";
    } elseif ($host === 'onedrive') {
        return "https://onedrive.live.com/?id=$id";
    } elseif ($host === 'racaty') {
        return "https://racaty.net/$id";
    } elseif ($host === 'solidfiles') {
        return "https://solidfiles.com/v/$id";
    } elseif ($host === 'uploadsmobi') {
        return "https://uploads.mobi/$id";
    } elseif ($host === 'userscloud') {
        return "https://userscloud.com/$id";
    } elseif ($host === 'vidmoly') {
        return "https://vidmoly.me/w/$id";
    } elseif ($host === 'vidoza') {
        return "https://vidoza.net/$id.html";
    } elseif ($host === 'vupto') {
        return "https://vup.to/$id.html";
    } elseif ($host === 'streamable') {
        return "https://streamable.com/$id";
    } elseif ($host === 'okru') {
        return "https://ok.ru/video/$id";
    } elseif ($host === 'videobin') {
        return "https://videobin.co/$id";
    } elseif ($host === 'vidlox') {
        return "https://vidlox.me/$id.html";
    } elseif ($host === 'streamtape') {
        return "https://streamtape.com/e/$id";
    } elseif ($host === 'upstream') {
        return "https://upstream.to/$id.html";
    } elseif ($host === 'rumble') {
        return "https://rumble.com/$id";
    }
    return $id;
}

function jsObfustatorStdr($script = '')
{
    if (!empty($script)) {
        $md = new \Mobile_Detect;
        if (!$md->isMobile() && !$md->isTablet()) {
            $packer = new \Tholu\Packer\Packer($script, 95, false, true, true);
            $packed_js = utf8_encode($packer->pack());
        } else {
            $packer = new \Tholu\Packer\Packer($script, 62, true, false, true);
            $packed_js = $packer->pack();
        }
        return $packed_js;
    }
    return $script;
}

function jsObfustator($script = '')
{
    if (!empty($script)) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://javascriptobfuscator.com/Javascript-Obfuscator.aspx",
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "UploadLib_Uploader_js=1&__EVENTTARGET=ctl00%24MainContent%24Button1&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKMTM4MjU3NDgxNw9kFgJmD2QWAgIDD2QWAgIBDxYCHgRUZXh0BdkBPGxpIGNsYXNzPSdsaXN0LWlubGluZS1pdGVtIG1yLTAnPjxhIGNsYXNzPSd1LWhlYWRlcl9fbmF2YmFyLWxpbmsnIGhyZWY9Jy9zaWduaW4uYXNweCc%2BQWNjb3VudCBMb2dpbjwvYT48L2xpPgo8bGkgY2xhc3M9J2xpc3QtaW5saW5lLWl0ZW0gbXItMCc%2BPGEgY2xhc3M9J3UtaGVhZGVyX19uYXZiYXItbGluaycgaHJlZj0nL3JlZ2lzdGVyLmFzcHgnPlJlZ2lzdGVyPC9hPjwvbGk%2BIGQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgUFGmN0bDAwJE1haW5Db250ZW50JGNiTGluZUJSBRpjdGwwMCRNYWluQ29udGVudCRjYkluZGVudAUdY3RsMDAkTWFpbkNvbnRlbnQkY2JFbmNvZGVTdHIFG2N0bDAwJE1haW5Db250ZW50JGNiTW92ZVN0cgUgY3RsMDAkTWFpbkNvbnRlbnQkY2JSZXBsYWNlTmFtZXNJfhOUrd%2FjYMwya4KqO76nY28hwfkIpQAmM%2Bhk51YiJA%3D%3D&__VIEWSTATEGENERATOR=6D198BE1&__EVENTVALIDATION=%2FwEdAAzyRDYiu41ivvipFNnKHrClCJ8xELtYGHfHJig8BNR1A%2Fnd3wctyww89JbDbeLvgrjW%2FQY5cz%2Bpu3qUjqM%2B4n5jIWlyEKFxLO5ck%2BF6M0ODiJ1itZp%2B2hATYVWj%2Fb%2B%2BnyR8f2dPhQQre4aI0Iea4dKYmjI5SSrP8%2Fdi9FPKAsCRiSDSoNvpe2qp90wnP2HAWzNs9mdJae9TApAJFRRb54f73WbA4XcESfoeI8EInEzA%2BdxRJK%2FkVxlULg0AsW337%2FI8ZVc1MOVK9zP9AcHGfTxHt98XiGpmCkjM8SbZaQl4aw%3D%3D&ctl00%24MainContent%24uploader1=&ctl00%24MainContent%24TextBox1=" . rawurlencode($script) . "&ctl00%24MainContent%24TextBox2=&ctl00%24MainContent%24cbEncodeStr=on&ctl00%24MainContent%24cbMoveStr=on&ctl00%24MainContent%24cbReplaceNames=on&ctl00%24MainContent%24TextBox3=%5E_get_%0D%0A%5E_set_%0D%0A%5E_mtd_",
            CURLOPT_COOKIE => "__cfduid=d41b6180c51339579b7a9ae256dab7d801594036261",
            CURLOPT_HTTPHEADER => array(
                "accept-language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7",
                "content-type: application/x-www-form-urlencoded",
                "cookie: __cfduid=db10f886e3be05849ac0744138d550aa01594035667",
                'host: javascriptobfuscator.com',
                "origin: https://javascriptobfuscator.com",
                "referer: https://javascriptobfuscator.com/Javascript-Obfuscator.aspx",
                "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36"
            ),
        ));
        session_write_close();
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if (!$err) {
            $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
            if ($dom) {
                $text2 = $dom->find('#ctl00_MainContent_TextBox2', 0);
                if ($text2) {
                    $packed_js = jsObfustatorStdr(trim(html_entity_decode($text2->innertext)));
                }
            } else {
                $packed_js = jsObfustatorStdr($script);
            }
        } else {
            $packed_js = jsObfustatorStdr($script);
        }
        return $packed_js;
    }
    return $script;
}

function recaptcha_validate($captcha = '')
{
    $sk = get_option('recaptcha_secret_key');
    if ($sk) $secretKey = $sk;
    else $secretKey = '';

    if (!empty($secretKey)) {
        if (!empty($captcha)) {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array('secret' => $secretKey, 'response' => $captcha);

            $response = @file_get_contents($url . '?' . http_build_query($data));
            if ($response) {
                $responseKeys = json_decode($response, true);
                return (bool) $responseKeys['success'];
            } else {
                error_log($url . '?' . http_build_query($data) . ' => ' . $response);
            }
        } else {
            return false;
        }
    } else {
        return true;
    }
    return false;
}

function subtitle_languages($name = '', $selected = '', $required = false, $json = false)
{
    $languages = [
        "Default",
        "Afrikanns",
        "Albanian",
        "Arabic",
        "Armenian",
        "Basque",
        "Bengali",
        "Bulgarian",
        "Catalan",
        "Cambodian",
        "Chinese",
        "Croatian",
        "Czech",
        "Danish",
        "Dutch",
        "English",
        "Estonian",
        "Fiji",
        "Finnish",
        "French",
        "Georgian",
        "German",
        "Greek",
        "Gujarati",
        "Hebrew",
        "Hindi",
        "Hungarian",
        "Icelandic",
        "Indonesian",
        "Irish",
        "Italian",
        "Japanese",
        "Javanese",
        "Korean",
        "Latin",
        "Latvian",
        "Lithuanian",
        "Macedonian",
        "Malay",
        "Malayalam",
        "Maltese",
        "Maori",
        "Marathi",
        "Mongolian",
        "Nepali",
        "Norwegian",
        "Persian",
        "Polish",
        "Portuguese",
        "Punjabi",
        "Quechua",
        "Romanian",
        "Russian",
        "Samoan",
        "Serbian",
        "Slovak",
        "Slovenian",
        "Spanish",
        "Swahili",
        "Swedish",
        "Tamil",
        "Tatar",
        "Telugu",
        "Thai",
        "Tibetan",
        "Tonga",
        "Turkish",
        "Ukranian",
        "Urdu",
        "Uzbek",
        "Vietnamese",
        "Welsh",
        "Xhosa"
    ];
    if (!$json) {
        $html = '<select name="' . $name . '" class="form-control" ' . ($required ? 'required' : '') . '>';
        foreach ($languages as $lang) {
            $html .= '<option value="' . $lang . '" ' . ($selected === $lang ? 'selected' : '') . '>' . $lang . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
    return json_encode($languages);
}

function earnmoney_website($id = '', $selected = '', $required = false, $json = false)
{
    $website = [
        "random" => "Random",
        "adf.ly" => "AdFly",
        "adtival.network" => "Adtival Network",
        "clk.sh" => "Clk.sh",
        "cutpaid.com" => "Cutpaid",
        "ouo.io" => "ouo.io",
        "shrinkads.com" => "Shrink Ads (Safelink Blog)",
        "safelinkblogger.com" => "Safelink Blogger",
        "safelinku.com" => "SafelinkU",
        "shorten-link.com" => "Shorten-link",
        "wi.cr" => "Wicr!",
        "ylinkz.com" => "YLinkz"
    ];
    if (!$json) {
        $html = '<select name="' . $id . '" id="' . $id . '" class="form-control" ' . ($required ? 'required' : '') . '>';
        foreach ($website as $key => $value) {
            $html .= '<option value="' . $key . '" ' . ($selected === $key ? 'selected' : '') . '>' . $value . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
    return json_encode($website);
}

function earnmoney_link($link = '', $provider = '')
{
    if (!empty($link) && !empty($provider)) {
        $apikey = get_option('additional_url_shortener_' . $provider);
        if (!empty($apikey)) {
            switch ($provider) {
                case 'ylinkz.com':
                    return 'https://ylinkz.com/st?api=' . $apikey . '&url=' . $link;
                    break;
                case 'ouo.io':
                    return 'http://ouo.io/qs/' . $apikey . '?s=' . $link;
                    break;
                case 'safelinku.com':
                    return 'https://semawur.com/full/?type=2&api=' . $apikey . '&url=' . $link;
                    break;

                case 'adtival.network':
                    return 'https://www.adtival.network/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'safelinkblog.com':
                    return 'https://www.shrinkads.com/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'shrinkads.com':
                    return 'https://www.shrinkads.com/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'noyads.com':
                    return 'https://noyads.com/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'safelinkblogger.com':
                    return 'https://safelinkblogger.com/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'shrink.world':
                    return 'https://shrink.world/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'shorten-link.com':
                    return 'https://shorten-link.com/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'shortzon.com':
                    return 'https://shortzon.com/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'cutpaid.com':
                    return 'https://cutpaid.com/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'wi.cr':
                    return 'https://wi.cr/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'adf.ly':
                    return 'http://adf.ly/' . $apikey . '/' . $link;
                    break;

                case 'clk.sh':
                    return 'https://clk.sh/st?api=' . $apikey . '&url=' . $link;
                    break;

                case 'l2s.pet':
                    return 'https://l2s.pet/st?api=' . $apikey . '&url=' . $link;
                    break;

                default:
                    return $link;
                    break;
            }
        }
    }
    return $link;
}

function bitly_link($longurl = '')
{
    $token = get_option('main_url_shortener');
    if (!empty($token)) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL             => 'https://api-ssl.bitly.com/v4/bitlinks',
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING => "",
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_POSTFIELDS      => json_encode(['long_url' => $longurl]),
            CURLOPT_USERAGENT       => USER_AGENT,
            CURLOPT_HTTPHEADER      => [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json"
            ],
        ));
        session_write_close();
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if (!$err) {
            $bitly = json_decode($response, true);
            $safelink = !empty($bitly['link']) ? $bitly['link'] : $longurl;
            return $safelink;
        }
    }
    return $longurl;
}

function download_link($link = '')
{
    if (!empty($link)) {
        $providers = json_decode(earnmoney_website('', '', false, true), true);
        $provider = !empty(get_option('additional_url_shortener')) ? get_option('additional_url_shortener') : 'random';
        if ($provider === 'random') {
            $pidx = array_rand($providers);
            $provider = $providers[$pidx];
        }
        $longurl = earnmoney_link($link, strtolower($provider));
        return bitly_link($longurl);
    }
    return $link;
}

function free_proxy_list_net()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://free-proxy-list.net');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if (defined('CURLOPT_TCP_FASTOPEN')) {
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, 1);
    }
    curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'host: free-proxy-list.net',
        'origin: http://free-proxy-list.net',
    ));
    session_write_close();
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if (!$err) {
        $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
        $raw = $dom->find('#raw', 0);
        if (!empty($raw)) {
            $textarea = $raw->find('textarea', 0)->plaintext;
            $list = explode('UTC.', $textarea);
            $list = trim(end($list));
            if (!empty($list)) {
                $result = [];
                $array = explode(" ", $list);
                $array = count($array) > 1 ? $array : explode("\r\n", $list);
                foreach ($array as $proxy) {
                    array_push($result, $proxy);
                }
                return $result;
            }
        }
    }
    return [];
}

function free_proxy_cz()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://free-proxy.cz/en/proxylist/country/all/https/ping/level1');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if (defined('CURLOPT_TCP_FASTOPEN')) {
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, 1);
    }
    curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'host: free-proxy.cz',
        'origin: http://free-proxy.cz',
    ));
    session_write_close();
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if (!$err) {
        $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
        $scripts = $dom->find('script');
        $fports = $dom->find('span.fport');
        if (!empty($scripts) && !empty($fports)) {
            $ip = [];
            $ports = [];
            foreach ($scripts as $sc) {
                if (strpos($sc, 'Base64.decode') !== FALSE) {
                    $ip[] = base64_decode(strtr($sc->innertext, ['document.write(Base64.decode("', '"))' => '', ';' => '']));
                }
            }
            foreach ($fports as $port) {
                $ports[] = (string) trim($port->plaintext);
            }
            $cip = count($ip);
            $cport = count($ports);
            if ($cip === $cport) {
                $result = [];
                foreach ($ip as $k => $v) {
                    array_push($result, $v . ':' . $ports[$k]);
                }
                return $result;
            }
        }
    }
    return [];
}

function proxy_docker_com()
{
    // ambil token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.proxydocker.com/id/proxylist/search?need=Google&type=http-https&anonymity=ELITE&port=&country=Indonesia&city=&state=all');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if (defined('CURLOPT_TCP_FASTOPEN')) {
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, 1);
    }
    curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/proxy-docker.txt');
    curl_setopt($ch, CURLOPT_REFERER, 'https://www.proxydocker.com/id/proxylist/platform/google');
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'host: www.proxydocker.com',
        'origin: https://www.proxydocker.com',
    ));
    session_write_close();
    $response = curl_exec($ch);
    $err = curl_error($ch);
    if (!$err) {
        $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
        $meta = $dom->find('meta[name="_token"]');
        if (!empty($meta)) {
            curl_setopt($ch, CURLOPT_URL, 'https://www.proxydocker.com/id/api/proxylist/');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'token=' . $meta[0]->content . '&country=Indonesia&city=all&state=all&port=all&type=http-https&anonymity=ELITE&need=all&page=1');
            curl_setopt($ch, CURLOPT_REFERER, 'https://www.proxydocker.com/id/proxylist/search?need=Google&type=http-https&anonymity=ELITE&port=&country=Indonesia&city=&state=all');
            curl_setopt($ch, CURLOPT_COOKIEFILE, BASE_DIR . 'cookies/proxy-docker.txt');
            session_write_close();
            $response = curl_exec($ch);
            $err = curl_error($ch);
            if (!$err) {
                $arr = json_decode($response, TRUE);
                $result = [];
                foreach ($arr['proxies'] as $pr) {
                    $result[] = $pr['ip'] . ':' . $pr['port'];
                }
                return $result;
            }
        }
    }
    curl_close($ch);
    return [];
}

function autoupdateProxy()
{
    $disableFree = get_option('free_proxy');
    $disableProxy = get_option('disable_proxy');
    if (filter_var($disableFree, FILTER_VALIDATE_BOOLEAN) === FALSE && filter_var($disableProxy, FILTER_VALIDATE_BOOLEAN) === FALSE) {
        $list = explode("\n", strtr(trim(get_option('proxy_list')), ["\r\n" => "\n"]));
        $clist = count($list);
        if ($clist <= 1) {
            $pd = proxy_docker_com();
            $fpl = free_proxy_list_net();
            $fp = free_proxy_cz();
            $prx = array_unique(array_merge($list, $fpl, $fp, $pd), SORT_REGULAR);
            if (!empty($prx)) {
                set_option('proxy_list', implode("\n", $prx));
                return $prx;
            }
        }
    }
    return FALSE;
}

function newUpdate()
{
    global $db;
    try {
        // mysql/mariadb

        // tabel short link
        $db->query("CREATE TABLE IF NOT EXISTS `tb_videos_short` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `key` varchar(50) NOT NULL,
            `vid` bigint(20) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `key` (`key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");

        // table gdrive mirrors
        $db->query("CREATE TABLE IF NOT EXISTS `tb_gdrive_mirrors` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `gdrive_id` varchar(50) NOT NULL,
            `mirror_id` varchar(50) NOT NULL,
            `mirror_email` varchar(255) NOT NULL,
            `added` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");

        // table gdrive auth
        $db->query("CREATE TABLE IF NOT EXISTS `tb_gdrive_auth` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(100) NOT NULL,
            `api_key` varchar(50) NOT NULL,
            `client_id` varchar(100) NOT NULL,
            `client_secret` varchar(50) NOT NULL,
            `refresh_token` varchar(150) NOT NULL,
            `created` int(11) NOT NULL,
            `modified` int(11) NOT NULL DEFAULT '0',
            `uid` int(11) NOT NULL DEFAULT '1',
            `status` int(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`),
            UNIQUE INDEX `email` (`email`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");
    } catch (\PDOException | \Exception $e) {
        error_log($e->getMessage());
    }

    try {
        $dir = BASE_DIR . 'includes/gdrive_auth/';
        $files = array_values(array_diff(scandir($dir), array('..', '.', 'sample.json', 'sample@gmail.com.json')));
        if (!empty($files)) {
            foreach ($files as $value) {
                $file = $dir . $value;
                $finfo = pathinfo($file);
                if (is_file($file) && $finfo['extension'] === 'json') {
                    $content = @file_get_contents($dir . $value);
                    if ($content) {
                        $data = json_decode($content, TRUE);
                        $sql = "INSERT INTO `tb_gdrive_auth` (`email`, `api_key`, `client_id`, `client_secret`, `refresh_token`, `created`) VALUES (?, ?, ?, ?, ?, ?)";
                        $params = array($data['email'], $data['api_key'], $data['client_id'], $data['client_secret'], $data['refresh_token'], time());
                        $db->prepare($sql)->execute($params);
                        $newID = $db->lastInsertId();
                        if ($newID) {
                            unlink($file);
                        }
                    }
                }
            }
        }
    } catch (\Exception $e) {
        error_log('insert gdrive accounts failed: ' . $e->getMessage());
    }
}
