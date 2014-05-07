<?php
namespace Victoire\Bundle\CoreBundle\Theme;

class ThemeChain
{

    private $themes;
    private $widgets;

    public function __construct($widgets)
    {
        $this->widgets = $widgets;
        $this->themes = array();
    }

    public function addTheme($theme, $widget)
    {
        $this->themes[$this->widgets[$widget]['class']][] = $theme;
    }

    public function getThemes($widget = null)
    {
        if ($widget) {
            if (!empty($this->themes[$widget])) {
                return $this->themes[$widget];
            } else {
                return array();
            }
        }

        return $this->themes;
    }
}
