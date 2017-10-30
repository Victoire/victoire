<?php

namespace Victoire\Bundle\RedirectionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class NotFoundError
 *
 * @ORM\Entity()
 * @ORM\Table("vic_notFoundError")
 */
class NotFoundError
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string")
     */
    private $url;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer")
     */
    private $count = 1;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return NotFoundError
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return NotFoundError
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return $this
     */
    public function increaseCount(){
        $this->count += 1;
        return $this;
    }
}