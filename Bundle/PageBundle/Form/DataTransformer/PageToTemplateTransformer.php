<?php
namespace Victoire\Bundle\PageBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\PageBundle\Entity\Template;


/**
 * Transforms a page in template
 */
class PageToTemplateTransformer implements DataTransformerInterface
{

    protected $em;


    /**
     * construct
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }
    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param Page|Template $page
     * @return string
     */
    public function transform($page)
    {
        if ($page instanceof Template) {
            return $page;
        }

        $template = new Template();
        $template->setTitle($page->getTitle());
        $template->setSlug($page->getSlug());
        $template->setLayout($page->getLayout());
        $template->setWidgets($page->getWidgets());
        $template->setWidgetMap($page->getWidgetMap());

        $page->setTemplate($template);

        return $template;
    }

    /**
     * unused reverse transform
     *
     * @param  string $number
     * @return Issue|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($template)
    {
        return $template;
    }
}
