<?php
namespace Victoire\Bundle\PageBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\TemplateBundle\Entity\Template;

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
     * @param Page $page
     *
     * @return Template
     */
    public function transform($page)
    {
        if ($page instanceof Template) {
            return $page;
        }

        $template = new Template();
        $template->setName($page->getName());
        $template->setSlug($page->getSlug());
        $template->setLayout($page->getTemplate()->getLayout());
        $template->setWidgets($page->getWidgets());
        $template->setWidgetMap($page->getWidgetMap());

        $page->setTemplate($template);

        return $template;
    }

    /**
     * unused reverse transform
     * @param string $template
     *
     * @return string
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($template)
    {
        return $template;
    }
}
