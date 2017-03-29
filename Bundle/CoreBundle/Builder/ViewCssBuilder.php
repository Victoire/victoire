<?php

namespace Victoire\Bundle\CoreBundle\Builder;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Container;
use Victoire\Bundle\CoreBundle\Entity\View;

/**
 * View CSS Builder
 * ref: victoire_core.view_css_builder.
 */
class ViewCssBuilder
{
    protected $container;
    protected $victoireTwigResponsive;
    private $viewCssDir;

    /**
     * Construct.
     *
     * @param EngineInterface $templating
     * @param                 $victoireTwigResponsive
     * @param                 $kernelRootDir
     *
     * @internal param WidgetRenderer $widgetRenderer
     */
    public function __construct(Container $container, $victoireTwigResponsive, $kernelRootDir)
    {
        $this->webDir = '/view-css';
        $this->viewCssDir = $kernelRootDir.'/../web'.$this->webDir;
        $this->container = $container;
        $this->victoireTwigResponsive = $victoireTwigResponsive;
    }

    /**
     * Update css by removing old file and writing new file.
     *
     * @param $oldHash
     * @param View  $view
     * @param array $widgets
     */
    public function updateViewCss($oldHash, View $view, array $widgets)
    {
        $this->removeCssFile($oldHash);
        $this->generateViewCss($view, $widgets);
    }

    /**
     * Construct css file and write it.
     *
     * @param View  $view
     * @param array $widgets
     */
    public function generateViewCss(View $view, array $widgets)
    {
        $css = '';

        foreach ($widgets as $widget) {
            $style = $this->container->get('templating')->render(
            'VictoireCoreBundle:Widget:style/style.html.twig',
            [
                'widget'                   => $widget,
                'victoire_twig_responsive' => $this->victoireTwigResponsive,
            ]
        );
            $css .= trim($style);
        }

        if ($css !== '') {
            $this->writeCssFile($view, $css);
        }
    }

    /**
     * Get css path for a View.
     *
     * @param View $view
     *
     * @return string
     */
    public function getViewCssFile(View $view)
    {
        return $this->getViewCssFileFromHash($view->getCssHash());
    }

    /**
     * Get href for link markup for a View.
     *
     * @param View $view
     *
     * @return string
     */
    public function getHref(View $view)
    {
        return $this->webDir.'/'.$view->getCssHash().'.css';
    }

    /**
     * Remove css file.
     *
     * @param $hash
     */
    public function removeCssFile($hash)
    {
        $file = $this->getViewCssFileFromHash($hash);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Remove all views css files.
     */
    public function clearViewCssFolder()
    {
        if (!is_dir($this->viewCssDir)) {
            return;
        }

        $files = glob($this->viewCssDir.DIRECTORY_SEPARATOR.'*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Tell if css file exists for a given View.
     *
     * @param View $view
     *
     * @return bool
     */
    public function cssFileExists(View $view)
    {
        $file = $this->getViewCssFileFromHash($view->getCssHash());

        return file_exists($file);
    }

    /**
     * Construct and return css path from a hash.
     *
     * @param $hash
     *
     * @return string
     */
    private function getViewCssFileFromHash($hash)
    {
        return $this->viewCssDir.DIRECTORY_SEPARATOR.$hash.'.css';
    }

    /**
     * Write css file.
     *
     * @param $view
     * @param $css
     */
    private function writeCssFile(View $view, $css)
    {
        $oldmask = umask(0);
        if (!is_dir($this->viewCssDir)) {
            mkdir($this->viewCssDir, 0777, true);
        }
        $file = $this->getViewCssFile($view);
        file_put_contents($file, $css);
        umask($oldmask);
    }
}
