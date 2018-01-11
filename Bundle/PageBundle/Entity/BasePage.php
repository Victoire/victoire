<?php

namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;

/**
 * Page.
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\PageBundle\Repository\BasePageRepository")
 * @ORM\HasLifecycleCallbacks
 */
abstract class BasePage extends View implements WebViewInterface
{
    use \Victoire\Bundle\PageBundle\Entity\Traits\WebViewTrait;

    /**
     * Construct.
     **/
    public function __construct()
    {
        parent::__construct();
        $this->publishedAt = new \DateTime();
        $this->status = PageStatus::PUBLISHED;
        $this->homepage = false;
    }

    /**
     * Get WebView children.
     * Exclude unpublished or not published yet if asked.
     *
     * @param bool $excludeUnpublished
     *
     * @return string
     */
    public function getWebViewChildren($excludeUnpublished = false)
    {
        $webViewChildren = [];
        foreach ($this->children as $child) {
            if (!$child instanceof BusinessTemplate) {
                $notPublished = $child->getStatus() != PageStatus::PUBLISHED;
                $scheduledDateNotReached = $child->getStatus() == PageStatus::SCHEDULED && $child->getPublishedAt() > new \DateTime();

                if ($excludeUnpublished && ($notPublished || $scheduledDateNotReached)) {
                    continue;
                }

                $webViewChildren[] = $child;
            }
        }

        return $webViewChildren;
    }
}
