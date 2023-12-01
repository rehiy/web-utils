<?php

function page_api()
{
    // 创建桌面快捷方式
    $save_name = $_GET['save_name'] ?? '';
    $save_url = $_GET['save_url'] ?? '';
    if ($save_name && $save_url) {
        header("Content-Type: application/octet-stream");
        $filename = urldecode($save_name) . '.url'; //生成的文件名
        $encoded_filename = urlencode($filename);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);
        if (preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
            header('Content-Disposition:  attachment; filename="' . $encoded_filename . '"');
        } elseif (preg_match("/Firefox/", $_SERVER['HTTP_USER_AGENT'])) {
            header('Content-Disposition: attachment; filename*="' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        output_raw("[InternetShortcut]\nURL={$save_url}\nProp3=19,2\nIconIndex=1");
    }
    // 请求类型
    switch ($_GET['type'] ?? '') {
        case 'checkweixin':
            $txt_url = $_GET['txt_url'] ?? '';
            if (!preg_match('/(http:\/\/)|(https:\/\/)/i', $txt_url)) {
                $txt_url = 'http://' . $txt_url;
            }
            // 官方API接口
            $api = get_headers('http://mp.weixinbridge.com/mp/wapredirect?url=' . urlencode($txt_url), 1);
            if (isset($api['Location']['1'])) {
                if ($api['Location']['1'] == $txt_url) {
                    $code = 0;
                    $msg = '域名正常！';
                } else {
                    $code = 1;
                    $msg = '域名被拦截！';
                }
            }
            output_json(array(
                'code' => $code,
                'msg' => $msg,
                'status' => 1
            ));
        case 'check_url':
            $page = $_GET['page'] ?? 1;
            $url = $_GET['url'] ?? '';
            $str = browser($url);
            $count_str = '';
            $data = '';
            $list = array(array(), array(), 0);
            if ($str) {
                preg_match_all('/<a .*?href="(.*?)".*?>/is', $str, $ahref);
                $title = preg_replace("/.*<title>(.*?)<\/title>.*/is", '\\1', $str);
                $url_p = 'http://' . preg_replace("/(http[s]?:)?(\/\/)?([\w.]+)[\w\/]*[\w.]*\??[\w=&\+\%]*/is", '\\3', $url);
                $id = 1;
                $aLink = [];
                $aLink[] = array(
                    'url' => $url_p,
                    'title' => $title,
                    'id' => $id
                );
                $arr = array();
                foreach ($ahref['1'] as $key => $vo) {
                    $qdiv = substr($vo, 0, 1) != '#' && $vo != '/' && substr($vo, 0, 11) != 'javascript:';
                    if ($qdiv && $vo != $url_p && !in_array($vo, $arr)) {
                        $arr[] = $vo;
                        if (substr($vo, 0, 2) == '//') {
                            ++$id;
                            $aLink[] = array(
                                'url' => 'http:' . $vo,
                                'title' => '',
                                'id' => $id
                            );
                        } elseif (substr($vo, 0, 4) == 'http') {
                            ++$id;
                            $aLink[] = array(
                                'url' => $vo,
                                'title' => '',
                                'id' => $id
                            );
                        } elseif (substr($vo, 0, 1) == '/') {
                            ++$id;
                            $aLink[] = array(
                                'url' => $url_p . $vo,
                                'title' => '',
                                'id' => $id
                            );
                        } else {
                            ++$id;
                            $aLink[] = array(
                                'url' => $url_p . '/' . $vo,
                                'title' => '',
                                'id' => $id
                            );
                        }
                    }
                }
                $list = page($aLink, 20, $page);
                for ($i = 1; $i <= $list['1']; $i++) {
                    $count_str .= '<li class="page-number "><a href="javascript:;" style="' . ($i == $page ? 'background:#ccc' : '') . '" onclick="get_data(' . $i . ')">' . $i . '</a></li>';
                }
                foreach ($list['0'] as $key => $vo) {
                    $data .= '<tr class=""><td class="order">' . $vo['id'] . '</td><td class="title" id="tr_title_' . $vo['id'] . '">' . ($vo['title'] ? $vo['title'] : ' - ') . '</td><td class="owner" style="text-overflow: ellipsis;white-space: nowrap;overflow: hidden;"><a class="green" href="' . $vo['url'] . '" target="_blank">' . $vo['url'] . '</a></td><td class="title" id="tr_' . $vo['id'] . '"> - </td></tr>';
                }
            }
            output_json(array(
                'status' => $str ? 1 : 0,
                'data' => $data,
                'obj' => $list['0'],
                'total_count' => $list['2'],
                'count_str' => $count_str
            ));
        case 'single_url':
            $url = $_GET['url'] ?? '';
            $str = '';
            if ($url) {
                $str = url_title_code($url);
            }
            output_json($str);
        case 'camelcase':
            $id = $_GET['id'] ?? '';
            $text = $_GET['text'] ?? '';
            if ($id == 2) {
                $text = preg_replace_callback('/([^a-zA-Z][a-z])/', function ($m) {
                    return strtoupper(str_replace('_', '', $m[0]));
                }, ucfirst($text));
            } else {
                $text = preg_replace_callback('/(([A-Z]).*?([A-Z]))/', function ($m) {
                    return str_replace($m['3'], '_' . $m['3'], $m[0]);
                }, ucfirst($text));
                $text = strtolower($text);
            }
            output_json(array(
                'status' => $text ? 1 : 0,
                'msg' => $text
            ));
        default:
            output_json(array(
                'status' => 1,
                'msg' => null
            ));
    }
}
