<?php
namespace Encoder;
use Config\StateMinimization;
require_once __DIR__ . "/Encoder.php";
class KissEncoder extends Encoder
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render(): string
    {
        $this->push(".start_kiss");
        $this->push(".i " . $this->input_amount);
        $this->push(".o " . $this->output_amount);
        $this->push(".p " . count($this->rules));
        $this->push(".s " . count($this->variables));
        $this->push(".r " . $this->init_variable);
        $this->push($this->get_rule_string());
        $this->push(".end_kiss");
        return $this->result;
    }

    private function get_rule_string() : string
    {
        return join(PHP_EOL, array_map(function($el) {
            $this_line = array();
            array_push($this_line, str_replace(StateMinimization::$signal_prefix, "", $el["input"]));
            array_push($this_line, $el["variable"]["name"]);
            array_push($this_line, $el["next_variable"]["name"]);
            array_push($this_line, str_replace(StateMinimization::$signal_prefix, "", $el["output"]));
            return join(" ", $this_line);
        }, $this->rules));
    }
}