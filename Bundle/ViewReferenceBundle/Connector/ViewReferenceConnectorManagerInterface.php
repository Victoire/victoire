<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector;

interface ViewReferenceConnectorManagerInterface
{
    /**
     * This method create a viewReference in redis with an array of data.
     *
     * @param array $data
     */
    public function create(array $data);

    /**
     * This method update a viewReference with data.
     *
     * @param $id
     * @param array $data
     */
    public function update($id, array $data);

    /**
     * This method remove a reference.
     *
     * @param $id
     */
    public function remove($id);

    /**
     * This method add a child to a reference.
     *
     * @param $parentId
     * @param $childId
     */
    public function addChild($parentId, $childId);

    /**
     * This method build an url for a viewReference with parent in redis.
     *
     * @param $id
     */
    public function buildUrl($id);

    /**
     * This method set an url for a redis reference.
     *
     * @param $refId
     * @param $url
     * @param string $locale
     */
    public function setUrl($refId, $url, $locale = 'fr');

    /**
     * Remove an url.
     *
     * @param $url
     * @param $locale
     */
    public function removeUrl($url, $locale);

    /**
     * This method clear redis.
     */
    public function reset();
}
