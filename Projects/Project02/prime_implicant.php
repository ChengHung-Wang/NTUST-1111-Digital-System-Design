<?php
require_once("./obdd.php");
class PrimeImplicant extends OBDD
{
    protected $pi_groups;

    public function render($print_src = false)
    {
        parent::render($print_src);
        if (! $this->read_end) return;
        if ($print_src) $this->print_table();
        $this->init();
        $this->simple();
    }

    /**
     * init Prime Implicant needs
     * @return void
     */
    protected function init()
    {
        $this->pi_groups = array();
        for ($index = 0; $index <= count($this->variables); $index ++)
            $this->pi_groups[$index] = array();
        $this->group();
    }

    protected function group()
    {
        foreach ($this->rules as $rule)
        {
            foreach($this->eval_all(join("", $rule["spell"])) as $item)
            {
                $index = substr_count($item, "1");
                array_push($this->pi_groups[$index], $item);
            }
        }
        foreach ($this->pi_groups as &$pi_group)
        {
            $pi_group = array_unique($pi_group);
        }
    }

    /**
     * simple(depends on $this->combine(int))
     * @return void
     */
    protected function simple()
    {
        for ($i = 0; $i < (count($this->variables) - 2); $i ++)
        {
            $new_pi_groups = array();
            for ($index = 0; ($index < count($this->pi_groups) - 1); $index ++)
            {
                if (isset($this->pi_groups[$index]) && isset($this->pi_groups[$index + 1]))
                {
                    $new_pi_groups[$index] = $this->combine($index);
                }
            }
            $this->pi_groups = $new_pi_groups;
        }
    }

    /**
     * prime implicant combine(ex: 0000, 0001 => 000-)
     * @return array
     */
    protected function combine($index)
    {
        $result = array();
        if (!isset($this->pi_groups[$index]) || !isset($this->pi_groups[$index + 1])) return array();
        foreach($this->pi_groups[$index] as $now)
        {
            foreach($this->pi_groups[$index + 1] as $next)
            {
                $diff = -1;
                $num = 0;
                for ($t_index = 0; $t_index < count($this->variables); $t_index ++)
                {
                    if ($now[$t_index] !== $next[$t_index])
                    {
                        $diff = $t_index;
                        $num ++;
                    }
                }
                if ($num === 1)
                {
                    $find = $now;
                    $find[$diff] = "-";
                    array_push($result, $find);
                }
            }
        }
        return array_unique($result);
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

class EssentialPrimeImplicant extends PrimeImplicant
{
    protected $m_table;
    protected $pi_table;

    public function render($print_src = false)
    {
        parent::render($print_src);
        if (! $this->read_end) return;
        if ($print_src) $this->print_table();
        $this->init();
        $this->simple();
        $this->mini();
    }

    /**
     * init Prime Implicant needs
     * @return void
     */
    protected function init()
    {
        $this->pi_groups = array();
        $this->m_table = array();
        $this->pi_table = array();
        for ($index = 0; $index <= count($this->variables); $index ++)
            $this->pi_groups[$index] = array();
        $this->group();
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
                        array_push($validates, $item);
                    }
                }
            }
        }
        $validates = array_unique($validates);
        foreach($validates as $validate)
        {
            var_dump($this->pi_table[$validate]);
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
}