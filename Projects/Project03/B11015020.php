<?php

require_once "./database.php";
require_once "./kiss_parser.php";
require_once "./state_minimization.php";

$service = new StateMinimization();
if (isset($argv[1]) && isset($argv[2]) && isset($argv[3])) {
    $kiss_input = $argv[1];
    $kiss_output = $argv[2];
    $dot_file = $argv[3];

    if (!file_exists($kiss_input))
    {
        die("ERR: FILE_NOT_EXISTS(.pla file: $kiss_input)\n");
    }
    $file = @fopen($dot_file, "w+");
    if (! $file) {
        die("ERR: PERMISSION_DENY in .dot file(check read/write permission)");
    }
    $parser = new KissParser($service, $kiss_input);
    $parser->analyze();
    $service->render();
    
//    $service->render(true);
//    fwrite($file, $service->encode_dot());
//    fclose($file);
//    echo $service->get_equation_str();
}