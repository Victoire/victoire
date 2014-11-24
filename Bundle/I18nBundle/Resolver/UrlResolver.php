<?php

namespace Victoire\Bundle\I18nBundle\Resolver;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\PageBundle\Helper\PageHelper;

class UrlResolver
{
	
	protected $pageHelper;
	protected $em;

	/**
	* Constructor
	*/
	public function __construct(PageHelper $pageHelper, EntityManager $em)
	{
		$this->pageHelper = $pageHelper;
		$this->em =$em;
	}

	/**
	* @param $currentLocale the current locale
	* @param $targetLocale the target locale
	*
	* Method that resolve the url for the current page in another locale
	*
	* @return string $targetUrl the target url found in database
	*/
	public function findUrlForTargetLocale($currentUrl, $currentLocale, $targetLocale)
	{
		$currentPage = $this->pageHelper->findPageByParameters(array('url' => $currentUrl, 'locale' => $currentLocale));

		if (null === $currentPage->getTranslationSource()) {
			$targetPage = $this->em->getRepository('VictoirePageBundle:Page')->findOneBy(array('translationSource' => $currentPage->getId(), 'locale' => $targetLocale));
		} else {
			$targetPage = $this->em->getRepository('VictoirePageBundle:Page')->findOneById($currentPage->getTranslationSource());
		}
		
		return $targetPage->getUrl();
	}
}
