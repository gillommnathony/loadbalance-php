<?php
function show_public_balancer(){
    global $db;
    if(!is_null($db)){
        $host = str_replace(['https:', 'http:'], '', BASE_URL);
        $lb = $db->prepare("SELECT `public` FROM tb_loadbalancers WHERE `status`=1 AND `link` LIKE '%$host'");
        $lb->execute();
        $data = $lb->fetchAll(PDO::FETCH_ASSOC);
        if($data){
            if($data[0]['public'] == 1){
                return TRUE;
            }
        }
        else{
            return TRUE;
        }
    }
    return FALSE;
}

function domain_whitelisted(){
    $domains = get_option('domain_whitelisted');
    if (!empty($domains)) {
        return explode("\n", str_replace(['www.', "\r\n"], ['', "\n"], trim($domains)));
    }
    return [];
}

function is_domain_whitelisted($ref=''){
    $referer= !empty($ref) ? ltrim(parse_url($ref, PHP_URL_HOST), 'www.') : '';
    if(!empty($referer)){
        $domains = domain_whitelisted();
        if(!empty($domains)) return in_array($referer, $domains);
    }
    return TRUE;
}

function is_domain_blacklisted($ref=''){
    $referer= !empty($ref) ? ltrim(parse_url($ref, PHP_URL_HOST), 'www.') : '';

    if(!empty($referer)){
        $domains = get_option('domain_blacklisted');
        if($domains){
            $domains = explode("\n", str_replace(['www.', "\r\n"], ['', "\n"], trim($domains)));
            return in_array($referer, $domains);
        }
    }
    return FALSE;
}

function is_referer_blacklisted($ref=''){
    if(!empty($ref)){
        $search = ['https://', 'http://', 'www.', "\r\n"];
        $replace= ['', '', '', "\n"];
        $referer = str_replace($search, '', trim(rawurldecode($ref), '/'));
        $links = get_option('link_blacklisted');
        if($links){
            $links = explode("\n", str_replace($search, $replace, trim($links, '/')));
            return in_array($referer, $links);
        }
    }
    return FALSE;
}

function sitename(){
    $dbsitename = get_option('site_name');
    if($dbsitename){
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

function current_user(){
    $login = new \login();
    return $login->cek_login();
}

function is_admin(){
    $user = current_user();
    return $user && intval($user['role']) === 0;
}

function is_anonymous(){
    $anonymous = get_option('anonymous_generator');
    return filter_var($anonymous, FILTER_VALIDATE_BOOLEAN);
}

function error($status=404){
    switch($status){
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

function get_host_status($host='', $html=FALSE){
    $disabled_hosts = !empty(get_option('disable_host')) ? json_decode(get_option('disable_host'), TRUE) : [];
    if(!empty($host)){
        $disabled = in_array($host, $disabled_hosts);
        if($html){
            if($disabled){
                return '<span class="text-danger"><i class="fa fa-ban fa-lg"></i><span class="ml-1">Disabled</span></span>';
            }
            else {
                return '<span class="text-success"><i class="fa fa-check-circle fa-lg"></i><span class="ml-1">Working</span></span>';
            }
        }
        else {
            return $disabled; 
        }
    }
    return FALSE;
}
