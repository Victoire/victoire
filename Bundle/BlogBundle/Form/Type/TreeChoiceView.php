<?php

namespace Victoire\Bundle\BlogBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class TreeChoiceView extends ChoiceView
{
    public $level;

    public function __construct($data, $value, $label, $level)
    {
        parent::__construct($data, $value, $label);
        $this->level = $level;
    }
}