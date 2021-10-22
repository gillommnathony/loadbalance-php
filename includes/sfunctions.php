<?php
require_once BASE_DIR .'administrator/includes/conn.php';

function autoloadAdmin($class){
    $classFile = BASE_DIR ."administrator/includes/classes/$class.class.php";
    if(file_exists($classFile)){
        require_once $classFile;
    }
}
spl_autoload_register("autoloadAdmin");

function autoloadClasses($class){
    $classFile = BASE_DIR ."includes/classes/$class.class.php";
    if(file_exists($classFile)){
        require_once $classFile;
    }
}
spl_autoload_register("autoloadClasses");

// phpfastcache
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Mongodb\Config as MangodbConfig;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;

if (extension_loaded('redis')) {
    $InstanceCache = CacheManager::getInstance('redis', new RedisConfig([
        'host' => '127.0.0.1',
        'port' => 6379
    ]));
} elseif (extension_loaded("mongodb")) {
    $InstanceCache = CacheManager::getInstance('mongodb', new MangodbConfig([
        'host' => '127.0.0.1',
        'port' => 27017,
        'username' => '',
        'password' => '',
        'timeout' => 1,
        'collectionName' => 'Cache',
        'databaseName' => 'phpFastCache'

    ]));
} else {
    // buat direktori tmp
    if (!is_dir(BASE_DIR . 'tmp')) {
        mkdir(BASE_DIR . 'tmp', 0755, true);
        chmod(BASE_DIR . 'tmp', 0755);
    }
    $CacheInstance = extension_loaded('pdo_sqlite') ? 'sqlite' : 'files';
    CacheManager::setDefaultConfig(new ConfigurationOption([
        'path' => BASE_DIR . 'tmp',
        'preventCacheSlams' => true,
        'cacheSlamsTimeout' => 30
    ]));
    $InstanceCache = CacheManager::getInstance($CacheInstance);
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

function decode($data=''){
    if(!empty($data)){
        try{
            // Remove the base64 encoding from our key
            $encryption_key = @base64_decode(SECURE_SALT);
            // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
            list($encrypted_data, $iv) = explode('::', base64_decode(rawurldecode($data)), 2);
            $decode = @openssl_decrypt($encrypted_data, 'aes-128-cbc', $encryption_key, 0, $iv);
            if($decode){
                return $decode;
            }
        }
        catch(Exception $e){
            error_log($e->getMessage());
            return FALSE;            
        }
    }
    return FALSE;
}

function domain_whitelisted()
{
    $domains = get_option('domain_whitelisted');
    if (!empty($domains)) {
        return explode("\n", str_replace(['www.', "\r\n"], ['', "\n"], trim($domains)));
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
            $domains = explode("\n", str_replace(['www.', "\r\n"], ['', "\n"], trim($domains)));
            return in_array($referer, $domains);
        }
    }
    return FALSE;
}

function is_referer_blacklisted($ref = '')
{
    if (!empty($ref)) {
        $search = ['https://', 'http://', 'www.', "\r\n"];
        $replace = ['', '', '', "\n"];
        $referer = str_replace($search, '', trim(rawurldecode($ref), '/'));
        $links = get_option('link_blacklisted');
        if ($links) {
            $links = explode("\n", str_replace($search, $replace, trim($links, '/')));
            return in_array($referer, $links);
        }
    }
    return FALSE;
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

function parse_hls($content='', $ref=''){
    $ref = explode('.m3u8', $ref);
    $ref = explode('/', $ref[0]);
    $eref = count($ref) - 1;
    unset($ref[$eref]);

    $ref = implode('/', $ref) .'/';
    if(!empty($content)){
        $ex = explode("\n", str_replace("\r\n", "\n", $content));

        $result = [];
        foreach($ex as $val){
            if((strpos($val, '.m3u8') !== FALSE || (strpos($val, 'mycdn.me') !== FALSE && strpos($val, '/video') !== FALSE)) && strpos($val, 'clipwatching.com') ===FALSE && strpos($val, 'zplayer.live') === FALSE){
                if(filter_var($val, FILTER_VALIDATE_URL)){
                    $result[] = BASE_URL .'playlist?url='. encode($val);
                }
                elseif(preg_match('/URI="([^"]+)"/', $val, $uri)){
                    $result[] = str_replace($uri[1], BASE_URL .'playlist?url='. encode($uri[1]), $val);
                }
                else {
                    $result[] = $val;
                }
            } elseif(strpos($val, '.ts') !== FALSE){
                if(filter_var($val, FILTER_VALIDATE_URL)){
                    $result[] = BASE_URL .'hls?url='. encode($val);
                }
                elseif(!empty($ref)){
                    $result[] = BASE_URL .'hls?url='. encode($ref . $val);
                }
                else {
                    $result[] = $val;
                }
            }
            else {
                $result[] = $val;
            }
        }
        return implode("\n", $result);
    }
    return $content;
}
