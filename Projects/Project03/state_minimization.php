<?php
require_once "./configurations.php";
require_once "./models/variables.php";
require_once "./models/rules.php";
require_once "./models/implications.php";

use Config\Debug;
use DB\Rules;
use DB\Variables;
use DB\Implications;

class StateMinimization
{
    /**
     * DataTables
     */
    protected $variables;
    protected $rules;
    protected $implications;

    /**
     * Debug print
     */
    protected $debug;

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
        $this->debug = new Debug();
    }

    public function render()
    {
        if (! $this->init_done)
            return;
        $this->fill_lower_triangle();
        $this->judge_implication_block();
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
    protected function fill_lower_triangle() {
        foreach ($this->variables->get(null, -1) as $index => $row) {
            foreach ($this->variables->get(1 + $index) as $col) {
                $this->implications->insert($row["id"], $col["id"]);
            }
        }
        $this->debug->success("Success: StateMinimization::fill_lower_triangle()");
    }

    protected function judge_implication_block() {
        do {
            $block_change = false;
            foreach($this->implications->get() as $implication)
            {
                if ($implication["disabled"] === false)
                {
                    foreach ($implication["diff_of_output"] as $item)
                    {
                        if (count(array_unique($item)) !== 1) {
                            $this->implications->set_disabled($implication["id"]);
                            $block_change = true;
                            $this->debug->info($this->variables->find($implication["first_judge_var_id"])["name"] . "->" . $this->variables->find($implication["second_judge_var_id"])["name"] . " has disabled.");
                        }
                    }
                }
            }
        } while($block_change === true && $this->debug->warning("Warning: StateMinimization::judge_implication_block() loop again"));
        $this->debug->success("Success: StateMinimization::judge_implication_block()");
    }

//    /**
//     * @param string $name
//     * @param null|string $signal
//     * @return array|mixed
//     */
//    protected function get_rule_by_var_name(string $name, string $signal = null)
//    {
//        $filter = array_filter($this->rules, function($item) use($name)
//        {
//            return $item["variable"] === $name;
//        });
//        $result = array();
//        foreach ($filter as $content)
//        {
//            $result[$content['input']] = $content;
//        }
//        return $signal === null ? $result : $result[$signal];
//    }
//
//    /**
//     * get not compatibles
//     * @return array
//     */
//    protected function get_not_compatibles(): array
//    {
//        $not_compatibles = array();
//        foreach($this->variables as $variable)
//        {
//            $output = array();
//            foreach($this->get_rule_by_var_name($variable["name"]) as $content) {
//                array_push($output, $content["output"]);
//            }
//            if (count(array_unique($output)) !== 1) {
//                array_push($not_compatibles, $variable["name"]);
//            }
//        }
//        return array_unique($not_compatibles);
//    }
}