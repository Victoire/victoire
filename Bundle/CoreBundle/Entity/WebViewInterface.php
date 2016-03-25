<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Victoire\Bundle\SeoBundle\Entity\PageSeo;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

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

    public function setStatus($status);

    public function getStatus();

    public function setChildren($children);

    public function getChildren();

    public function hasChildren();

    public function setReference(ViewReference $reference, $locale = null);
    /**
     * @param ViewReference[]
     */
    public function setReferences($references);

    /**
     * @return ViewReference
     */
    public function getReference($locale = null);

    /**
     * @return ViewReference[]
     */
    public function getReferences();

    public function setPublishedAt($publishedAt);

    public function getPublishedAt();

    public function isPublished();

    public function isHomepage();

    public function setHomepage($homepage);
}
