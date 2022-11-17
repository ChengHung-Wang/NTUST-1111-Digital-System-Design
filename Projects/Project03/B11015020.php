<?php
use Encoder\DotEncoder;
use Encoder\KissEncoder;

require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/KissParser.php";
require_once __DIR__ . "/StateMinimization.php";
require_once __DIR__ . "/KissEncoder.php";
require_once __DIR__ . "/DotEncoder.php";

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

    $kiss_encoder = new KissEncoder();
    $dot_encoder = new DotEncoder();
    $file_kiss_output = @fopen($kiss_output, "w+");
    $file_dot_file = @fopen($dot_file, "w+");
    fwrite($file_kiss_output, $service->output($kiss_encoder));
    fwrite($file_dot_file, $service->output($dot_encoder));
    fclose($file_kiss_output);
    fclose($file_dot_file);
}