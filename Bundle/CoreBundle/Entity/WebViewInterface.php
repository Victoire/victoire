<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Victoire\Bundle\SeoBundle\Entity\PageSeo;

/**
 * Victoire View.
 */
interface WebViewInterface
{
    public function getUrl();

    public function setUrl($url);

    public function setSeo(PageSeo $seo);

    public function getSeo();

    public function getReferers();

    public function setReferers($referers);

    public function setRoutes($routes);

    public function removeRoute(Route $route);

    public function addRoute(Route $route);

    public function getRoutes();

    public function setStatus($status);

    public function getStatus();

    public function setChildren($children);

    public function getChildren();

    public function hasChildren();

    public function setViewReference(array $viewReference);

    public function getViewReference();

    public function setPublishedAt($publishedAt);

    public function getPublishedAt();

    public function isPublished();

    public function isHomepage();

    public function setHomepage($homepage);
}
