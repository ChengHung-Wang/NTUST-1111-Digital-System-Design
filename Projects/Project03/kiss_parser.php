<?php
class KissParser {
    /***
     * @var StateMinimization $stateMinimization
     * State Minimization Object
     */
    public $stateMinimization;

    /**
     * @var bool $kiss_start
     */
    public $kiss_start = false;

    /**
     * @var int $variable_amount
     * amount of variable
     */
    public $variable_amount = -1;

    /**
     * @var int $rule_amount
     * amount of rule
     */
    public $rule_amount = -1;

    /**
     * @var array $rules
     */
    public $rules = array();

    /**
     * @var array $variables
     * variables (vector<string>)
     */
    public $variables = array();

    /***
     * @var array $input
     * input data(split by newline)
     */
    public $input = array();

    /**
     * @var string $init_var
     * var name for init
     */
    public $init_var = "";

    /**
     * kiss parser init
     * @param $stateMinimization
     * @param string $file_path
     */
    public function __construct(&$stateMinimization, string $file_path = "")
    {
        $this->stateMinimization = $stateMinimization;
        if ($file_path !== "")
        {
            $this->input = array_filter(explode(PHP_EOL, str_replace("\r", '',file_get_contents($file_path))), function($item)
            {
                return $item !== "";
            });
        }
    }

    /**
     * @return void
     * analyze kiss file content
     */
    public function analyze() {
        foreach ($this->input as $content)
        {
            $is_function = substr($content, 0, 1) === ".";
            if ($is_function) {
                $arg = explode(" ", $content);
                switch ($arg[0])
                {
                    case ".start_kiss":
                        $this->kiss_start = true;
                        break;
                    case ".i":
                        $this->stateMinimization->set_input_count((int)$arg[1]);
                        break;
                    case ".o":
                        $this->stateMinimization->set_output_count((int)$arg[1]);
                        break;
                    case ".p":
                        $this->rule_amount = (int)$arg[1];
                        break;
                    case ".s":
                        $this->variable_amount = (int)$arg[1];
                        break;
                    case ".r":
                        $this->init_var = $arg[1];
                        break;
                    case ".end_kiss":
                        foreach($this->rules as $rule) {
                            if (in_array($rule["var_name"], $this->variables) && in_array($rule["next_var"], $this->variables)) {
                                $this->stateMinimization->add_rule($rule['input'], $rule['var_name'], $rule['next_var'], $rule['output']);
                            }
                        }
                        $this->stateMinimization->ready();
                        break;
                }
            }
            else if ($this->kiss_start && count(explode(" ", $content)) === 4)
            {
                $content = explode(" ", $content);
                $var_name = trim($content[1]);
                $next_var = trim($content[2]);
                $input = "signal_" . trim($content[0]);
                $output = "signal_" . trim($content[3]);
                if (!in_array($var_name, $this->variables) && $this->variable_amount > 0) {
                    $this->variable_amount --;
                    $this->stateMinimization->add_variable($var_name, $var_name === $this->init_var);
                    array_push($this->variables, $var_name);
                }

//                $next_var_exists = in_array($next_var, $this->variables);
                if (count($this->rules) < $this->rule_amount) {
                    array_push($this->rules, array(
                        "input" => $input,
                        "var_name" => $var_name,
                        "next_var" => $next_var,
                        "output" => $output
                    ));
                }
            }
        }
    }
}