<?php
namespace DB;
use Config\DB;
use SleekDB\Exceptions\IdNotAllowedException;
use SleekDB\Exceptions\InvalidArgumentException;
use SleekDB\Exceptions\IOException;
use SleekDB\Exceptions\JsonException;

class Rules extends Table {
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
            $this->debug->error("ERROR: \DB\Rules::insert(string, string, string, string);");
            return -1;
        }
    }

    /**
     * @param string $signal
     * @param int $variable_id
     * @return array
     */
    public function find_by_variable_id(int $variable_id, string $signal = "") : array
    {
        try {
            $result = $this->db->findBy(array("variable_id", "=", $variable_id), array("id" => "asc"));
            return $signal === "" ? $result : array_values(array_filter($result, function($el) use ($signal) {
                return $el["input"] === $signal;
            }))[0];
        } catch (IOException | InvalidArgumentException $e) {
            $this->debug->error("ERROR: \DB\Rules::find_by_variable_id(int, string);");
            return array();
        }
    }

    public function override_variable_id($from_id, $override_id) {
        try {
            $targets =
                $this->db->createQueryBuilder()
                    ->where(array("next_variable_id", "=", $from_id))
                    ->getQuery()->fetch();
            $this->db->update(array_map(function($el) use ($override_id) {
                $el["next_variable_id"] = $override_id;
                return $el;
            }, $targets));
        } catch (IOException | InvalidArgumentException $e) {
            $this->debug->error("ERROR: \DB\Rules::replace_variable_id(int, int);");
        }
    }

    /**
     * @param int $variable_id
     * @return void
     */
    public function del_by_variable_id(int $variable_id) : void
    {
        try {
            $this->db->deleteBy(array("variable_id", "=", $variable_id));
        } catch (IOException | InvalidArgumentException $e)
        {
            $this->debug->error("ERROR: \DB\Rules::del_by_variable_id(int);");
        }
    }
}