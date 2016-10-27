<?php

namespace Victoire\Bundle\CoreBundle\Controller;

/**
 * @property Container container
 */
trait VictoireAlertifyControllerTrait
{
    /**
     * Alert message to flash bag.
     *
     * @param string $content Captain Obvious ? We have to setup a content
     * @param string $type    Success or Error ? Warning or Info ? You choose !
     */
    public function alert($content, $type = 'success')
    {
        if (!is_array($content)) {
            $content = [
                'body' => $content,
            ];
        }

        $content = array_merge($content, [
                'context' => 'victoire',
                'layout'  => 'growl',
                'effect'  => 'jelly',
            ]);
        $this->container->get('alertify')->alert($content, $type);
    }

    /**
     * Congrats user through flash bag : all happened successfully.
     *
     * @param string $content
     */
    public function congrat($content)
    {
        $this->alert($content, 'success');
    }

    /**
     * Warn user through flash bag: something requires attention.
     *
     * @param string $content
     */
    public function warn($content)
    {
        $this->alert($content, 'warning');
    }

    /**
     * Inform user through flash bag: something have to be said.
     *
     * @param string $content
     */
    public function inform($content)
    {
        $this->alert($content, 'info');
    }

    /**
     * Scold user through flash bag: something went wrong.
     *
     * @param string $content
     */
    public function scold($content)
    {
        $this->alert($content, 'error');
    }
}
