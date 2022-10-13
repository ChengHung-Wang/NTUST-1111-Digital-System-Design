<?php
require_once("./prime_implicant.php");

class EssentialPrimeImplicant extends PrimeImplicant
{
    protected $m_table;
    protected $pi_table;

    public function render($print_src = false)
    {
        if (! $this->read_end) return;
        if ($print_src) $this->print_table();
        $this->init();
        $this->analyze();
        $this->mini();
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
        var_dump($validates);
        foreach($validates as $p_key => $m_key)
        {
//            var_dump($p_key);
//            var_dump($m_key);
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