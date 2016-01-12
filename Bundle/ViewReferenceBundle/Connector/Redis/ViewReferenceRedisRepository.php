<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector\Redis;

use Predis\ClientInterface;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceConnectorRepositoryInterface;

/**
 * Class ViewReferenceRedisRepository.
 */
class ViewReferenceRedisRepository implements ViewReferenceConnectorRepositoryInterface
{
    protected $redis;
    private $alias = 'reference';
    private $tools;

    /**
     * ViewReferenceRedisRepository constructor.
     *
     * @param ClientInterface $redis
     */
    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
        $this->tools = new ViewReferenceRedisTool();
    }

    /**
     * @inheritdoc
     */
    public function getAll()
    {
        return $this->redis->keys($this->alias.'*');
    }

    /**
     * @inheritdoc
     */
    public function getAllBy(array $criteria, $type = 'AND')
    {
        $filters = [];
        $criteria = $this->tools->redislizeArray($criteria);
        // Create hashs for every index needed
        foreach ($criteria as $key => $value) {
            $filters[] = $this->tools->generateKey($key.'_'.$this->alias, $value);
        }
        // Call the right method for "AND" or "OR"
        if ($type != 'OR') {
            return $this->redis->sinter($filters);
        } else {
            return $this->redis->sunion($filters);
        }
    }

    /**
     * @inheritdoc
     */
    public function getResults(array $data)
    {
        $results = [];
        foreach ($data as $item) {
            $results[] = $this->findById($item);
        }

        return $results;
    }
    public function findById($id)
    {
        return $this->tools->unredislizeArray($this->redis->hgetall($this->alias.':'.$id));
    }

    /**
     * @inheritdoc
     */
    public function findValueForId($value, $id)
    {
        $reference = $this->findById($id);
        if (!isset($reference[$value])) {
            return;
        }

        return $this->tools->unredislize($reference[$value]);
    }

    /**
     * @inheritdoc
     */
    public function getChildren($id)
    {
        return $this->redis->smembers($this->alias.':'.$id.':children');
    }

    /**
     * @param $alias
     *
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function findRefIdByUrl($url = '', $locale = 'fr')
    {
        if ($url == '' || $url[0] != '/') {
            $url = '/'.$url;
        }
        $refId = $this->tools->unredislize($this->redis->get($locale.':'.$url));

        return $refId;
    }
}
