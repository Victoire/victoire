<?php

namespace Victoire\Bundle\ViewReferenceBundle\ViewReference;

class ViewReference
{
    protected $id;
    protected $locale;
    protected $name;
    protected $slug;
    /**
     * @var string built by ViewReferenceCacheRepo
     */
    protected $url;
    protected $viewId;
    protected $viewNamespace;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * looks like ref_{view.id}[_{view.businessEntity.id}].
     *
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
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

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
