<?php
function guess($mess, &$res, $tmp = "", $n = 0)
{
    if ($n == strlen($mess))
    {
        array_push($res, $tmp);
        return;
    }
    if ($mess[$n] == "-")
    {
        guess($mess, $res, $tmp . "1", $n + 1);
        guess($mess, $res, $tmp . "0", $n + 1);
    }
    else
    {
        guess($mess, $res, $tmp . $mess[$n], $n + 1);
    }
}
$res = array();
$str = "00--11-";
guess($str, $res);
var_dump($res);