<?php
class anti_captcha
{
    private $apiKey = '';
    private $websiteURL = '';
    private $websiteKey = '';
    private $ch;

    function __construct()
    {
        $this->apiKey = get_option('anti_captcha');
        session_write_close();
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_ENCODING, "");
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if (defined('CURLOPT_TCP_FASTOPEN')) {
            curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
        }
        curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
        curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
    }

    function set_websiteURL($url = '')
    {
        $this->websiteURL = $url;
    }

    function set_websiteKey($gcaptcha = '')
    {
        $this->websiteKey = $gcaptcha;
    }

    function createTask()
    {
        curl_setopt($this->ch, CURLOPT_URL, 'https://api.anti-captcha.com/createTask');
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, '{
            "clientKey": "' . $this->apiKey . '",
            "task": {
                "type": "RecaptchaV2TaskProxyless",
                "websiteURL": "' . $this->websiteURL . '",
                "websiteKey": "' . $this->websiteKey . '"
            }
        }');
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        session_write_close();
        $response = curl_exec($this->ch);
        $err = curl_error($this->ch);
        if(!$err){
            return json_decode($response, true);
        }
        return FALSE;
    }

    function getTaskResult(int $taskId = 0)
    {
        curl_setopt($this->ch, CURLOPT_URL, 'https://api.anti-captcha.com/getTaskResult');
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, '{
            "clientKey": "' . $this->apiKey . '",
            "taskId": '. $taskId .'
        }');
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        session_write_close();
        $response = curl_exec($this->ch);
        $err = curl_error($this->ch);
        if (!$err) {
            return json_decode($response, true);
        }
        return FALSE;
    }

    function __destruct()
    {
        curl_close($this->ch);
    }
}
