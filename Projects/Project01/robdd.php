<?php
require_once("./obdd.php");
class ROBDD extends OBDD {
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
            if ($print_src)
            {
                $this->print_table();
            }
            // render robdd
            $no_effects = $this->get_no_effects();
            $duplicates = $this->get_duplicates();
            while(count($no_effects) > 0 || count($duplicates) > 0)
            {
                if (count($no_effects) > 0)
                {
                    // drop no effect
                    $target_drop = array_reverse($no_effects)[0];
                    $this->drop($target_drop, $target_drop["then"]);
                }
                else if(count($duplicates) > 0)
                {
                    // drop duplicate
                    $duplicates = array_values(array_values(array_reverse($duplicates))[0])[0];
                    $target_drop = array_pop($duplicates);
                    $set_then = array_pop($duplicates)["id"];
                    $this->drop($target_drop, $set_then);
                }
                $no_effects = $this->get_no_effects();
                $duplicates = $this->get_duplicates();
            }
        }
    }

    /**
     * get duplicates list
     * @return array
     */
    protected function get_duplicates()
    {
        $result = array();
        $layer_group = array();
        foreach($this->get_available() as $item)
        {
            // group by layer name
            if (!isset($layer_group[$item["layer_var_name"]]))
            {
                $layer_group[$item["layer_var_name"]] = array();
            }
            array_push($layer_group[$item["layer_var_name"]], $item);
        }
        foreach($layer_group as $layer_name => $content)
        {
            // group by layer name
            if (!isset($result[$layer_name]))
            {
                $result[$layer_name] = array();
            }
            foreach($content as $item)
            {
                $this_key = $item["then"] . ", " . $item["catch"];
                if (!isset($result[$layer_name][$this_key]))
                {
                    $result[$layer_name][$this_key] = array();
                }
                array_push($result[$layer_name][$this_key], $item);
            }
            $result[$layer_name] = array_filter($result[$layer_name], function($item)
            {
               return count($item) > 1;
            });
        }
        return array_filter($result, function($item)
        {
            return count($item) >= 1;
        });
    }

    /**
     * get no effects list
     * @return array
     */
    protected function get_no_effects()
    {
        $result = array();
        foreach ($this->get_available() as $item)
        {
            if ($item["then"] == $item["catch"])
            {
                array_push($result, $item);
            }
        }
        return $result;
    }

    /**
     * drop item from data list
     * @param array $target
     * @param int $replace
     */
    protected function drop($target, $replace)
    {
        $depends = $this->get_depends($target["id"]);
        foreach ($depends as $depend)
        {
            $this->set_route($depend, $target["id"], $replace);
        }
        $this->set_disabled($target["id"]);
    }

    /**
     * set someone disabled status
     * @param $id
     * @param bool $disable (optional, default to disable)
     * @return void
     */
    protected function set_disabled($id, $disable = true)
    {
        $index = $this->get_node_index($id);
        if ($index > -1)
        {
            $this->data[$index]["disabled"] = $disable;
        }
    }

    /**
     * replace route(update route)
     * @param array $obj
     * @param int $src
     * @param int $target
     * @return void
     */
    protected function set_route($obj, $src, $target)
    {
        $index = $this->get_node_index($obj["id"]);
        if ($index < 0)
        {
            return;
        }
        $obj["then"] = $obj["then"] == $src ? $target : $obj["then"];
        $obj["catch"] = $obj["catch"] == $src ? $target : $obj["catch"];
        $this->data[$index] = $obj;

        if ($obj["then"] == $obj["catch"])
        {
            $target = $obj["then"];
            foreach ($this->get_depends($obj["id"]) as $depend)
            {
                $this->set_route($depend, $obj["id"], $target);
            }
            $this->set_disabled($obj["id"]);
        }
    }

    /**
     * search which item used this nodeIndex. before drop used.
     * @param int $id
     * @return array
     */
    protected function get_depends($id)
    {
        $result = array();
        foreach ($this->get_available() as $item)
        {
            if ($item["then"] == $id || $item["catch"] == $id)
            {
                array_push($result, $item);
            }
        }
        return $result;
    }
}
