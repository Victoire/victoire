<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Symfony\Component\DependencyInjection\Container;

/**
 * @property Container container
 */
trait VictoireAlertifyControllerTrait {

    /**
     * Alert message to flash bag.
     *
     * @param string $content Captain Obvious ? We have to setup a content
     * @param string $type    Success or Error ? Warning or Info ? You choose !
     */
    public function alert($content, $type = 'success')
    {
        if (is_array($content)) {
            $content['context'] = 'victoire';
        } else {
            $content = array(
                'context' => 'victoire',
                'body'    => $content
            );
        }
        $this->container->get('appventus_alertifybundle.helper.alertifyhelper')->alert($content, $type);
    }

    /**
     * Congrats user through flash bag : all happened successfully
     *
     * @param string $content
     */
    public function congrat($content)
    {
        $this->alert($content, 'success');
    }

    /**
     * Warn user through flash bag: something requires attention
     *
     * @param string $content
     */
    public function warn($content)
    {
        $this->alert($content, 'warning');
    }

    /**
     * Inform user through flash bag: something have to be said
     *
     * @param string $content
     */
    public function inform($content)
    {
        $this->alert($content, 'info');
    }

    /**
     * Scold user through flash bag: something went wrong
     *
     * @param string $content
     */
    public function scold($content)
    {
        $this->alert($content, 'error');
    }
}
