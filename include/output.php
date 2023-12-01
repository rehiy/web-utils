<?php

/**
 * 输出文本
 * @param string $text 内容
 */
function output_raw($text)
{
    exit($text);
}

/**
 * 输出模板
 * @param string $act  模板名
 * @param array  $data 模板变量
 */
function output_tpl($act, $data = [])
{
    extract($data);
    if ($act == 'index') {
        include complie_template($act);
    } else {
        include complie_template('action/' . $act);
    }
}

/**
 * 输出json数据
 * @param mixed $data    返回的数据
 * @param int   $code    状态码
 * @param array $header  头部
 * @param array $options 参数
 */
function output_json($data, $code = 200, $header = [], $options = [])
{
    $json = json_encode(compact('data', 'code', 'header', 'options'));

    header('Content-Type: application/json');
    foreach ($header as $key => $value) {
        header("$key: $value");
    }

    http_response_code($code);
    exit($json);
}

/**
 * 编译模板
 * @param string $name 模板名
 * @return string
 */
function complie_template($name)
{
    // 检查模板文件
    $file = 'view/' . $name . '.html';
    if (!is_file($file)) {
        exit("View {$name} not found");
    }

    // 读取模板内容
    $template = file_get_contents($file);

    // 匹配 {if condition} 标签
    $pattern = '/\{if (.*?)\}/';
    $replacement = '<?php if ($1): ?>';
    $template = preg_replace($pattern, $replacement, $template);

    // 匹配 {elseif condition} 标签
    $pattern = '/\{elseif (.*?)\}/';
    $replacement = '<?php elseif ($1): ?>';
    $template = preg_replace($pattern, $replacement, $template);

    // 匹配 {else} 标签
    $pattern = '/\{else\}/';
    $replacement = '<?php else: ?>';
    $template = preg_replace($pattern, $replacement, $template);

    // 匹配 {/if} 标签
    $pattern = '/\{\/if\}/';
    $replacement = '<?php endif; ?>';
    $template = preg_replace($pattern, $replacement, $template);

    // 匹配 {for $i=0; $i<10; $i++} 标签
    $pattern = '/\{for (.*?)\}/';
    $replacement = '<?php for ($1): ?>';
    $template = preg_replace($pattern, $replacement, $template);

    // 匹配 {/for} 标签
    $pattern = '/\{\/for\}/';
    $replacement = '<?php endfor; ?>';
    $template = preg_replace($pattern, $replacement, $template);

    // 匹配 {foreach $array as $item} 标签
    $pattern = '/\{foreach (.*?) as (.*?)\}/';
    $replacement = '<?php foreach ($1 as $2): ?>';
    $template = preg_replace($pattern, $replacement, $template);

    // 匹配 {/foreach} 标签
    $pattern = '/\{\/foreach\}/';
    $replacement = '<?php endforeach; ?>';
    $template = preg_replace($pattern, $replacement, $template);

    // 匹配 ${variable} 标签
    $pattern = '/\$\{(.*?)\}/';
    $replacement = function ($matches) {
        $mat = explode('.', $matches[1], 2);
        if (count($mat) > 1) {
            $mat[0] .= '["' . str_replace('.', '"]["', $mat[1]) . '"]';
            $mat[0] = preg_replace('/\["(\d+)"\]/', '[$1]', $mat[0]);
        }
        return '<?php echo $' . $mat[0] . '; ?>';
    };
    $template = preg_replace_callback($pattern, $replacement, $template);

    // 匹配 {include 'name'} 标签
    $pattern = '/\{include file="(.+)" \/\}/';
    $replacement = '<?php include complie_template(\'$1\'); ?>';
    $template = preg_replace($pattern, $replacement, $template);

    // 保存编译后的模板
    $sdir = dirname($tplc = 'cache/' . $name . '.php');
    is_dir($sdir) ||  mkdir($sdir, 0777, true);
    file_put_contents($tplc, $template);

    // 返回编译后的模板
    return $tplc;
}
