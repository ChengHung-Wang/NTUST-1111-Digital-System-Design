<?php
require_once "./models/variables.php";
require_once "./models/rules.php";
require_once "./models/implications.php";

use DB\Rules;
use DB\Variables;
use DB\Implications;
use SleekDB\Exceptions\IOException;

class StateMinimization
{
    /**
     * DataTables
     */
    protected $variables;
    protected $rules;
    protected $implications;

    /**
     * @var int $input_count
     * input count
     */
    public $input_count = 0;

    /**
     * @var int $output_count
     * output count
     */
    public $output_count = 0;

    /**
     * @var array $gauss_table
     * Implication Table
     */
    public $implication_table = array();

    /**
     * @var bool $init_done
     */
    public $init_done = false;

    /**
     * class init
     * @return void
     */
    public function __construct()
    {
        \DB\drop_all();
        $this->variables = new Variables();
        $this->rules = new Rules();
        $this->implications = new Implications();
    }

    public function render()
    {
        if (! $this->init_done)
            return;
//        $this->init_gauss_table();
    }

    // util
    /**
     * set amount of input
     * @param int $num
     * @return void
     */
    public function set_input_count(int $num)
    {
        $this->input_count = $num;
    }

    /**
     * set amount of output
     * @param int $num
     * @return void
     */
    public function set_output_count(int $num)
    {
        $this->output_count = $num;
    }

    /**
     * set variable
     * @param string $name
     * @param bool $is_init
     */
    public function add_variable(string $name, bool $is_init)
    {
        $this->variables->insert($name, $is_init);
    }

    /**
     * set read end
     */
    public function ready() {
        $this->init_done = true;
    }

    /**
     * add rule
     * @param string $input
     * @param string $variable_name
     * @param string $next_state
     * @param string $output
     * @return int rule id
     */
    public function add_rule(string $input, string $variable_name, string $next_state, string $output) : int
    {
        $id = $this->rules->insert($variable_name, $input, $output, $next_state);
        if ($id === -1)
            die("ERROR: add rule field!\n");
        return $id;
    }

    // methods
    /**
     * init gauss table
     */
    protected function init_gauss_table() {
        try {
            $all_var = $this->variables->db->findAll();
            for ($index = 0; $index < $this->variables->db->count() - 1; $index++) {
                $this->implication_table[$this->variables[$index]["name"]] = array();
                for ($col_index = $index + 1; $col_index < count($this->variables); $col_index++) {
                    $this_block = array();
                    $primary_output = array();
                    foreach ($this->get_rule_by_var_name($this->variables[$index]["name"]) as $value) {
                        $sub_obj["from"] = $value;
                        $sub_obj["next"] = $this->get_rule_by_var_name($this->variables[$col_index]["name"], $value["input"]);
                        $sub_obj["dont_care"] = $sub_obj["from"]["next_variable"] === $sub_obj["next"]["next_variable"];
                        $this_block[$value["input"]] = $sub_obj;
                        array_push($primary_output, $value["output"]);
                    }
                    $this->implication_table[$this->variables[$index]["name"]][$this->variables[$col_index]["name"]] = $this_block;
                }
            }
        } catch (IOException | \SleekDB\Exceptions\InvalidArgumentException $e)
        {}
        // mark disabled
        foreach($this->get_not_compatibles() as $not_compatible_var)
        {
            foreach ($this->implication_table as $row_index => &$row_content)
            {
                foreach ($row_content as $col_index => &$col_content)
                {
                    $disabled = (
                        $row_index === $not_compatible_var ||
                        $col_index === $not_compatible_var
                    );
                    foreach($col_content as &$col_item)
                    {
                        if (!$disabled) {
                            $disabled = ($col_item["from"]["next_variable"] === $not_compatible_var || $col_item["next"]["next_variable"] === $not_compatible_var);
                        }
                        $col_item["disabled"] = $disabled;
                    }
                }
            }
        }
//        var_dump($this->implication_table);
    }

    /**
     * @param string $name
     * @param null|string $signal
     * @return array|mixed
     */
    protected function get_rule_by_var_name(string $name, string $signal = null)
    {
        $filter = array_filter($this->rules, function($item) use($name)
        {
            return $item["variable"] === $name;
        });
        $result = array();
        foreach ($filter as $content)
        {
            $result[$content['input']] = $content;
        }
        return $signal === null ? $result : $result[$signal];
    }

    /**
     * get not compatibles
     * @return array
     */
    protected function get_not_compatibles(): array
    {
        $not_compatibles = array();
        foreach($this->variables as $variable)
        {
            $output = array();
            foreach($this->get_rule_by_var_name($variable["name"]) as $content) {
                array_push($output, $content["output"]);
            }
            if (count(array_unique($output)) !== 1) {
                array_push($not_compatibles, $variable["name"]);
            }
        }
        return array_unique($not_compatibles);
    }
}