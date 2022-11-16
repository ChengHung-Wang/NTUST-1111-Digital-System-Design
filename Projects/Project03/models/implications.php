<?php
namespace DB;
use SleekDB\Exceptions\IdNotAllowedException;
use SleekDB\Exceptions\InvalidArgumentException;
use SleekDB\Exceptions\IOException;
use SleekDB\Exceptions\JsonException;

class Implications extends Table {
    public function __construct()
    {
        parent::__construct();
        $this->db = $this->implications;
    }

    /**
     * @param int $first_judge_var_id
     * @param int $second_judge_var_id
     * @return int last insert id
     */
    public function insert(int $first_judge_var_id, int $second_judge_var_id): int
    {
        try {
            return $this->db->insert(array(
                "first_judge_var_id" => $first_judge_var_id,
                "second_judge_var_id" => $second_judge_var_id,
                "disabled" => false
            ))["id"];
        } catch (IOException | InvalidArgumentException | JsonException | IdNotAllowedException $e)
        {
            return -1;
        }
    }

    /**
     * set implication block to disabled
     * @param int $id
     * @param bool $set
     * @return void
     */
    public function set_disabled(int $id, bool $set = true) : void
    {
        try {
            $this->db->updateById($id, array(
                "disabled" => $set
            ));
        } catch (IOException | InvalidArgumentException | JsonException $e) {
            die("ERROR: set disabled field(set id:$id to $set).\n");
        }
    }

    /**
     * get all implication block detail
     * @return array|void
     */
    public function get()
    {
        try {
            $rules_table = $this->rules;
            return array_map(function($el) {
                $el["diff_of_output"] = array();
                foreach ($el["first"] as $first) {
                    if (!isset($el["diff_of_output"][$first["input"]])) {
                        $el["diff_of_output"][$first["input"]] = array();
                    }
                    array_push($el["diff_of_output"][$first["input"]], $first["output"]);
                }
                foreach ($el["second"] as $second) {
                    if (!isset($el["diff_of_output"][$second["input"]])) {
                        $el["diff_of_output"][$second["input"]] = array();
                    }
                    array_push($el["diff_of_output"][$second["input"]], $second["output"]);
                }
                return $el;
            }, $this->db
                ->createQueryBuilder()
                ->join(function ($el) use ($rules_table) {
                    return $rules_table->findBy(["variable_id", "=", $el["first_judge_var_id"]]);
                }, "first")
                ->join(function ($el) use ($rules_table) {
                    return $rules_table->findBy(["variable_id", "=", $el["second_judge_var_id"]]);
                }, "second")
                ->orderBy(array("id" => "asc"))
                ->getQuery()->fetch());

        } catch (IOException | InvalidArgumentException $e) {
            die("ERROR: get all implications detail.\n");
        }
    }

    /**
     * @param int $first_variable_id
     * @param int $second_variable_id
     * @return bool
     * @throws IOException
     * @throws InvalidArgumentException
     */
    public function is_disabled(int $first_variable_id, int $second_variable_id): bool
    {
        if ($first_variable_id === $second_variable_id) {
            return false;
        }
        $result = $this->db->createQueryBuilder()
            ->where(array(
                array("first_judge_var_id", "=", $first_variable_id),
                array("second_judge_var_id", "=", $second_variable_id),
                array("disabled", "=", true)
            ))
            ->orWhere(array(
                array("first_judge_var_id", "=", $second_variable_id),
                array("second_judge_var_id", "=", $first_variable_id),
                array("disabled", "=", true)
            ))
            ->getQuery()
            ->fetch();
//        if ($first_variable_id == 2 && $second_variable_id == 4)
//        {
//            $this->debug->info(json_encode($this->db->createQueryBuilder()
//                ->where(array(
//                    array("first_judge_var_id", "=", $first_variable_id),
//                    array("second_judge_var_id", "=", $second_variable_id),
//                ))
//                ->orWhere(array(
//                    array("first_judge_var_id", "=", $second_variable_id),
//                    array("second_judge_var_id", "=", $first_variable_id),
//                ))
//                ->getQuery()
//                ->fetch(), JSON_PRETTY_PRINT));
//        }
//        $this->debug->info(json_encode(array(
//            "data" => $result,
//            "first" => $first_variable_id,
//            "second" => $second_variable_id
//        )));
        return count($result) > 0;
    }
}