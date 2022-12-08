<?php
namespace Encoder;
class Encoder
{
    /**
     * @var array $variables
     */
    public $variables;

    /**
     * @var string $init_variable
     */
    public $init_variable;

    /**
     * @var array $rules
     */
    public $rules;

    /**
     * @var int $input_amount
     */
    public $input_amount = -1;

    /**
     * @var int $output_amount
     */
    public $output_amount = -1;

    /**
     * @var string $result
     */
    protected $result = "";

    public function __construct()
    {}

    public function render() : string
    {
        return "";
    }

    /**
     * @param string $content
     * @param string $madeline
     */
    protected function push(string $content, string $madeline = PHP_EOL) : void
    {
        $this->result .= $content;
        if ($madeline)
        {
            $this->result .= $madeline;
        }
    }

}