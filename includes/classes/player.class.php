<?php
class player
{
    var $title      = '';
    var $poster     = '';
    var $sources    = [];
    var $tracks     = [];
    var $vast_ads   = [];
    var $subcolor   = '#ffff00';
    var $abouttext  = 'GunDeveloper.com';
    var $aboutlink  = 'https://gundeveloper.com';
    var $sharelink  = '';

    function __construct()
    {
    }

    function config()
    {
        $player = get_option('player');
        if (!empty($player)) {
            $config = $this->$player();
        } else {
            $config = $this->jwplayer();
        }
        return $config;
    }

    function get_tracks()
    {
        return $this->tracks;
    }

    private function get_jwKey()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://content.jwplatform.com/libraries/KB5zFt7A.js');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'host: content.jwplatform.com',
            'origin: https://content.jwplatform.com',
        ));
        session_write_close();
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if (!$error) {
            if (preg_match('/"key": "([^"]+)"/', $response, $key)) {
                return $key[1];
            }
        }
        return 'W7zSm81+mmIsg7F+fyHRKhF3ggLkTqtGMhvI92kbqf/ysE99';
    }

    private function jwplayer()
    {
        $stretching = get_option('stretching');

        $tracks = [];
        if (!empty($this->tracks)) {
            foreach ($this->tracks as $tr) {
                $tracks[] = [
                    'file' => BASE_URL . 'subtitle/?url=' . $tr['file'],
                    'label' => $tr['label']
                ];
            }
            $tracks[0]['default'] = true;
        }
        $this->tracks = $tracks;
        if(!empty($this->poster)) $this->poster = BASE_URL . 'poster/?url=' . encode($this->poster);

        $config = [
            'key'       => $this->get_jwKey(),
            'title'     => (string) $this->title,
            'autostart' => filter_var(get_option('autoplay'), FILTER_VALIDATE_BOOLEAN),
            'repeat'    => filter_var(get_option('repeat'), FILTER_VALIDATE_BOOLEAN),
            'mute'      => filter_var(get_option('mute'), FILTER_VALIDATE_BOOLEAN),
            'rewind'    => false,
            'image'     => $this->poster,
            'abouttext' => $this->abouttext,
            'aboutlink' => $this->aboutlink,
            'tracks'    => $this->tracks,
            'sources'   => $this->sources,
            'sharing'   => false,
            'controls'  => true,
            'hlshtml'   => true,
            'primary'   => 'html5',
            'preload'   => 'auto',
            'cast'      => (object) array('appid' => '00000000'),
            'androidhls'    => true,
            'stretching'    => (!empty($stretching) ? $stretching : 'uniform'),
            'displaytitle'  => filter_var(get_option('display_title'), FILTER_VALIDATE_BOOLEAN),
            'displaydescription'    => false,
            'playbackRateControls'  => filter_var(get_option('playback_rate'), FILTER_VALIDATE_BOOLEAN),
            'captions'  => [
                'color' => $this->subcolor,
                'backgroundOpacity' => 0,
            ],
            'aspectratio' => '16:9',
            "floating"  => false,
            "tracks"    => $tracks
        ];

        $skin = get_option('player_skin');
        if (!empty($skin)) {
            $config["skin"] = array(
                'name' => $skin,
                'url' => BASE_URL . 'assets/css/skin/' . get_option('player') . '/' . $skin . '.css'
            );
        }

        $logo = get_option('logo_file');
        if (!empty($logo)) {
            $hide = !empty(get_option('logo_hide')) ? true : false;
            $config['logo'] = [
                'file'  => $logo,
                'link'  => get_option('logo_open_link'),
                'hide'  => $hide,
                'position'  => get_option('logo_position')
            ];
        }

        if (filter_var(get_option('enable_share_button'), FILTER_VALIDATE_BOOLEAN)) {
            $config['sharing'] = [
                'link'      => $this->sharelink,
                'code'      => $this->embed_code(),
                'heading'   => 'Share ' . $this->title,
                'sites'     => ['facebook', 'twitter', 'email'],
            ];
        }

        if (!empty($this->vast_ads)) {
            $config['advertising'] = $this->vast_ads;
        }

        return $config;
    }

    private function embed_code()
    {
        return htmlentities('<iframe src="' . $this->sharelink . '" frameborder="0" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" width="640" height="320"></iframe>');
    }
}
