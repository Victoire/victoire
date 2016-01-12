<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector;

interface ViewReferenceConnectorManagerInterface
{
    public function create(array $data);

    public function update($id, array $data);

    public function remove($id);

    public function index($id, $values);

    public function addChild($parentId, $childId);

    public function buildUrl($id);

    public function setUrl($refId, $url, $locale = 'fr');

    public function removeUrl($url, $locale);

    public function reset();
}
