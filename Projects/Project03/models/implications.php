<?php
namespace DB;
use SleekDB\Exceptions\IdNotAllowedException;
use SleekDB\Exceptions\InvalidArgumentException;
use SleekDB\Exceptions\IOException;
use SleekDB\Exceptions\JsonException;

class Implications extends Table {
    protected $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = $this->variables;
    }

    /**
     * @param int $first_judge_var_id
     * @param int $second_judge_var_id
     * @throws IOException
     * @throws JsonException
     * @throws InvalidArgumentException
     * @throws IdNotAllowedException
     * @return int last insert id
     */
    public function insert(int $first_judge_var_id, int $second_judge_var_id): int
    {
        return $this->db->insert(array(
            "first_judge_var_id" => $first_judge_var_id,
            "second_judge_var_id" => $second_judge_var_id,
            "disabled" => false
        ))["id"];
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
    public function get_all()
    {
        $rules_table = $this->rules;
        try {
            return $this->db
                ->createQueryBuilder()
                ->join(function ($el) use ($rules_table) {
                    return $rules_table->findBy([$rules_table->getPrimaryKey(), "=", "first_judge_var_id"]);
                }, "first")
                ->join(function ($el) use ($rules_table) {
                    return $rules_table->findBy([$rules_table->getPrimaryKey(), "=", "second_judge_var_id"]);
                }, "second")
                ->getQuery()->fetch();
        } catch (IOException | InvalidArgumentException $e) {
            die("ERROR: get all implications detail.\n");
        }
    }
}