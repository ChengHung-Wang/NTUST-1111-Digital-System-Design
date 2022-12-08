<?php
namespace DB;
use Config\DB;
use SleekDB\Exceptions\IdNotAllowedException;
use SleekDB\Exceptions\InvalidArgumentException;
use SleekDB\Exceptions\IOException;
use SleekDB\Exceptions\JsonException;

class Variables extends Table {
    /**
     * data table init
     */
    public function __construct()
    {
        parent::__construct();
        $this->db = $this->variables;
    }

    /**
     * get all variables
     * @param null $offset
     * @param null $limit
     * @return array
     */
    public function get($offset = null, $limit = null) : array
    {
        if ($limit < 0 && $limit !== null) {
            try {
                $limit = $this->db->count() + $limit;
            } catch (IOException $e) {
                return array();
            }
        }
        try {
            return $this->db->findAll([$this->db->getPrimaryKey() => "asc"], $limit, $offset);
        } catch (IOException | InvalidArgumentException $e)
        {
            $this->debug->error("ERROR: \DB\Variables::get();\n");
            return array();
        }
    }

    /**
     * @param string $name,
     * @param bool $is_init
     * @return int last insert id
     */
    public function insert(string $name, bool $is_init): int
    {
        try {
            return $this->db->insert(array(
                "name" => $name,
                "is_init" => $is_init
            ))[DB::$configuration["primary_key"]];
        } catch (IOException | IdNotAllowedException | InvalidArgumentException | JsonException $e) {
            $this->debug->error("ERROR: \DB\Variables::insert();\n");
            return -1;
        }
    }

    /**
     * find variable by id
     * @param int $id
     * @return array
     */
    public function find(int $id): array
    {
        return $this->find_by(DB::$configuration["primary_key"], $id);
    }

    /**
     * delete variable
     */
    public function delete(int $id) : void
    {
        try {
            $this->db->deleteById($id);
        } catch (InvalidArgumentException $e)
        {
            $this->debug->error("ERROR: \DB\Variables::delete();\n");
        }
    }

    /**
     * get init variable name
     */
    public function get_init_name() : string
    {
        try {
            $result = $this->db->findBy(array("is_init", "=", true));
            return $result[0]["name"];
        } catch (IOException | InvalidArgumentException $e) {
            $this->debug->error("ERROR: \DB\Variables::get_init_name();\n");
            return "undefined";
        }
    }

    /**
     * find variable by name
     * @param string $name
     * @return array
     */
    public function find_by_name(string $name): array
    {
        return $this->find_by("name", $name);
    }
}