<?php
namespace Victoire\Bundle\WidgetBundle\Resolver\Chain;

class WidgetContentResolverChain
{

    private $resolvers;

    public function __construct()
    {
        $this->resolvers = array();
    }

    public function addResolver($alias, $resolver)
    {
        $this->resolvers[$alias] = $resolver;
    }

    public function getResolvers()
    {
        return $this->resolvers;
    }

    public function hasResolver($alias)
    {
        if (array_key_exists($alias, $this->resolvers)) {
            return $this->resolvers[$alias];
        }

        return false;
    }
    public function getResolver($alias)
    {
        if (array_key_exists($alias, $this->resolvers)) {
            return $this->resolvers[$alias];
        } else {
            throw new \InvalidArgumentException(sprintf('The "%s" resolver does not exist', $alias));
        }
    }
}
