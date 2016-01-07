<?php

namespace Victoire\Bundle\ViewReferenceBundle\Cache\Redis;

use Predis\ClientInterface;

/**
 * Class ViewReferenceRedisManager
 * @package Victoire\Bundle\ViewReferenceBundle\Cache\Redis
 *
 * This class is used to make operations "update", "create", "remove" and "reset" on redis for view References
 * ref : victoire_view_reference.redis.manager
 */
class ViewReferenceRedisManager
{
    protected $redis;
    protected $tools;
    protected $alias = "reference";

    /**
     * ViewReferenceRedisManager constructor.
     * @param ClientInterface $redis
     */
    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
        $this->tools = new ViewReferenceRedisTool();
    }

    /**
     * This method create a viewReference in redis with an array of data
     * @param array $data
     * @throws \Exception
     */
    public function create(array $data)
    {
        // To generate a new view reference we need an id
        if (!isset($data['id']) && empty($data['id']))
        {
            throw new \Exception('You can\'t create a redis item without an id.');
        }
        $id = $data['id'];
        // The hash/key for the view reference is generated
        $hash = $this->tools->generateKey($this->alias, $id);

        // If if we already have a same hash we throw an exception
        if($this->redis->exists($hash))
        {
            throw new \Exception('An redis hash  "' . $this->alias . '" with id ' . $hash . ' already exist');
        }
        // Data have to be "redislize"
        $values = $this->tools->redislizeArray($data);
        // Data are set for the specified hash
        $this->redis->hmset($hash, $values);
        // Index values to be able to query them
        $this->index($id, $values);

    }

    /**
     * This method update a viewReference with data
     * @param $id
     * @param array $data
     * @throws \Exception
     */
    public function update($id, array $data)
    {
        $data['id'] = $id;
        $hash = $this->tools->generateKey($this->alias, $id);
        // If a reference with same hash exist
        if($this->redis->exists($hash))
        {
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
     * This method remove a reference
     * @param $id
     */
    public function remove($id)
    {
        $hash = $this->tools->generateKey($this->alias, $id);
        $values = $this->redis->hgetall($hash);
        foreach($values as $name => $value)
        {
            // Remove index for this reference
            $key = $this->tools->generateKey($name. "_" .$this->alias , $value);
            $this->redis->srem($key, $id);
        }
        // Remove the reference
        $this->redis->del($hash);

    }

    /**
     * This method index a reference
     * @param $id
     * @param $values
     */
    public function index($id, $values)
    {
        foreach ($values as $name => $value)
        {
            $key = $this->tools->generateKey($name. "_" .$this->alias , $value);
            // Store id for value
            $this->redis->sadd($key, $id);
        }
    }

    /**
     * This method add a child to a reference
     * @param $parentId
     * @param $childId
     */
    public function addChild($parentId, $childId)
    {
        $parentHash = $this->tools->generateKey($this->alias , $parentId);
        $childHash = $this->tools->generateKey($this->alias , $childId);
        $hash = $this->tools->generateKey($parentHash, "children");
        // Index the child in list of children to parent
        $this->redis->sadd($hash, $childId);
        // Index and add the value of parent for the child
        $this->redis->hset($childHash, "parent", $parentId);
        $this->redis->sadd("parent_" . $parentHash, $childId);
    }

    /**
     * This method clear redis
     */
    public function reset()
    {
        $this->redis->flushall();
    }
}