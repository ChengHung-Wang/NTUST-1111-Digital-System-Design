<?php
require_once("./robdd.php");
require_once("./parser.php");
$service = new ROBDD();

if (isset($argv[1]) && isset($argv[2])) {
    $pla_file = $argv[1];
    $dot_file = $argv[2];
    if (!file_exists($pla_file))
    {
        die("ERR: FILE_NOT_EXISTS(.pla file: $pla_file)\n");
    }
    $file = @fopen($dot_file, "w+");
    if (! $file) {
        die("ERR: PERMISSION_DENY in .dot file(check read/write permission)");
    }

    $parser = new Parser($service, $pla_file);
    $service->render(true);
    $service->print_table();
    fwrite($file, $service->encode_dot());
    fclose($file);
    echo $service->get_equation_str();
}
else
{
    $parser = new Parser($service);
    $parser->start();
}