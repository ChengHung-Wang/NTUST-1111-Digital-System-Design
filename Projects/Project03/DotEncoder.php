<?php
/**
digraph STG {
    rankdir=LR;

    INIT [shape=point];
    a [label="a"];
    b [label="b"];
    d [label="d"];
    f [label="f"];

    INIT -> a;
    a -> a [label="0/0"];
    a -> b [label="1/0"];
    b -> a [label="0/0"];
    b -> d [label="1/1"];
    d -> d [label="0/1"];
    d -> f [label="1/1"];
    f -> a [label="0/1,1/0"];
}
 */
namespace Encoder;
use Config\StateMinimization;

require_once __DIR__ . "/Encoder.php";
class DotEncoder extends Encoder
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render(): string
    {
        $this->push("digraph STG {");
        $this->push("    rankdir=LR;");
        $this->push(""); // madeline
        $this->push("    INIT [shape=point];");
        $this->push($this->get_variable_string());
        $this->push(""); // madeline
        $this->push("    INIT -> " . $this->init_variable);
        $this->push($this->get_rule_string());
        $this->push("}");
        return $this->result;
    }

    private function get_variable_string() : string
    {
        return join(PHP_EOL, array_map(function($el) {
            return "    " . $el["name"] . " [label=\"" . $el["name"] . "\"];";
        },$this->variables));
    }

    private function get_rule_string() : string
    {
        $result = array();
        foreach ($this->rules as $rule)
        {
            $key = $rule["variable"]["name"] . " -> " . $rule["next_variable"]["name"];
            if (!isset($result[$key])) {
                $result[$key] = array();
            }
            array_push($result[$key], array(
                "key" => $key,
                "input" => str_replace(StateMinimization::$signal_prefix, "", $rule["input"]),
                "output" => str_replace(StateMinimization::$signal_prefix, "", $rule["output"])
            ));
        }
        return join(PHP_EOL, array_map(function($el) {
            $key = $el[0]["key"];
            return "    " . $key . " [label=\"" .  join(",", array_map(function($e) {
                return $e["input"] . "/" . $e["output"];
            }, $el)) . "\"];";
        }, array_values($result)));
    }
}