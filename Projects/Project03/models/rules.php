<?php
namespace DB;
use Config\DB;
use SleekDB\Exceptions\IdNotAllowedException;
use SleekDB\Exceptions\InvalidArgumentException;
use SleekDB\Exceptions\IOException;
use SleekDB\Exceptions\JsonException;

class Rules extends Table {
    protected $db;

    /**
     * data table init
     */
    public function __construct()
    {
        parent::__construct();
        $this->db = $this->rules;
    }

    /**
     * @param string $from
     * @param string $input
     * @param string $output
     * @param string $next
     * @return int last insert id
     */
    public function insert(string $from, string $input, string $output, string $next): int
    {
        try {
            $variables_obj = new Variables();
            $col_key = DB::$configuration["primary_key"];
            $from_id = $variables_obj->find_by_name($from)[$col_key];
            $next_id = $variables_obj->find_by_name($next)[$col_key];
            if ($from_id === -1 || $next_id === -1)
                return -1;
            return $this->db->insert(array(
                "variable_id" => $from_id,
                "input" => $input,
                "output" => $output,
                "next_variable_id" => $next_id
            ))[$col_key];
        } catch (IOException | IdNotAllowedException | InvalidArgumentException | JsonException $e) {
            return -1;
        }
    }


}