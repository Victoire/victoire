<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector;

interface ViewReferenceConnectorRepositoryInterface
{
    public function getAll();
    public function getAllBy(array $criteria, $type = 'AND');
    public function getResults(array $data);
    public function findById($id);
    public function findValueForId($value, $id);
    public function getChildren($id);
    public function findRefIdByUrl($url = '', $locale = 'fr');
}