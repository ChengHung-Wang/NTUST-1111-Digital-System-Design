<?php
$data = array(
    0 => array(
        array("V"), // -01-
        array("X") // --11
    ),
    1 => array(
        array("V"), // -01-
        array("W"), // 10--
        array("X"), // --11
        array("Z") // 1--1
    ),
    2 => array(
        array("W"), // 10--
        array("Z") // 1--1
    )
);

for ($index = 0; $index < count($data) - 1; $index ++)
{
    $first = $data[$index];
    $second = $data[$index + 1];
    $second_temp = array();
    foreach($first as &$first_item)
    {
        foreach($second as &$second_item)
        {
            $second_item_temp = $second_item;
            foreach ($first_item as $content)
            {
                array_push($second_item_temp, $content);
            }
            array_push($second_temp, $second_item_temp);
        }
    }
    $data[$index + 1] = array_filter(array_map(function($item)
    {
        sort($item);
        return array_unique($item);
    }, $second_temp), function($item)
    {
        return count($item) >= 2;
    });
    $uni_index = array();
    $uni_content = array();
    foreach($data[$index + 1] as $key => $item)
    {
        $uni_index[join("", $item)] = $key;
    }
    foreach ($uni_index as $item)
    {
        array_push($uni_content, $data[$index + 1][$item]);
    }
    $data[$index + 1] = $uni_content;
}
var_dump($data[count($data) - 1]);