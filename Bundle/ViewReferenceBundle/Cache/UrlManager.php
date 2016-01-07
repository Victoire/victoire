<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache;


use Predis\ClientInterface;
use Victoire\Bundle\ViewReferenceBundle\Cache\Redis\ViewReferenceRedisRepository;
use Victoire\Bundle\ViewReferenceBundle\Cache\Redis\ViewReferenceRedisTool;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * Class UrlManager
 * @package Victoire\Bundle\ViewReferenceBundle\Cache
 *
 * This class manage url vor ViewReferences
 * ref : victoire_view_reference.url.manager
 */
class UrlManager
{
    private $viewReferenceRepository;
    private $redis;
    private $tool;

    /**
     * UrlManager constructor.
     * @param ViewReferenceRedisRepository $viewReferenceRepository
     * @param ClientInterface $redis
     */
    public function __construct(ViewReferenceRedisRepository $viewReferenceRepository, ClientInterface $redis)
    {
        $this->viewReferenceRepository = $viewReferenceRepository;
        $this->redis = $redis;
        $this->tool = new ViewReferenceRedisTool();
    }

    /**
     * This method build an url for a viewReference with parent in redis
     * @param ViewReference $viewReference
     */
    public function buildUrl(ViewReference $viewReference)
    {
        $reference = $this->viewReferenceRepository->findById($viewReference->getId());
        $url = "";
        // while the reference has a slug
        while(isset($reference['slug']) && $reference['slug'] != "" )
        {
            // Build url
            if($url != "")
            {
                $url = $reference['slug'].'/'. $url;
            }else{
                $url = $reference['slug'];
            }
            // Set reference with the parent
            if($parentId = $this->tool->unredislize($reference['parent']))
            {
                $reference = $this->viewReferenceRepository->findById($parentId);
            }else{
                $reference = array();

            }
        }
        // set the new url
        $this->setUrl($viewReference->getId(), $url, $viewReference->getLocale());
    }

    /**
     * This method set an url for a redis reference
     * @param $refId
     * @param $url
     * @param string $locale
     */
    public function setUrl($refId, $url, $locale = "fr")
    {
        //if an url exist for the current reference
        if($this->redis->hexists("reference:".$refId, "url"))
        {
            // Remove the old url
            $refUrl = $this->tool->unredislize($this->redis->hget("reference:".$refId, "url"));
            if($refUrl != "")
            {
                $this->removeUrl($refUrl, $locale);
                $this->redis->hdel("reference:".$refId, "url");
            }
        }
        // Set the new url
        $this->redis->set($locale.":/".$url, $refId);
        $this->redis->hset("reference:".$refId, "url", $url);
    }

    /**
     * Find a ref id for an url
     * @param string $url
     * @param string $locale
     * @return mixed|string
     */
    public function findRefIdByUrl($url ="", $locale = "fr")
    {
        $refId = $this->tool->unredislize($this->redis->get($locale.":".$url));
        return $refId;
    }

    /**
     * Remove an url
     * @param $url
     * @param $locale
     */
    public function removeUrl($url, $locale)
    {
        if($url == "" || $url[0] != "/")
        {
            $url = "/" . $url;
        }
        $this->redis->del($locale.':'.$url);
    }

    /**
     * Remove an url for a viewReference with his reference in redis
     * @param ViewReference $viewReference
     */
    public function removeUrlForViewReference(ViewReference $viewReference)
    {
        $id = $viewReference->getId();
        if($url = $this->viewReferenceRepository->findValueForId('url', $id))
        {
            $this->removeUrl($url, $this->viewReferenceRepository->findValueForId('locale', $id));
        }
    }
}