<?php
namespace DB;
require_once "./configurations.php";
require_once "./DB-Engine/Store.php";

use Config\DB;
use SleekDB\Exceptions\InvalidConfigurationException;
use SleekDB\Exceptions\IOException;
use SleekDB\Store;

class Table {
    public $variables;
    public $rules;
    public $implications;

    public function __construct()
    {
        try {
            $this->variables = new Store("variables", DB::$databaseDirectory, DB::$configuration);
            $this->rules = new Store("rules", DB::$databaseDirectory, DB::$configuration);
            $this->implications = new Store("implications", DB::$databaseDirectory, DB::$configuration);
        } catch (IOException | \SleekDB\Exceptions\InvalidArgumentException | InvalidConfigurationException $e) {
            echo "sql init field\n";
        }
    }
}

function drop_all($target = "") {
    if ($target === "")
        $target = DB::$databaseDirectory;

    if (is_dir($target)) {
        foreach(glob($target . '*', GLOB_MARK) as $file)
            drop_all($file);

        rmdir($target);
    } elseif(is_file($target))
        unlink($target);
}