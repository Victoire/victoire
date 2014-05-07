<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Symfony\Component\DependencyInjection\Container;

class UserCallableHelper
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param callable
     * @param string $userEntity
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

   /**
    * @{inheritdoc}
    */
   public function getCurrentUser()
   {
        $userClass = $this->container->getParameter('victoire_cms.user_class');
        $token = $this->container->get('security.context')->getToken();
        if ($token->getUser() instanceof $userClass) {
            return $token->getUser();
        }

        return null;
   }
}
