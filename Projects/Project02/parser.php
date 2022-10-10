<?php
class Parser
{
    /**
     * @var ROBDD
     */
    public $robdd_service;

    /**
     * @var array input
     */
    protected $input;

    public function __construct(&$obdd_service, $file_path = "")
    {
        $this->robdd_service = $obdd_service;
        if ($file_path !== "")
        {
            $this->input = array_filter(explode(PHP_EOL, str_replace("\r", '',file_get_contents($file_path))), function($item)
            {
                return $item !== "";
            });
            $this->analyze();
        }
    }

    public function start()
    {
        $input = rtrim(fgets(STDIN));
        array_push($this->input, $input);
        while($input != ".e")
        {
            $input = rtrim(fgets(STDIN));
            array_push($this->input, $input);
        }
        $this->analyze();
    }

    protected function analyze()
    {
        $rule_limit = 0;
        foreach($this->input as $item)
        {
            $split = explode(" ", $item);
            if (count($split) >= 2 && $item !== ".e")
            {
                $method = $split[0];
                unset($split[0]);
                $content = join(" ", array_values($split));
                if (! is_numeric(str_replace("-", "", $method))) {
                    switch ($method)
                    {
                        case ".i":
                            $this->robdd_service->set_input(intval($content));
                            break;
                        case ".o":
                            $this->robdd_service->set_output(intval($content));
                            break;
                        case ".ilb":
                            foreach(explode(" ", $content) as $char)
                            {
                                $this->robdd_service->add_var($char);
                            }
                            break;
                        case ".ob":
                            $this->robdd_service->set_equation_name($content);
                            break;
                        case ".p":
                            if ($rule_limit <= 0)
                            {
                                $rule_limit = (intval($content));
                            }
                            break;
                    }
                }
                else if ($rule_limit > 0)
                {
                    $this->robdd_service->add_rule($method, $content);
                    $rule_limit -= 1;
                }
            }
            if ($item === ".e")
            {
                $this->robdd_service->set_end();
            }
        }
    }
}