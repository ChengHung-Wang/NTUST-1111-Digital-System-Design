<?php
$n = 4;

for ($i = 0; $i < $n; $i++) {
    for ($j = $i + 1; $j < $n; $j++) {
        echo $i . " -> " . $j . PHP_EOL;
    }
}