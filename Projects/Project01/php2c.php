<?php
$fileStr = file_get_contents('main.php');
$newStr  = '';

function remove_hint(&$newStr, $fileStr) {
    $commentTokens = array(T_COMMENT);
    if (defined('T_DOC_COMMENT')) {
        $commentTokens[] = T_DOC_COMMENT; // PHP 5
    }

    if (defined('T_ML_COMMENT')) {
        $commentTokens[] = T_ML_COMMENT;  // PHP 4
    }

    $tokens = token_get_all($fileStr);

    foreach ($tokens as $token) {
        if (is_array($token)) {
            if (in_array($token[0], $commentTokens)) {
                continue;
            }

            $token = $token[1];
        }

        $newStr .= $token;
    }
}
function get_includes($fileStr) {
    $regex = '/require_once\((\"|\')*.+(\"|\')\)\;/';
    preg_match_all($regex, $fileStr, $matches, PREG_OFFSET_CAPTURE);
    return (array_map(function($item) {
        return $item[0];
    }, array_filter($matches[0], function($match) {
        foreach ($match as $item) {
            return count(explode("require_once", $item)) >= 2;
        }
    })));
}

remove_hint($newStr, $fileStr);

while(count(get_includes($newStr)) > 0) {
    foreach (get_includes($newStr) as $match) {
        $replace_target = $match;
        $start = '/require_once\((\"|\')/';
        $end = '/(\"|\')\)\;/';
        $file_name = preg_replace($start, '', $replace_target);
        $file_name = preg_replace($end, '', $file_name);
        $newStr = str_replace(
            $replace_target,
            str_replace("?>", "", str_replace("<?php", '', file_get_contents($file_name))),
            $newStr
        );
    }
}
$new_str = ""; // temp
remove_hint($new_str, $newStr);
$newStr = str_replace("\r", "", $new_str);
$newStr = str_replace("\"", "\\\"", $new_str);

$writeCode =  '#define PHP_PROG "'  . join("\"\\\n\"", array_filter(explode("\n", $newStr), function($el) {
    return $el != "";
})) . "\"\n\n" . file_get_contents("php7engine.c");
$file = fopen("main.c", "w") or die("Unable to open file!");
fwrite($file, $writeCode);
fclose($file);