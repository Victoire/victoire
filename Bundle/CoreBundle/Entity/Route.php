<?php

namespace Victoire\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Route.
 *
 * @ORM\Table("vic_route_history")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\CoreBundle\Repository\RouteRepository")
 */
class Route
{
    use \Gedmo\Timestampable\Traits\TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \stdClass
     *
     * @ORM\ManyToOne(targetEntity="\Victoire\Bundle\CoreBundle\Entity\View", inversedBy="routes", cascade={"persist"})
     * @ORM\JoinColumn(name="view_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $view;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    protected $url;

    /**
     * contructor.
     **/
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * The to string method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->url;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set view.
     *
     * @param View $view
     *
     * @return Route
     */
    public function setView(View $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get view.
     *
     * @return \stdClass
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Route
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
