<?php
namespace Config {
    require_once "./Color.php";
    use CLI\Colors;
    use SleekDB\Query;
    class DB
    {
        public static $databaseDirectory = __DIR__ . "/database/";

        public static $configuration = array(
            "auto_cache" => true,
            "cache_lifetime" => null,
            "timeout" => false,
            "primary_key" => "id",
            "search" => array(
                "min_length" => 2,
                "mode" => "or",
                "score_key" => "scoreKey",
                "algorithm" => Query::SEARCH_ALGORITHM["hits"]
            ),
            "folder_permissions" => 0777
        );
    }

    class Debug {
        // true = enable, false = disable
        public $status = true;
        private $cli;

        public function __construct()
        {
            $this->cli = new Colors();
        }

        public function error($mess, $end = "\n", $color = "black", $background = "red") : bool
        {
            if ($this->status) {
                echo $this->cli->getColoredString($mess, $color, $background);
                echo $this->cli->getColoredString($end);
            }
            return true;
        }

        public function success($mess, $end = "\n", $color = "white", $background = "green"): bool
        {
            if ($this->status) {
                echo $this->cli->getColoredString($mess, $color, $background);
                echo $this->cli->getColoredString($end);
            }
            return true;
        }

        public function info($mess, $end = "\n", $color = "white", $background = "dark_gray") : bool
        {
            if ($this->status) {
                echo $this->cli->getColoredString($mess, $color, $background);
                echo $this->cli->getColoredString($end);
            }
            return true;
        }

        public function warning($mess, $end = "\n", $color = "black", $background = "yellow") : bool
        {
            if ($this->status) {
                echo $this->cli->getColoredString($mess, $color, $background);
                echo $this->cli->getColoredString($end);
            }
            return true;
        }
    }
}
