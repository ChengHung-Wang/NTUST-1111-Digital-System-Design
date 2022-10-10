<?php
require_once("./cli_table.php");
class OBDD {
    /**
     * @var array all ROBDD data
     */
    protected $data;

    /**
     * @var array
     */
    protected $variables;

    /**
     * @var int binary tree size
     */
    protected $size;

    /**
     * @var int output size
     */
    protected $output_size;

    /**
     * @var bool check ".e" read
     */
    protected $read_end;

    /**
     * @var array
     */
    public $rules;

    /**
     * @var string for equation output
     */
    public $equation;

    /**
     * @var string
     */
    public $output_equation_name;

    /**
     * @var string
     */
    public $out_dot_file_path;

    /**
     * @var array
     */
    public $dont_care;

    /**
     * init
     * @return void
     */
    public function __construct()
    {
        $this->data = array();
        $this->variables = array();
        $this->rules = array();
        $this->dont_care = array();
        $this->size = 0;
        $this->output_size = 0;
        $this->out_dot_file_path = "";
        $this->output_equation_name = "";
        $this->read_end = false;
        $this->equation = "";
    }

    /**
     * define input size
     * @param int $num
     * @return void
     */
    public function set_input($num)
    {
        $this->size = pow(2, intval($num));
        if ($num <= 0)
        {
            return;
        }
        $this->add_data(0, false, null, null, true);
        $this->add_data($this->size, true, null, null, true);
    }

    /**
     * define output size
     * @param int $num
     * @return void
     */
    public function set_output($num)
    {
        if (! is_numeric($num))
        {
            return;
        }
        $this->output_size = intval($num);
        // TODO
    }

    /**
     * set end
     * @return void
     */
    public function set_end()
    {
        $this->read_end = true;
    }

    /**
     * add rule(must has set var_name)
     * @param string $spell
     * @param string $target
     * @return void
     */
    public function add_rule($spell, $target)
    {
        if (count(str_split($spell)) != count($this->variables))
        {
            return;
        }
        if ($this->output_equation_name == "")
        {
            return;
        }

        $spell = str_split($spell);
        $equ_part = "";
        for ($index = 0; $index < count($this->variables); $index ++)
        {

            if ($spell[$index] !== "-")
            {
                $equ_part .= $this->variables[$index] . (intval($spell[$index]) == 0 ? "'" : "");
            }
        }

        // don't care
        if ($target == "-")
        {
            array_push($this->dont_care, array(
                "spell" => $spell,
                "equation_part" => $equ_part
            ));
        }
        else
        {
            if ($this->equation != "")
            {
                $this->equation .= " + ";
            }
            $this->equation .= $equ_part;
        }

        array_push($this->rules, array(
            "spell" => $spell,
            "target" => $target
        ));
    }

    /**
     * set equation name
     * @param string $name
     */
    public function set_equation_name($name)
    {
        $this->output_equation_name = $name;
    }

    /**
     * add variable
     * @param string $name
     */
    public function add_var($name)
    {
        if ($name != "" && array_search($name, $this->variables) === false)
        {
            array_push($this->variables, $name);
        }
    }

    /**
     * start render table
     * @param bool $print_src
     * @return void
     */
    public function render($print_src = false)
    {
        if ($this->read_end)
        {
            // render obdd
            $this->dhcp();
            if ($print_src) {
                $this->print_table();
            }
        }
    }

    /**
     * get equation string
     * @return string
     */
    public function get_equation_str()
    {
        return $this->output_equation_name . " = " . $this->equation;
    }

    /**
     * get don't care equation string
     * @return string
     */
    public function get_dont_care_str()
    {
        $result = "";
        foreach ($this->dont_care as $item)
        {
            if ($result != "")
            {
                $result .= " + ";
            }
            $result .= $item["equation_part"];
        }
        return $result;
    }

    /**
     * auto assign binary tree
     * @return void
     */
    public function dhcp()
    {
        if (!$this->read_end)
        {
            return;
        }
        for ($index = 1; $index < $this->size; $index ++)
        {
            $this_layer = $this->get_range_summary($index);
            if ($this_layer["next_node"]["true"] == null)
            {
                $node_result = $this->get_node_result($index);
                $this->add_data(
                    $this_layer["id"],
                    $this_layer["layer_var_name"],
                    $node_result["true"],
                    $node_result["false"]
                );
            }else {
                $this->add_data(
                    $this_layer["id"],
                    $this_layer["layer_var_name"],
                    $this_layer["next_node"]["true"],
                    $this_layer["next_node"]["false"]
                );
            }
        }
    }

    /**
     * Print table
     * @return void;
     */
    public function print_table()
    {
        if (! $this->read_end)
        {
            return;
        }
        if (count($this->data) < 2)
        {
            return;
        }
        $table_data = array();
        array_push($table_data, $this->data[0]);
        foreach ($this->data as $item)
        {
            if (! $item["func"])
            {
                array_push($table_data, $item);
            }
        }
        array_push($table_data, $this->data[1]);

        foreach ($table_data as $key => $item)
        {
            $table_data[$key]["comment"] = "";
            $table_data[$key]["var"] = $item["layer_var_name"];
            if ($item["func"])
            {
                $table_data[$key]["comment"] = $item["layer_var_name"] === true ? "Boolean 1" : "Boolean 0";
                $table_data[$key]["var"] = "-";
                $table_data[$key]["then"] = "-";
                $table_data[$key]["catch"] = "-";
            }
            if ($item["disabled"])
            {
                $table_data[$key]["comment"] = "redundant";
            }
        }
        $table = new CliTable;
        $table->setTableColor('reset');
        $table->setHeaderColor('blue');
        $table->addField('Index', 'id', false, 'gray');
        $table->addField('Variable',  'var', false, 'yellow');
        $table->addField('Else-edge',        'catch', false, 'white');
        $table->addField('Then-edge',      'then', false, 'white');
        $table->addField('Comment',  'comment', false, 'red');
        $table->injectData($table_data);
        $table->display();
    }

    /**
     * encode dot file
     * @return string;
     */
    public function encode_dot()
    {
        $result = "";
        $tab = "    ";
        $result .= "digraph " . get_class($this) . " {" . PHP_EOL;

        $ranks = array();
        foreach ($this->get_available() as $item) {
            if (! isset($ranks[$item["layer_var_name"]])) {
                $ranks[$item["layer_var_name"]] = array();
            }
            array_push($ranks[$item["layer_var_name"]], $item);
        }
        foreach ($ranks as $rank) {
            $result .= $tab . "{rank=same " . join(" ", array_map(function($item) {
                    return $item["id"];
                }, $rank)) . "}" . PHP_EOL;
        }
        $result .= PHP_EOL;

        $data = array();
        array_push($data, $this->data[0]);
        foreach ($this->data as $item)
        {
            if (! $item["func"])
            {
                array_push($data, $item);
            }
        }
        array_push($data, $this->data[1]);

        foreach ($data as $item)
        {
            if (! $item["disabled"]) {
                $result .= $tab . $item["id"] . ' [label="' . ($item["func"] ? ($item["layer_var_name"] ? "1" : "0") : $item["layer_var_name"]) . '"';
                if ($item["func"]) {
                    $result .= ", shape=box";
                }
//                $result .= ']' . ($item["func"] ? ";" : "") . PHP_EOL;
                $result .= ']' . PHP_EOL;
            }
        }
        $result .= PHP_EOL;

        foreach ($this->get_available() as $item)
        {
            $then_node = $item["then"];
            $catch_node = $item["catch"];
            $result .= $tab . $item['id'] . " -> " . $catch_node . ' [label="0" style=dotted]' . PHP_EOL;
            $result .= $tab . $item['id'] . " -> " . $then_node . ' [label="1" style=solid]' . PHP_EOL;
        }

        $result .= "}" . PHP_EOL;
        return $result;
    }

    /**
     * get node result, for lasted layer
     * @param int $id nodeIndex
     * @param array $find_then
     * @param array $find_catch
     * @return array
     */
    protected function get_node_result($id, $find_then = array(), $find_catch = array())
    {
        $result = array(
            "true" => null,
            "false" => null
        );
        if (count($find_then) == 0 && count($find_catch) == 0)
        {
            array_push($find_then, 1);
            array_push($find_catch, 0);
        }
        if ($id == 1)
        {
            $find_then = join("", array_reverse($find_then));
            $find_catch = join("", array_reverse($find_catch));
            $result["true"] = $this->judge_rule($find_then) ? $this->size : 0;
            $result["false"] = $this->judge_rule($find_catch) ? $this->size : 0;
            return $result;
        }else
        {
            $previous = $this->get_range_summary($id);
            if ($previous["previous"] != null)
            {
                $previous = $previous["previous"];
                array_push($find_then, intval($id) % 2 === 0 ? 0 : 1);
                array_push($find_catch, intval($id) % 2 ===  0 ? 0 : 1);
                return $this->get_node_result($previous, $find_then, $find_catch);
            }
        }
    }

    /**
     * judge rule(get_node_result dependencies)
     * @param string $spell
     */
    protected function judge_rule($spell)
    {
        $spell = str_split($spell);
        foreach ($this->rules as $rule)
        {
            $has_find = true;
            foreach($rule["spell"] as $key => $value)
            {
                if (is_numeric($value))
                {
                    $value = intval($value);
                    if ($value != $spell[$key])
                    {
                        $has_find = false;
                    }
                }
            }
            if ($has_find)
            {
                return $rule["target"];
            }
        }
        return false;
    }

    /**
     * Get range summary
     * @param int $id nodeIndex
     * @return array
     */
    protected function get_range_summary($id)
    {
        if (! is_numeric($id))
        {
            return array();
        }
        $id = intval($id);
        $result = array(
            "id" => $id,
            "layer" => $id == 1 ? 1 : (intval(log($id, 2)) + 1)
        );
        $result["layer_id_range"] = array(
            "start" => $id == 1 ? 1 : pow(2, $result["layer"] - 1),
            "end" => $id == 1 ? 1 : pow(2, $result["layer"] - 1) + (pow(2, $result["layer"] - 1) - 1)
        );
        $result["next_node"] = array(
            "true" => null,
            "false" => null
        );
        $result["previous"] = null;
        if (log($this->size, 2) > $result["layer"])
        {
            $result["next_node"]["false"] = $result["layer_id_range"]["start"] * 2 + (2 * ($id - $result["layer_id_range"]["start"]));
            $result["next_node"]["true"] = $result["next_node"]["false"] + 1;
        }
        if ($id > 1)
        {
            $result["previous"] = $id % 2 === 0 ? $id / 2 : ($id - 1) / 2;
        }
        $result["layer_var_name"] = $this->variables[$result["layer"] - 1];
        return $result;
    }

    /**
     * Get available items
     * @return array
     */
    protected function get_available()
    {
        return array_values(array_filter($this->data, function($item)
        {
            return !$item["func"] && !$item["disabled"];
        }));
    }

    /**
     * add data item
     * @param int $index
     * @param bool|string $variable
     * @param int|null $then
     * @param null|int $catch
     * @param bool $func
     */
    protected function add_data($index, $variable, $then = null, $catch = null, $func = false)
    {
        $this_item = array();
        $this_item["id"] = $index;
        $this_item["layer_var_name"] = $variable;
        $this_item["then"] = $then;
        $this_item["catch"] = $catch;
        $this_item["func"] = $func;
        $this_item["disabled"] = false;
        array_push($this->data, $this_item);
    }

    /**
     * get node by id
     * @param int $index
     * @return int
     */
    protected function get_node_index($index)
    {
        foreach (array_values($this->data) as $key => $item)
        {
            if ($item["id"] == $index)
            {
                return $key;
            }
        }
        return -1;
    }
}
