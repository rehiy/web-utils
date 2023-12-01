<?php

function page_act()
{
    $act = $_GET['act'] ?? 'index';
    $data = array(
        'pages' => include('dataset/pages.php')
    );
    switch ($act) {
        case 'uuid':
            $data['uuid_number'] = $_GET['uuid_number'] ?? 1;
            $data['uuid_letter'] = $_GET['uuid_letter'] ?? 2;
            $data['uuid'] = uuid($data['uuid_number'], $data['uuid_letter']);
            break;
        case 'guid':
            $data['guid_number'] = $_GET['guid_number'] ?? 1;
            $data['guid_letter'] = $_GET['guid_letter'] ?? 1;
            $array = array();
            for ($i = 0; $data['guid_number'] > $i; $i++) {
                $guid = create_guid();
                if (!in_array($guid, $array)) {
                    $array[] = ($data['guid_letter'] == 1) ? strtoupper($guid) : strtolower($guid);
                } else {
                    $i--;
                }
            }
            $data['guid'] = $array;
            break;
        case 'md5':
            $data['txt_md5'] = $_GET['txt_md5'] ?? '';
            $md532 = md5($data['txt_md5']);
            $md516 = substr($md532, 8, 16);
            $data['md532_d'] = strtoupper($md532);
            $data['md532_x'] = strtolower($md532);
            $data['md516_d'] = strtoupper($md516);
            $data['md516_x'] = strtolower($md516);
            break;
        case 'caiji':
            $data['url'] = $_GET['url'] ?? '';
            $data['content'] = $data['url'] ? browser($data['url']) : '';
            break;
        case 'ip':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ip = $_POST['ip'] ?? '';
                header('Location: /ip/?ip=' . $ip);
                exit();
            }
            $data['getip'] = get_client_ip();
            $data['get_os_browser'] = get_os_browser();
            $ipdata = request('https://ipip.rehi.org/json/cn/' . $data['getip']);
            if ($ipdata) {
                $data = array_merge($data, json_decode($ipdata, true));
            }
            if ($search = $_GET['ip'] ?? '') {
                $data['ym']['search'] = $search;
                $data['ym']['ip'] = gethostbyname($search);
                $data['ym']['ip2long'] = ip2long($data['ym']['ip']);
                $ipv4 = preg_replace('/(\d+)..*/', '\\1', $data['ym']['ip']);
                if ('1' <= $ipv4 && $ipv4 <= '126') {
                    $data['ym']['fw'] = '1.0.0.1 - 126.155.255.254';
                } elseif ('128' <= $ipv4 && $ipv4 <= '191') {
                    $data['ym']['fw'] = '128.0.0.1 - 191.255.255.254';
                } elseif ('192' <= $ipv4 && $ipv4 <= '223') {
                    $data['ym']['fw'] = '192.0.0.1 - 223.255.255.254';
                }
                $ipdata = request('https://ipip.rehi.org/json/cn/' . $data['ym']['ip']);
                if ($ipdata) {
                    $data['ym'] = array_merge($data['ym'], json_decode($ipdata, true));
                }
            }
            break;
        case 'favicon':
            $data['upmsg'] = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $upimage = $_FILE['upimage'] ?? '';
                $getInfo = $upimage->getInfo();
                if (isset($getInfo['tmp_name']) && $getInfo['tmp_name'] && is_uploaded_file($getInfo['tmp_name'])) {
                    if ($getInfo['type'] > 210000) {
                        $data['upmsg'] = "<font color=\"red\">你上传的文件体积超过了限制 最大不能超过200K</font>";
                    } else {
                        $fileext = array("image/pjpeg", "image/gif", "image/x-png", "image/png", "image/jpeg", "image/jpg");
                        if (!in_array($getInfo['type'], $fileext)) {
                            $data['upmsg'] = "<font color=\"red\">你上传的文件格式不正确 仅支持 jpg，gif，png</font>";
                        } else {
                            $type = substr(strrchr($getInfo['name'], '.'), 1);
                            switch ($type) {
                                case 'pjpeg':
                                case 'jpeg':
                                case 'jpg':
                                    $im = imagecreatefromjpeg($getInfo['tmp_name']);
                                    break;
                                case 'x-png':
                                case 'png':
                                    $im = imagecreatefrompng($getInfo['tmp_name']);
                                    break;
                                case 'gif':
                                    $im = imagecreatefromgif($getInfo['tmp_name']);
                                    break;
                                default:
                                    $im = null;
                            }
                            if ($im) {
                                $imginfo = getimagesize($getInfo['tmp_name']);
                                if (!is_array($imginfo)) {
                                    $data['upmsg'] = "<font color=\"red\">图形格式错误！</font>";
                                } else {
                                    switch ($_GET['favicon_size'] ?? '') {
                                        case 1;
                                            $resize_im = imagecreatetruecolor(16, 16);
                                            $size = 16;
                                            break;
                                        case 2;
                                            $resize_im = imagecreatetruecolor(32, 32);
                                            $size = 32;
                                            break;
                                        case 3;
                                            $resize_im = imagecreatetruecolor(48, 48);
                                            $size = 48;
                                            break;
                                        case 4;
                                            $resize_im = imagecreatetruecolor(64, 64);
                                            $size = 64;
                                            break;
                                        case 5;
                                            $resize_im = imagecreatetruecolor(128, 128);
                                            $size = 128;
                                            break;
                                        default;
                                            $resize_im = imagecreatetruecolor(32, 32);
                                            $size = 32;
                                            break;
                                    }
                                    imagecopyresampled($resize_im, $im, 0, 0, 0, 0, $size, $size, $imginfo[0], $imginfo[1]);
                                    require_once APP_ROOT . 'include/class/ico.php';
                                    $icon = new Ico();
                                    $gd_image_array = array($resize_im);
                                    $icon_data = $icon->GD2ICOstring($gd_image_array);
                                    header("Accept-Ranges: bytes");
                                    header("Accept-Length: " . strlen($icon_data));
                                    header("Content-type: application/octet-stream");
                                    header("Content-Disposition: attachment; filename=" . 'favicon.ico');
                                    output_raw($icon_data);
                                }
                            } else {
                                $data['upmsg'] = "<font color=\"red\">生成错误请重试！</font>";
                            }
                        }
                    }
                }
            }
            break;
        case 'gzip':
            $data['q'] = $_GET['q'] ?? '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data['q'] = str_replace(array('http://', 'https://'), '', $data['q']);
                header('Location: /gzip/?q=' . $data['q']);
                exit();
            }
            if ($data['q']) {
                $data['url'] = preg_replace("/([\w.]+)[\w\/]*[\w.]*\??[\w=&\+\%]*/is", '\\1', $data['q']);
                $data['gzip'] = filter_var('http://' . $data['q'], FILTER_VALIDATE_URL) !== false ? url_header($data['q']) : '';
            }
            break;
        case 'refresh':
            $url = $_GET['url'] ?? '';
            if ($url) {
                $content = browser($url);
                output_raw($content);
            }
            break;
        case 'checkkeyword':
            $data['url'] = $_GET['txt_url'] ?? '';
            $data['keyword'] = $_GET['txt_keyword'] ?? '';
            if ($data['url'] && $data['keyword']) {
                $str = browser($data['url']);
                $str = html_to_text($str);
                $data['html_strlen'] = mb_strlen($str, 'utf-8');
                $data['html_gjccd'] = mb_strlen($data['keyword'], 'utf-8');
                $data['html_gjcsl'] = substr_count($str, $data['keyword']);
                $data['html_gjczcd'] = $data['html_gjccd'] * $data['html_gjcsl'];
                $data['html_mdjgjs'] = @round(($data['html_gjczcd'] / $data['html_strlen'] * 100), 1);
            }
            break;
        case 'chameta':
            $data['url'] = $_GET['txt_url'] ?? '';
            $data['title'] = '';
            $data['title_len'] = 0;
            $data['keywords'] = '';
            $data['keywords_len'] = 0;
            $data['description'] = '';
            $data['description_len'] = 0;
            if ($data['url']) {
                $str = browser($data['url']);
                preg_match("/<title>([\w\W]*?)<\/title>/is", $str, $match);
                $data['title'] = isset($match['1']) ? $match['1'] : null;
                $data['title_len'] = mb_strlen($data['title'], 'utf-8');
                preg_match("/<meta\s+name=\"keywords\"\s+content=\"([\w\W]*?)\"\s+\/>/is", $str, $match);
                $data['keywords'] = isset($match['1']) ? $match['1'] : null;
                $data['keywords_len'] = mb_strlen($data['keywords'], 'utf-8');
                preg_match("/<meta\s+name=\"description\"\s+content=\"([\w\W]*?)\"\s+\/>/is", $str, $match);
                $data['description'] = isset($match['1']) ? $match['1'] : null;
                $data['description_len'] = mb_strlen($data['description'], 'utf-8');
            }
            break;
        case 'webstatus':
            $data['url'] = $_GET['url'] ?? '';
            $data['ip'] = '';
            if ($data['url']) {
                $web = url_status($data['url']);
                $data['ip'] = $web['ip'];
                $data['code'] = $web['code'];
                $data['head'] = $web['head'];
            }
            break;
        case 'whois':
            $data['url'] = $_GET['whois'] ?? '';
            $data['whois'] = '';
            $data['domain'] = array();
            if ($data['url']) {
                if (filter_var($data['url'], FILTER_VALIDATE_IP)) {
                    $type = 'ip';
                } else {
                    $type = 'domain';
                    if (filter_var($data['url'], FILTER_VALIDATE_URL)) {
                        $data['url'] = parse_url($data['url'])['host'];
                    }
                    if (substr($data['url'], 0, 4) == 'www.') {
                        $data['url'] = substr($data['url'], 4);
                    }
                    if (!check_domain($data['url'])) {
                        break;
                    }
                }
                $whois = whois_query($data['url']);
                if (preg_match('/Registrar:\s+(.*)/', $whois, $domain)) {
                    $data['domain']['注册商'] = $domain['1'];
                }
                if (preg_match('/Registrant[:]?\s+(.*)/', $whois, $domain)) {
                    $data['domain']['联系人'] = $domain['1'];
                }
                if (preg_match('/(Registrar\s+Abuse|Registrant)\s+Contact\s+Email[:]?\s+(.*)/', $whois, $domain)) {
                    $data['domain']['联系邮箱'] = $domain['2'];
                }
                if (preg_match('/(Registrar\s+Abuse|Registrant)\s+Contact\s+Phone[:]?\s+(.*)/', $whois, $domain)) {
                    $data['domain']['联系电话'] = $domain['2'];
                }
                if (preg_match('/Updated\s+Date[:]?\s+(.*)/', $whois, $domain)) {
                    $data['domain']['更新时间'] = $domain['1'];
                }
                if (preg_match('/(Registration\s+Time|Creation\s+Date)[:]?\s+(.*)/', $whois, $domain)) {
                    $data['domain']['创建时间'] = $domain['2'];
                }
                if (preg_match('/(Expiration\s+Time|Registry\s+Expiry\s+Date)[:]?\s+(.*)/', $whois, $domain)) {
                    $data['domain']['过期时间'] = $domain['2'];
                }
                if (preg_match('/Registrar\s+WHOIS\s+Server[:]?\s+(.*)/', $whois, $domain)) {
                    $data['domain']['域名服务器'] = $domain['1'];
                }
                if (preg_match_all('/Name\s+Server?[:]\s+(.*)/', $whois, $domain)) {
                    $data['domain']['DNS'] = $domain['1'];
                }
                if (preg_match('/Domain\s+Status[:]?\s+(.*)/', $whois, $domain)) {
                    $data['domain']['状态'] = $domain['1'];
                }
                unset($domain);
                $whois = "'" . str_replace(["\r", '\'', "\n"], ['', "\'", "'+\"\\n\"+\r'"], $whois) . '\'';
                $data['whois'] = $whois;
            }
            break;
        case 'chaicp':
            $data['url'] = $_GET['icp'] ?? '';
            $data['icp'] = array();
            if ($data['url']) {
                if (filter_var($data['url'], FILTER_VALIDATE_URL)) {
                    $data['url'] = parse_url($data['url'])['host'];
                }
                if (substr($data['url'], 0, 4) == 'www.') $data['url'] = substr($data['url'], 4);
                if (!check_domain($data['url'])) {
                    $data['code'] = 400;
                    $data['icp']['msg'] = '域名格式不正确';
                    break;
                }
                try {
                    $result = icp_query($data['url']);
                    if ($result['total'] == 0) {
                        $data['code'] = 502;
                        $data['icp']['msg'] = '未查询到备案信息';
                        break;
                    }
                    $data['code'] = 200;
                    $data['icp'] = ['网站域名' => $result['data'][0]['domain'], 'ICP备案号' => $result['data'][0]['webLicence'], '主办单位名称' => $result['data'][0]['unitName'], '主办单位性质' => $result['data'][0]['unitType'], '审核日期' => $result['data'][0]['updateTime'], '是否限制接入' => $result['data'][0]['limitAccess']];
                } catch (Exception $e) {
                    $data['code'] = 501;
                    $data['icp']['msg'] = $e->getMessage();
                    break;
                }
            }
            break;
        case 'lishishangdejintian':
            $lsjt = browser('https://api.oick.cn/lishi/api.php');
            $data['list'] = array();
            if ($lsjt) {
                $lsjt = str_replace('""', '"', $lsjt);
                $lsjt = json_decode($lsjt, true);
                $data['list'] = ($lsjt['code'] == 200) ? $lsjt['result'] : array();
            }
            break;
    }
    output_tpl($act, $data);
}
