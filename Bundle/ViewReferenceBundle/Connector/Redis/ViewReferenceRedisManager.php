<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector\Redis;

use Predis\ClientInterface;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceConnectorManagerInterface;

/**
 * Class ViewReferenceRedisManager.
 */
class ViewReferenceRedisManager implements ViewReferenceConnectorManagerInterface
{
    protected $redis;
    protected $tools;
    protected $alias = 'reference';
    protected $repository;

    /**
     * ViewReferenceRedisManager constructor.
     *
     * @param ClientInterface $redis
     */
    public function __construct(ClientInterface $redis, ViewReferenceRedisRepository $repository)
    {
        $this->redis = $redis;
        $this->tools = new ViewReferenceRedisTool();
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        // To generate a new view reference we need an id
        if (!isset($data['id']) && empty($data['id'])) {
            throw new \Exception('You can\'t create a redis item without an id.');
        }
        $id = $data['id'];
        // The hash/key for the view reference is generated
        $hash = $this->tools->generateKey($this->alias, $id);

        // If if we already have a same hash we throw an exception
        if ($this->redis->exists($hash)) {
            throw new \Exception('An redis hash  "'.$this->alias.'" with id '.$hash.' already exist');
        }
        // Data have to be "redislize"
        $values = $this->tools->redislizeArray($data);
        // Data are set for the specified hash
        $this->redis->hmset($hash, $values);
        // Index values to be able to query them
        $this->index($id, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        $data['id'] = $id;
        $hash = $this->tools->generateKey($this->alias, $id);
        // If a reference with same hash exist
        if ($this->redis->exists($hash)) {
            $oldValues = $this->redis->hgetall($hash);
            $oldValues = $this->tools->unredislizeArray($oldValues);
            // The old reference is removed
            $this->remove($id);
            // Old and new data are merge
            $data = array_merge($oldValues, $data);
        }
        // Create the new reference
        $this->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($id)
    {
        $hash = $this->tools->generateKey($this->alias, $id);
        $values = $this->redis->hgetall($hash);
        foreach ($values as $name => $value) {
            // Remove index for this reference
            $key = $this->tools->generateKey($name.'_'.$this->alias, $value);
            $this->redis->srem($key, $id);
        }
        // Remove the reference
        $this->redis->del($hash);
    }

    /**
     * This method index a reference.
     *
     * @param $id
     * @param $values
     */
    public function index($id, $values)
    {
        foreach ($values as $name => $value) {
            $key = $this->tools->generateKey($name.'_'.$this->alias, $value);
            // Store id for value
            $this->redis->sadd($key, $id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addChild($parentId, $childId)
    {
        $parentHash = $this->tools->generateKey($this->alias, $parentId);
        $childHash = $this->tools->generateKey($this->alias, $childId);
        $hash = $this->tools->generateKey($parentHash, 'children');
        // Index the child in list of children to parent
        $this->redis->sadd($hash, $childId);
        // Index and add the value of parent for the child
        $this->redis->hset($childHash, 'parent', $parentId);
        $this->redis->sadd('parent_'.$parentHash, $childId);
    }

    /**
     * {@inheritdoc}
     */
    public function buildUrl($id)
    {
        $reference = $this->repository->findById($id);
        $url = '';
        // while the reference has a slug
        while (isset($reference['slug']) && $reference['slug'] != '') {
            // Build url
            if ($url != '') {
                $url = $reference['slug'].'/'.$url;
            } else {
                $url = $reference['slug'];
            }
            // Set reference with the parent
            if ($parentId = $reference['parent']) {
                $reference = $this->repository->findById($parentId);
            } else {
                $reference = [];
            }
        }
        // set the new url
        $this->setUrl($id, $url, $reference['locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function setUrl($refId, $url, $locale = 'fr')
    {
        //if an url exist for the current reference
        if ($this->redis->hexists('reference:'.$refId, 'url')) {
            // Remove the old url
            $refUrl = $this->tools->unredislize($this->redis->hget('reference:'.$refId, 'url'));
            if ($refUrl != '') {
                $this->removeUrl($refUrl, $locale);
                $this->redis->hdel('reference:'.$refId, 'url');
            }
        }
        // Set the new url
        $this->redis->set($locale.':/'.$url, $refId);
        $this->redis->hset('reference:'.$refId, 'url', $url);
    }

    /**
     * {@inheritdoc}
     */
    public function removeUrl($url, $locale)
    {
        if ($url == '' || $url[0] != '/') {
            $url = '/'.$url;
        }
        $this->redis->del($locale.':'.$url);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->redis->flushall();
    }
}
