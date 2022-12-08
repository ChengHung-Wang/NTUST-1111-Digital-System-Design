<?php
require_once __DIR__ . "/Configurations.php";
require_once __DIR__ . "/Models/Variables.php";
require_once __DIR__ . "/Models/Rules.php";
require_once __DIR__ . "/Models/Implications.php";

use Config\Debug;
use DB\Rules;
use DB\Variables;
use DB\Implications;
use Encoder\Encoder;
use function DB\drop_all;

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
     * @var bool $init_done
     */
    public $init_done = false;

    /**
     * class init
     * @return void
     */
    public function __construct()
    {
        drop_all();
        $this->variables = new Variables();
        $this->rules = new Rules();
        $this->implications = new Implications();
        $this->debug = new Debug();
    }

    /**
     * run state minimization
     */
    public function render()
    {
        if (! $this->init_done)
            return;
        $this->fill_lower_triangle();
        $this->judge_implication_block();
        $this->replace();
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

    /**
     * set read end
     */
    public function ready()
    {
        $this->init_done = true;
    }

    // methods
    /**
     * init gauss table
     */
    protected function fill_lower_triangle()
    {
        foreach ($this->variables->get(null, -1) as $index => $row)
        {
            foreach ($this->variables->get(1 + $index) as $col)
            {
                $this->implications->insert($row["id"], $col["id"]);
            }
        }
        $this->debug->success("Success: StateMinimization::fill_lower_triangle()");
    }

    /**
     * judge implication black
     * @return void
     */
    protected function judge_implication_block() : void
    {
        do {
            $block_change = false;
            $data = $this->implications->get();
            foreach($data as $implication)
            {
                if ($implication["disabled"] === false)
                {
                    foreach ($implication["diff_of_output"] as $item)
                    {
                        if (count(array_unique($item)) !== 1)
                        {
                            $this->implications->set_disabled($implication["id"]);
                            $block_change = true;
                            $this->debug->info($this->variables->find($implication["first_judge_var_id"])["name"] . "->" . $this->variables->find($implication["second_judge_var_id"])["name"] . " has disabled.");
                        }
                    }
                }
            }
            if ($block_change === false)
            {
                foreach($data as $implication)
                {
                    $has_disabled = false;
                    if ($implication["disabled"] === true)
                        continue;
                    $check_list = array();
                    foreach ($implication["first"] as $first)
                    {
                        if (!isset($check_list[$first["input"]]))
                            $check_list[$first["input"]] = array();
                        array_push($check_list[$first["input"]], $first["next_variable_id"]);
                    }
                    foreach ($implication["second"] as $second)
                    {
                        if (!isset($check_list[$second["input"]]))
                            $check_list[$second["input"]] = array();
                        array_push($check_list[$second["input"]], $second["next_variable_id"]);
                    }

                    foreach ($check_list as $content)
                    {
                        if ($has_disabled)
                            continue;
                        $level_list = array();
                        $target_disabled = count($content) >= 2 && $this->implications->is_disabled($content[0], $content[1]);
                        foreach ($content as $item)
                        {
                            foreach ($this->rules->find_by_variable_id($item) as $target)
                            {
                                if (!isset($level_list[$target["input"]])) {
                                    $level_list[$target["input"]] = array();
                                }
                                array_push($level_list[$target["input"]], $target["output"]);
                            }
                        }
                        foreach ($level_list as $main)
                        {
                            if (count(array_unique($main)) !== 1 || $target_disabled)
                            {
                                $this->implications->set_disabled($implication["id"]);
                                $block_change = true;
                                $has_disabled = true;
                                $this->debug->info($this->variables->find($implication["first_judge_var_id"])["name"] . "->" . $this->variables->find($implication["second_judge_var_id"])["name"] . " has disabled(" . ($target_disabled ? "target_disabled" : "") . ").");
                                break;
                            }
                        }
                    }
                }
            }
        } while($block_change === true && $this->debug->warning("Warning: StateMinimization::judge_implication_block() loop again."));
        $this->debug->success("Success: StateMinimization::judge_implication_block()");
    }

    /**
     * replace all Compatibility
     * @return void
     */
    protected function replace() : void
    {
        $data = $this->implications->get_not_disabled();
        if (count($data) >= 1) {
            $data = $data[0];
            $this->implications->override_variable_id($data["second_judge_var_id"], $data["first_judge_var_id"], true);
            $this->rules->override_variable_id($data["second_judge_var_id"], $data["first_judge_var_id"]);
            $this->rules->del_by_variable_id($data["second_judge_var_id"]);
            $this->debug->info("replace all of " . $this->variables->find($data["second_judge_var_id"])["name"] . " to " . $this->variables->find($data["first_judge_var_id"])["name"] . ".");
            $this->debug->warning("Warning: StateMinimization::replace() call by self.");
            $this->variables->delete($data["second_judge_var_id"]);
            $this->replace();
        }
    }

    /**
     * @param Encoder $service
     * @return string
     */
    public function output(Encoder $service) : string
    {
        $service->variables = $this->variables->get();
        $service->init_variable = $this->variables->get_init_name();
        $service->input_amount = $this->input_count;
        $service->output_amount = $this->output_count;
        $service->rules = $this->rules->get();
        return $service->render();
    }
}