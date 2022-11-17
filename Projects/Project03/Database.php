<?php
namespace DB;
require_once __DIR__ . "/Configurations.php";
require_once __DIR__ . "/DB-Engine/Store.php";

use Config\DB;
use Config\Debug;
use SleekDB\Exceptions\InvalidArgumentException;
use SleekDB\Exceptions\InvalidConfigurationException;
use SleekDB\Exceptions\IOException;
use SleekDB\Store;

class Table {
    /**
     * @var Store $db
     * @var Store $variables
     * @var Store $rules
     * @var Store $implications
     */
    public $db;
    public $variables;
    public $rules;
    public $implications;

    /**
     * debug console
     * @var Debug $debug
     */
    public $debug;

    /**
     * sql init
     */
    public function __construct()
    {
        try {
            $this->variables = new Store("variables", DB::$databaseDirectory, DB::$configuration);
            $this->rules = new Store("rules", DB::$databaseDirectory, DB::$configuration);
            $this->implications = new Store("implications", DB::$databaseDirectory, DB::$configuration);
            $this->debug = new Debug();
        } catch (IOException | InvalidArgumentException | InvalidConfigurationException $e) {
            echo "sql init field\n";
        }
    }

    /**
     * find by some columns name
     * @param string $col_name
     * @param string|int $content
     * @param bool $single
     * @return array
     */
    protected function find_by(string $col_name, $content, bool $single = true): array
    {
        try {
            $result = $this->db->createQueryBuilder()->where(array($col_name, "=", $content))->getQuery();
            return $single ? $result->first() : $result->fetch();
        } catch (IOException | InvalidArgumentException $e) {
            return array($this->db->getPrimaryKey() => -1);
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