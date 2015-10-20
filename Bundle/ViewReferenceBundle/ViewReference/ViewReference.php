<?php

namespace Victoire\Bundle\ViewReferenceBundle\ViewReference;


use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;

class ViewReference
{
    private $id;
    private $locale;
    private $entityId;
    private $entityNamespace;
    private $slug;
    private $viewId;
    private $viewNamespace;
    private $patternId;
    private $name;
    private $children;
    private $view;

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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPatternId()
    {
        return $this->patternId;
    }

    /**
     * @param int $patternId
     */
    public function setPatternId($patternId)
    {
        $this->patternId = $patternId;
    }

    /**
     * @return string
     */
    public function getViewNamespace()
    {
        return $this->viewNamespace;
    }

    /**
     * @param string $viewNamespace
     */
    public function setViewNamespace($viewNamespace)
    {
        $this->viewNamespace = $viewNamespace;
    }

    /**
     * @return int
     */
    public function getViewId()
    {
        return $this->viewId;
    }

    /**
     * @param int $viewId
     */
    public function setViewId($viewId)
    {
        $this->viewId = $viewId;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }

    /**
     * @param string $entityNamespace
     */
    public function setEntityNamespace($entityNamespace)
    {
        $this->entityNamespace = $entityNamespace;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return ViewReference[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param  ViewReference[] $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return WebViewInterface
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param WebViewInterface $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }
}