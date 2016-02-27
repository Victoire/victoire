<?php

namespace Victoire\Bundle\WidgetBundle\Form;


class WidgetOptionsContainer
{
    /**
     * @var array
     */
    private $options;

    /**
     * WidgetOptionsContainer constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param array $options
     */
    public function add($key, $value)
    {
        $this->options[$key] = $value;
    }
}