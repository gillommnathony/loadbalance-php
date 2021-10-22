<?php
require_once 'vendor/autoload.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');
if(!empty($_SERVER['QUERY_STRING'])){
    $query = decode($_SERVER['QUERY_STRING']);
    parse_str($query, $qry);
    if(!empty($qry['origin'])) {
        $origin = !empty($_SERVER['HTTP_ORIGIN']) ? parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST) : (!empty($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : '');
        $main = get_option('main_site');
        $lb = $db->prepare("SELECT id FROM tb_loadbalancers WHERE link LIKE ? OR link LIKE ? LIMIT 1");
        $lb->execute(['%'. $qry['origin'] .'%', '%' . $origin . '%']);
        $lbs = $lb->fetchAll(\PDO::FETCH_ASSOC);
        if(strpos($main, $origin) !== FALSE || $lbs){
            if(!empty($qry['source']) && $qry['source'] === 'db') {
                $video = new \videos();
                $data = $video->get($qry['id']);
                if (!empty($data['host_id'])) {
                    $key = $data['host'] . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $data['host_id']);
                    delete_video_cache($key);
                }
                if (!empty($data['ahost_id'])) {
                    $key = $data['ahost'] . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $data['ahost_id']);
                    delete_video_cache($key);
                }
            } else {
                if (!empty($qry['id'])) {
                    $key = $qry['host'] . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $qry['id']);
                    delete_video_cache($key);
                }
                if (!empty($qry['aid'])) {
                    $key = $qry['ahost'] . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $qry['aid']);
                    delete_video_cache($key);
                }
            }
            $parser = \UAParser\Parser::create();
            $browser = !empty($_SERVER['HTTP_USER_AGENT']) ? $parser->parse($_SERVER['HTTP_USER_AGENT'])->toString() : 'bot';
            $remote_ip = $_SERVER['REMOTE_ADDR'];

            $parse = new \parse_sources($qry);
            $parse->remote_ip = $remote_ip;
            $parse->user_agent = $browser;
            $parse->qry_string = $_SERVER['QUERY_STRING'];
            $parse->real_user_agent = $_SERVER['HTTP_USER_AGENT'];
            $config = $parse->get_config();
            if(!empty($config['sources']) || !empty($config['sources_alt'])){
                $sources = !empty($config['sources']) ? $config['sources'] : $config['sources_alt'];
                // ninjastream alternative
                $sca = 'sca = ' . json_encode($sources, TRUE) . ';';
                if (!empty($sources) && !filter_var($sources[0]['file'], FILTER_VALIDATE_URL)) {
                    $sca .= '
                        var ninjaFile = sca[0].file;
                        var ninjaLink = ninjaFile.split("~");
                        sca[0].file = ninjaDecode(ninjaLink[0]) + ninjaLink[1];
                    ';
                }
                $jsc = $sca . '
                    var xConfig = {
                        sources: sca,
                        tracks: ' . json_encode($config['tracks'], TRUE) . '
                    };
                    vp.load(xConfig);
                    vp.play();
                ';
                $jsc = filter_var(get_option('production_mode'), FILTER_VALIDATE_BOOLEAN) ? jsObfustatorStdr(trim($jsc)) : trim($jsc);
                $jsc = 'try{'. $jsc . '}catch(e){console.log("error");location.href=location.href;}';
                http_response_code(200);
                echo json_encode([
                    'status' => 'ok',
                    'message' => 'Video cache cleared successfully.',
                    'result' => $jsc
                ]);
                exit;
            }
        }
    }
    http_response_code(403);
    echo json_encode([
        'status' => 'fail',
        'message' => 'What do you want?'
    ]);
    exit;
}
http_response_code(404);
echo json_encode([
    'status' => 'fail',
    'message' => 'Video cache failed to clear.'
]);
