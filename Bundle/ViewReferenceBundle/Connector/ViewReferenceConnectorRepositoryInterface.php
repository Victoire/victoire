<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector;

interface ViewReferenceConnectorRepositoryInterface
{
    /**
     * This method return all references.
     *
     * @return array
     */
    public function getAll();

    /**
     * This method return all references matching with criteria
     * Support "AND" and "OR".
     *
     * @param array  $criteria
     * @param string $type
     *
     * @return array
     */
    public function getAllBy(array $criteria, $type = 'AND');

    /**
     * This method return an array of references matching with an array of id.
     *
     * @param array $data
     *
     * @return array
     */
    public function getResults(array $data);

    /**
     * This method return a reference matching with a ref id.
     *
     * @param $id
     *
     * @return array
     */
    public function findById($id);

    /**
     * This method return a specific value for an id or null if not exist.
     *
     * @param $value
     * @param $id
     *
     * @return mixed|null|string
     */
    public function findValueForId($value, $id);

    /**
     * getChildren ids for a ref id.
     *
     * @param $id
     *
     * @return array
     */
    public function getChildren($id);


    /**
     * Find a ref id for an url.
     *
     * @param string $url
     * @param string $locale
     *
     * @return mixed|string
     */
    public function findRefIdByUrl($url = '', $locale = 'fr');
}
