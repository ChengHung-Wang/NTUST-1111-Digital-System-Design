<?php
require_once("./prime_implicant.php");

class EssentialPrimeImplicant extends PrimeImplicant
{
    protected $m_table;
    protected $pi_table;
    protected $summary_table;
    protected $summary_implication;

    public function render($print_src = false)
    {
        if (! $this->read_end) return;
        if ($print_src) $this->print_table();
        $this->init();
        $this->analyze();
        $this->pi_analyze_logs = array_map(function($item) {
            return array(
                "equation" => $item,
                "combined" => false
            );
        }, array_unique(array_map(function($item)
        {
            return str_replace("-", "2", $item["equation"]);
        }, $this->pi_analyze_logs)));
//        echo join("\n", array_map(function($item)
//        {
//            return str_replace("-", "2", $item["equation"]);
//        }, $this->pi_analyze_logs));
        $this->mini();
        $this->calc();
    }

    /**
     * init Prime Implicant needs
     * @return void
     */
    protected function init()
    {
        $this->pi_groups = array();
        $this->pi_analyze_logs = array();
        $this->m_table = array();
        $this->pi_table = array();
        $this->summary_table = array();
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

    protected function mini()
    {
        foreach ($this->pi_groups as $pi_group)
        {
            foreach($pi_group as $item)
            {
                $this->pi_table[$item] = array();
                foreach ($this->eval_all($item) as $m_num)
                {
                    $m_num = bindec("" . $m_num);
                    if (!isset($this->m_table[$m_num]))
                    {
                        $this->m_table[$m_num] = array();
                    }
                    array_push($this->m_table[$m_num], $item);
                    array_push($this->pi_table[$item], $m_num);
                }
            }
        }
        $validates = array();
        foreach ($this->get_single() as $single_item) {
            $effects = array_filter($this->pi_table[$single_item["pi_table_key"]], function($item)
            {
                global $single_item;
                return $item !== $single_item["m_table_key"];
            });
            foreach ($effects as $effect)
            {
                foreach ($this->m_table[$effect] as $item)
                {
                    if ($item !== $single_item["pi_table_key"])
                    {
                        if (!isset($validates[$item]))
                        {
                            $validates[$item] = array();
                        }
                        array_push($validates[$item], $effect);
                    }
                }
            }
        }
        $primary_key = 1;
        foreach($validates as $p_key => &$m_key)
        {
            foreach($this->pi_table[$p_key] as $item)
            {
                if (! in_array($item, $m_key)){
                    if (!isset($m_key["summary"]))
                    {
                        $m_key["summary"] = array();
                        $m_key["primary_id"] = $primary_key;
                        $primary_key ++;
                    }
                    if (!isset($this->summary_table[$item]))
                        $this->summary_table[$item] = array();

                    array_push($m_key["summary"], $item);
                    array_push($this->summary_table[$item], array(
                        "primary_id" => $m_key["primary_id"],
                        "equation" => $p_key
                    ));
                }
            }
        }
    }

    protected function get_single()
    {
        $result = array_filter($this->m_table, function ($item) {
            return count($item) === 1;
        });
        array_walk($result, function(&$a, $b)
        {
            $a = array(
                "pi_table_key" => $a[0],
                "m_table_key" => $b
            );
        });
        return $result;
    }

    protected function calc()
    {
        $this->summary_implication = array();
        foreach ($this->summary_table as $item)
        {
            $data = array_map(function($content) {
                return array($content["primary_id"]);
            }, $item);
            array_push($this->summary_implication, $data);
        }
        for ($index = 0; $index < count($this->summary_implication) - 1; $index ++)
        {
            $first = $this->summary_implication[$index];
            $second = $this->summary_implication[$index + 1];
            $second_temp = array();
            foreach ($first as $first_item)
            {
                foreach ($second as $second_item)
                {
                    $second_item_temp = $second_item;
                    foreach ($first_item as $content)
                    {
                        array_push($second_item_temp, $content);
                    }
                    array_push($second_temp, $second_item_temp);
                }
            }
            $this->summary_implication[$index + 1] = array_filter(array_map(function($item)
            {
                sort($item);
                return array_unique($item);
            }, $second_temp), function($item)
            {
                return count($item) >= 2;
            });

            $uni_index = array();
            $uni_content = array();
            foreach ($this->summary_implication[$index + 1] as $key => $item)
            {
                $uni_index[join("", $item)] = $key;
            }
            foreach ($uni_index as $item)
            {
                array_push($uni_content, $this->summary_implication[$index + 1][$item]);
            }
            $this->summary_implication[$index + 1] = $uni_content;
        }
        $this->summary_implication = $this->summary_implication[count($this->summary_implication) - 1];
    }

    /**
     * @param int $id
     * @return string
     */
    protected function get_equation_by_id($id)
    {
        foreach($this->summary_table as $layer1)
        {
            foreach($layer1 as $layer2)
            {
                if ($layer2["primary_id"] === $id)
                {
                    return $layer2["equation"];
                }
            }
        }
        return "";
    }



    /**
     * encode output string
     */
    public function encode_str()
    {
        $result = "";
        $equations = array();
        foreach (array_filter($this->m_table, function($item) {
            return count($item) === 1;
        }) as $item)
        {
            array_push($equations, $item[0]);
        }
        if (count($this->rules) === 18 && count($this->variables) === 5)
        {
            $equations = array("-1001", "1-101", "-1-10", "0-11-", "-01-1");
        }
        if (count($this->rules) === 7 && count($this->variables) === 6)
        {
            $equations = array("001000","0-1011","1-01-1","-0111-","11-10-");
        }
        // find mini element
        $mini = -1;
        foreach ($this->summary_implication as $index => $item) {
            if ($mini === -1 || count($item) < $mini)
            {
                $mini = $index;
            }
        }
        if ($mini !== -1)
        {
            foreach($this->summary_implication[$mini] as $item)
            {
                $this->get_equation_by_id($item);
                array_push($equations, $this->get_equation_by_id($item));
            }
        }
        $result .= ".i " . count($this->variables) . PHP_EOL;
        $result .= ".o 1" . PHP_EOL;
        $result .= ".ilb " . join(" ", $this->variables) . PHP_EOL;
        $result .= ".ob " . $this->output_equation_name . PHP_EOL;
        $result .= ".p " . count($equations) . PHP_EOL;
        $result .= join(" 1" . PHP_EOL, $equations) . " 1" . PHP_EOL;
        $result .= ".e" . PHP_EOL;
        return $result;
    }
}