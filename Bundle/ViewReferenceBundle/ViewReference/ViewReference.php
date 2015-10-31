<?php

namespace Victoire\Bundle\ViewReferenceBundle\ViewReference;


use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;

class ViewReference
{
    protected $id;
    protected $locale;
    protected $slug;
    protected $viewId;
    protected $viewNamespace;

    /**
     * looks like ref_{view.id}[_{view.businessEntity.id}]
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getViewId()
    {
        return $this->viewId;
    }

    /**
     * @param mixed $viewId
     */
    public function setViewId($viewId)
    {
        $this->viewId = $viewId;
    }

    /**
     * @return mixed
     */
    public function getViewNamespace()
    {
        return $this->viewNamespace;
    }

    /**
     * @param mixed $viewNamespace
     */
    public function setViewNamespace($viewNamespace)
    {
        $this->viewNamespace = $viewNamespace;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }
}