<?php
namespace Victoire\Bundle\CoreBundle\Theme;

/**
 * The theme chain add and give the configurated themes
 * ref: victoire_core.theme_chain
 */
class ThemeChain
{
    protected $themes;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->themes = array();
    }

    /**
     * Add a theme for a widget
     * @param string $widgetTheme Name of the widget that is a theme
     * @param string $widgetName  Name of the widget that have the theme
     */
    public function addTheme($widgetTheme, $widgetName)
    {
        //add the widget manager reference as a theme for this widget
        $this->themes[$widgetName][] = $widgetTheme;
    }

    /**
     * Get all themes tagged with victoire_core.theme
     * @param string $widgetName
     *
     * @return array
     */
    public function getThemes($widgetName = null)
    {
        $themes = array();

        //get all the themes
        if ($widgetName === null) {
            $themes = $this->themes;
        } else {
            //get a specific theme
            if (isset($this->themes[$widgetName])) {
                $themes = $this->themes[$widgetName];
            }
        }

        return $themes;
    }
}
