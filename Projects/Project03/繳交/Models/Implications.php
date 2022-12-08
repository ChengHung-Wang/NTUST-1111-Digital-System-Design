<?php
namespace DB;
use SleekDB\Exceptions\IdNotAllowedException;
use SleekDB\Exceptions\InvalidArgumentException as InvalidArgumentExceptionAlias;
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
        } catch (IOException | InvalidArgumentExceptionAlias | JsonException | IdNotAllowedException $e)
        {
            $this->debug->error("ERROR: \DB\Implications::insert(int, int);\n");
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
        } catch (IOException | InvalidArgumentExceptionAlias | JsonException $e) {
            $this->debug->error("ERROR: \DB\Implications::set_disabled(int, bool);\n");
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

        } catch (IOException | InvalidArgumentExceptionAlias $e) {
            $this->debug->error("ERROR: \DB\Implications::get();\n");
        }
    }

    /**
     * @param int $first_variable_id
     * @param int $second_variable_id
     * @return bool
     */
    public function is_disabled(int $first_variable_id, int $second_variable_id): bool
    {
        if ($first_variable_id === $second_variable_id) {
            return false;
        }
        try {
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
            return count($result) > 0;
        } catch (IOException | InvalidArgumentExceptionAlias $e)
        {
            $this->debug->error("ERROR: \DB\Implications::is_disabled(int, int);\n");
            return false;
        }
    }

    /**
     * get all hasn't disabled for implication blocks
     * @return array
     */
    public function get_not_disabled() : array
    {
        try {
            return $this->db->createQueryBuilder()
                ->where(array("disabled", "=", false))
                ->orderBy(array($this->db->getPrimaryKey() => "asc"))
                ->getQuery()->fetch();
        } catch (IOException | InvalidArgumentExceptionAlias $e) {
            $this->debug->error("ERROR: \DB\Implications::get_not_disabled();\n");
            return array();
        }
    }

    /**
     * override variable id(before delete_rules_by_variable_id use)
     * @param int $from_id
     * @param int $override_id
     * @param bool $set_disabled
     * @return void
     */
    public function override_variable_id(int $from_id, int $override_id, bool $set_disabled = false) : void
    {
        try {
            $targets =
                $this->db->createQueryBuilder()
                    ->where(array("first_judge_var_id", "=", $from_id))
                    ->orWhere(array("second_judge_var_id", "=", $from_id))
                    ->getQuery()->fetch();
            $this->db->update(array_map(function($el) use ($from_id, $override_id, $set_disabled) {
                if ($el["first_judge_var_id"] === $from_id)
                    $el["first_judge_var_id"] = $override_id;
                if ($el["second_judge_var_id"] === $from_id)
                    $el["second_judge_var_id"] = $override_id;
                if ($set_disabled)
                    $el["disabled"] = true;
                return $el;
            }, $targets));
        } catch (IOException | InvalidArgumentExceptionAlias $e) {
            $this->debug->error("ERROR: \DB\Implications::replace_variable_id(int, int, bool);");
        }
    }
}