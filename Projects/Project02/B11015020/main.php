<?php
require_once("./essential_prime_implicant.php");
require_once("./parser.php");

$service = new EssentialPrimeImplicant();

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
    $service->render();
    fwrite($file, $service->encode_str());
    fclose($file);
    echo $service->get_equation_str() . PHP_EOL;
    echo "Don't care: ";
    echo $service->get_dont_care_str() . PHP_EOL;
}
else
{
    $parser = new Parser($service);
    $parser->start();
}