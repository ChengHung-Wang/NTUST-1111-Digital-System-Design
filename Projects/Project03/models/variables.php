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
     * @param int|null
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
     * find variable by name
     * @param string $name
     * @return array
     */
    public function find_by_name(string $name): array
    {
        return $this->find_by("name", $name);
    }
//
//    /**
//     * @param string $col_name
//     * @param string|int $content
//     * @param bool $single
//     * @return array
//     */
//    protected function find_by(string $col_name, $content, bool $single = true): array
//    {
//        try {
//            $result = $this->db->createQueryBuilder()->where(array($col_name, "=", $content))->getQuery();
//            return $single ? $result->first() : $result->fetch();
//        } catch (IOException | InvalidArgumentException $e) {
//            return array(DB::$configuration["primary_key"] => -1);
//        }
//    }
}