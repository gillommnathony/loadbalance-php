<?php
function getServerMemoryUsage($getPercentage = true)
{
    $memoryTotal = null;
    $memoryFree = null;

    if (stristr(PHP_OS, "win")) {
        // Get total physical memory (this is in bytes)
        $cmd = "wmic ComputerSystem get TotalPhysicalMemory";
        @exec($cmd, $outputTotalPhysicalMemory);

        // Get free physical memory (this is in kibibytes!)
        $cmd = "wmic OS get FreePhysicalMemory";
        @exec($cmd, $outputFreePhysicalMemory);

        if ($outputTotalPhysicalMemory && $outputFreePhysicalMemory) {
            // Find total value
            foreach ($outputTotalPhysicalMemory as $line) {
                if ($line && preg_match("/^[0-9]+\$/", $line)) {
                    $memoryTotal = $line;
                    break;
                }
            }

            // Find free value
            foreach ($outputFreePhysicalMemory as $line) {
                if ($line && preg_match("/^[0-9]+\$/", $line)) {
                    $memoryFree = $line;
                    $memoryFree *= 1024;  // convert from kibibytes to bytes
                    break;
                }
            }
        }
    } else {
        if (is_readable("/proc/meminfo")) {
            $stats = @file_get_contents("/proc/meminfo");

            if ($stats !== false) {
                // Separate lines
                $stats = strtr($stats, array("\r\n" => "\n", "\n\r" => "\n", "\r" => "\n"));
                $stats = explode("\n", $stats);

                // Separate values and find correct lines for total and free mem
                foreach ($stats as $statLine) {
                    $statLineData = explode(":", trim($statLine));

                    //
                    // Extract size (TODO: It seems that (at least) the two values for total and free memory have the unit "kB" always. Is this correct?
                    //

                    // Total memory
                    if (count($statLineData) == 2 && trim($statLineData[0]) == "MemTotal") {
                        $memoryTotal = trim($statLineData[1]);
                        $memoryTotal = explode(" ", $memoryTotal);
                        $memoryTotal = $memoryTotal[0];
                        $memoryTotal *= 1024;  // convert from kibibytes to bytes
                    }

                    // Free memory
                    if (count($statLineData) == 2 && trim($statLineData[0]) == "MemFree") {
                        $memoryFree = trim($statLineData[1]);
                        $memoryFree = explode(" ", $memoryFree);
                        $memoryFree = $memoryFree[0];
                        $memoryFree *= 1024;  // convert from kibibytes to bytes
                    }
                }
            }
        }
    }

    if (is_null($memoryTotal) || is_null($memoryFree)) {
        return null;
    } else {
        if ($getPercentage) {
            return (100 - ($memoryFree * 100 / $memoryTotal));
        } else {
            return array(
                "total" => $memoryTotal,
                "free" => $memoryFree,
            );
        }
    }
}

function getNiceFileSize($bytes, $binaryPrefix = true)
{
    if ($binaryPrefix) {
        $unit = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        if ($bytes == 0) return '0 ' . $unit[0];
        return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . ' ' . (isset($unit[$i]) ? $unit[$i] : 'B');
    } else {
        $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        if ($bytes == 0) return '0 ' . $unit[0];
        return @round($bytes / pow(1000, ($i = floor(log($bytes, 1000)))), 2) . ' ' . (isset($unit[$i]) ? $unit[$i] : 'B');
    }
}

function getServerCpuUsage()
{
    if (stristr(PHP_OS, 'win')) {
        $wmi = new COM("Winmgmts://");
        $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");

        $cpu_num = 0;
        $load_total = 0;

        foreach ($server as $cpu) {
            $cpu_num++;
            $load_total += $cpu->loadpercentage;
        }

        return intval($load_total / $cpu_num);
    } else {
        $sys_load = sys_getloadavg();
        return (int) $sys_load[0];
    }
    return false;
}

function getServerCpuThreads()
{
    return (int) ((PHP_OS_FAMILY === 'Windows') ? (getenv("NUMBER_OF_PROCESSORS") + 0) : substr_count(@file_get_contents("/proc/cpuinfo"), "processor"));
}

function getSubtitles($video = 0)
{
    global $db;

    if (!empty($video)) {
        $sub = $db->prepare("SELECT `language`, `link` FROM `tb_subtitles` WHERE `vid`=? LIMIT 1");
        $sub->execute(array($video));
        return $sub->fetch(\PDO::FETCH_ASSOC);
    }
    return FALSE;
}

function create_alert($type = '', $message = '', $header = null)
{
    $_SESSION['adm-type'] = $type;
    $_SESSION['adm-message'] = $message;

    if (!empty($header)) {
        @header("location:" . $header);
        return;
    }
}

function show_alert()
{
    if (isset($_SESSION['adm-type'])) {
        $type = strtolower($_SESSION['adm-type']);
        $message = $_SESSION['adm-message'];
        unset($_SESSION['adm-message']);
        unset($_SESSION['adm-type']);

        return "<div class='alert alert-$type'>$message</div>";
    }
}

function deleteDir($dirPath)
{
    if (is_dir($dirPath)) {
        $dir = new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS);
        foreach (new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST) as $filename => $file) {
            if (is_file($filename))
                if(is_writeable($filename)) @unlink($filename);
            else
                deleteDir($filename);
        }
        return @rmdir($dirPath);
    } else {
        return true;
    }
    return FALSE;
}

function delete_video_cache($key = '')
{
    global $InstanceCache;

    if (!empty($key)) {
        try {
            $ex = explode('~', $key);
            $cex = count($ex) - 1;
            unset($ex[$cex]);
            // hapus cache streaming
            $result[] = @unlink(BASE_DIR . 'cache/streaming/curl~' . $key . '.txt');
            // hapus cookie
            $result[] = @unlink(BASE_DIR . 'cookies/' . $key . '.txt');
            // hapus cache embed
            $result[] = deleteDir(BASE_DIR . 'cache/embed');
            // hapus cache sources
            $result[] = $InstanceCache->deleteItem($key);
            // hapus cache fileinfo
            $result[] = $InstanceCache->deleteItem('video~' . implode('~', $ex) . '~Default');
            $result[] = $InstanceCache->deleteItem('video~' . implode('~', $ex) . '~Original');
            $result[] = $InstanceCache->deleteItem('video~' . implode('~', $ex) . '~360p');
            $result[] = $InstanceCache->deleteItem('video~' . implode('~', $ex) . '~480p');
            $result[] = $InstanceCache->deleteItem('video~' . implode('~', $ex) . '~720p');
            $result[] = $InstanceCache->deleteItem('video~' . implode('~', $ex) . '~1080p');
            // hapus cache download
            $result[] = $InstanceCache->deleteItem('download_' . implode('~', $ex));
            return TRUE;
        } catch (\Exception $e) {
            error_log('delete video cache => ' . $e->getMessage());
        }
    }
    return FALSE;
}

function delete_video_info_cache()
{
    global $InstanceCache;
    try {
        $deleted = $InstanceCache->deleteItemsByTags(['mime_info', 'video_length']);
        return $deleted;
    } catch (\Exception $e) {
        error_log('delete video info cache => ' . $e->getMessage());
    }
    return FALSE;
}

function delete_lb_video_cache($key = '', $token = '', $ip = '', $ua = '')
{
    global $db;

    if (!empty($key)) {
        $data['action'] = 'delete_video_cache';
        $data['token'] = $token;
        $data['key'] = $key;
        $data['ip'] = $ip;
        $data['ua'] = $ua;

        $lbs = $db->prepare("SELECT link FROM tb_loadbalancers WHERE `status`=1");
        $lbs->execute();
        $rows = $lbs->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows) {
            $links = array_column($rows, 'link');
            $mh = curl_multi_init();
            $ch = [];
            foreach ($links as $i => $link) {
                $link = rtrim($link, '/');
                $host = parse_url($link, PHP_URL_HOST);
                $port = parse_URL($link, PHP_URL_PORT);
                if (empty($port)) {
                    $port = parse_url($link, PHP_URL_SCHEME) == 'https' ? 443 : 80;
                }
                $ipv4 = gethostbyname($host);
                $resolveHost = implode(':', array($host, $port, $ipv4));

                $ch[$i] = curl_init($link . '/administrator/api.php');
                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch[$i], CURLOPT_RESOLVE, [$resolveHost]);
                curl_setopt($ch[$i], CURLOPT_ENCODING, "");
                curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                curl_setopt($ch[$i], CURLOPT_POST, 1);
                curl_setopt($ch[$i], CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch[$i], CURLOPT_USERAGENT, USER_AGENT);
                curl_multi_add_handle($mh, $ch[$i]);
            }

            $active = null;
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($mh) == -1) {
                    usleep(10);
                }
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }

            $result = [];
            foreach ($links as $i => $link) {
                $status = curl_getinfo($ch[$i], CURLINFO_HTTP_CODE);
                if ($status > 0 && $status !== 404) {
                    $response = curl_multi_getcontent($ch[$i]);
                    $json = json_decode($response, true);
                    $result[] = [
                        'server' => $link,
                        'status' => $json['status'],
                        'message' => $json['message']
                    ];
                    error_log($link . ' => ' . $json['message']);
                } else {
                    $result[] = [
                        'server' => $link,
                        'status' => 'fail',
                        'message' => 'Failed! Not getting any response from the load balancer server.'
                    ];
                    error_log($link . ' => Failed! Not getting any response from the load balancer server.');
                }
                curl_multi_remove_handle($mh, $ch[$i]);
            }
            curl_multi_close($mh);

            return $result;
        } else {
            return TRUE;
        }
    }
    return FALSE;
}
