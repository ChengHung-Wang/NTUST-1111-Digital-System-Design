<?php
namespace Config {
    use SleekDB\Query;
    class DB {
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

        public static $searchOptions = array(
            "minLength" => 2,
            "mode" => "or",
            "scoreKey" => "scoreKey",
            "algorithm" => Query::SEARCH_ALGORITHM["hits"]
        );
    }
}
