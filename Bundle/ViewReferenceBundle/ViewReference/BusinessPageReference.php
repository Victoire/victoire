<?php

namespace Victoire\Bundle\ViewReferenceBundle\ViewReference;

class BusinessPageReference extends ViewReference
{
    protected $entityId;
    protected $entityNamespace;
    protected $templateId;
    protected $businessEntity;

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @deprecated
     * @return mixed
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }

    /**
     * @deprecated
     * @param mixed $entityNamespace
     */
    public function setEntityNamespace($entityNamespace)
    {
        $this->entityNamespace = $entityNamespace;
    }

    /**
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @param mixed $templateId
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     * @return mixed
     */
    public function getBusinessEntity()
    {
        return $this->businessEntity;
    }

    /**
     * @param mixed $businessEntity
     */
    public function setBusinessEntity($businessEntity)
    {
        $this->businessEntity = $businessEntity;
    }
}
