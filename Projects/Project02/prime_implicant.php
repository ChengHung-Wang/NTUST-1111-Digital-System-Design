<?php
require_once("./obdd.php");
class PrimeImplicant extends OBDD
{
    protected $pi_groups;
    protected $pi_analyze_logs;
    public function render($print_src = false)
    {
        if (! $this->read_end) return;
        if ($print_src) $this->print_table();
        $this->init();
        $this->analyze();
    }

    /**
     * group & simplification
     * @return void
     */
    protected function analyze()
    {
        $change = 0;
        foreach($this->rules as $rule) {
            foreach($this->eval_all(join("", $rule["spell"])) as $item)
            {
                array_push($this->pi_analyze_logs, $item);
            }
        }
        sort($this->pi_analyze_logs);
        $this->pi_analyze_logs = array_map(function($item) {
            return array(
                "equation" => $item,
                "combined" => false
            );
        }, array_values(array_unique($this->pi_analyze_logs)));
        while (1)
        {
            $n = count($this->pi_analyze_logs);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $first = $this->pi_analyze_logs[$i]["equation"];
                    $second = $this->pi_analyze_logs[$j]["equation"];
                    $diff = $this->get_diff($first, $second);
                    if (
                        count($diff) === 1 &&
                        (substr_count($second, "1") - substr_count($first, "1") === 1) &&
                        strlen(str_replace("-", "", $first)) > 2 &&
                        strlen(str_replace("-", "", $second)) > 2
                    )
                    {
                        $this->pi_analyze_logs[$i]["combined"] = true;
                        $this->pi_analyze_logs[$j]["combined"] = true;
                        $change ++;
                        $new_equation = str_split($first);
                        $new_equation[$diff[0]] = "-";
                        $new_equation = join("", $new_equation);
                        array_push($this->pi_analyze_logs, array(
                            "equation" => $new_equation,
                            "combined" => false
                        ));
                    }
                }
            }
            $this->pi_analyze_logs = array_values(array_filter($this->pi_analyze_logs, function($item) {
                return !$item["combined"];
            }));
            $this->pi_analyze_logs = array_map(function($item) {
                return array(
                    "equation" => $item,
                    "combined" => false
                );
            }, array_values(array_unique(array_map(function($item) {
                return $item["equation"];
            }, $this->pi_analyze_logs))));

            if ($change === 0)
            {
                $this->group($this->pi_analyze_logs);
                break;
            }
            $change = 0;
        }
    }

    /**
     * get_diff
     * @param string $first
     * @param string $second
     * @return array
     */
    protected function get_diff($first, $second)
    {
        $result = array();

        if (strlen($first) === strlen($second))
        {
            $first = str_split($first);
            $second = str_split($second);
            foreach($first as $index => $item)
            {
                if (
                    ($item === "-" && $second[$index] !== "-") ||
                    ($second[$index] === "-" && $item !== "-")
                )
                {
                    return array();
                }
                if (
                    ($item === "0" && $second[$index] === "1") ||
                    ($item === "1" && $second[$index] === "0")
                )
                {
                    array_push($result, $index);
                }
            }
        }
        return $result;
    }

    /**
     * init Prime Implicant needs
     * @return void
     */
    protected function init()
    {
        $this->pi_groups = array();
        $this->pi_analyze_logs = array();
        for ($index = 0; $index <= count($this->variables); $index ++)
            $this->pi_groups[$index] = array();
        foreach ($this->pi_groups as $pi_group)
        {
            foreach ($pi_group as $item)
            {
                array_push($this->pi_analyze_logs, array(
                    "equation" => $item,
                    "combined" => false,
                ));
            }
        }
    }

    /**
     * group by "1" count
     * @param array $data
     */
    protected function group($data)
    {
        foreach ($data as $rule)
        {
            $index = substr_count($rule["equation"], "1");
            array_push($this->pi_groups[$index], $rule["equation"]);
        }
        foreach ($this->pi_groups as &$pi_group)
        {
            $pi_group = array_unique($pi_group);
        }
    }

    /**
     * list all bool equation
     * @param string $str boolean equation
     * @return array
     */
    protected function eval_all($str)
    {
        $result = array();
        $this->guess($str, $result);
        return $result;
    }

    /**
     * dfs permutations
     * @param $str
     * @param $guess_arr
     * @param string $temp
     * @param int $index
     */
    protected function guess($str, &$guess_arr, $temp = "", $index = 0) {
        $str_split = str_split($str);
        if ($index === strlen($str))
        {
            array_push($guess_arr, $temp);
            return;
        }
        if ($str_split[$index] === "-")
        {
            $this->guess($str, $guess_arr, $temp . "0", $index + 1);
            $this->guess($str, $guess_arr, $temp . "1", $index + 1);
        }
        else
        {
            $this->guess($str, $guess_arr, $temp . $str_split[$index], $index + 1);
        }
    }
}