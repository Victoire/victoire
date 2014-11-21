<?php

namespace Victoire\Bundle\I18nBundle\Resolver;

use Doctrine\Orm\EntityManager;

class UrlResolver
{
	
	protected $em;

	/**
	* Constructor
	*/
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
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

		return $targetUrl;
	}
}
