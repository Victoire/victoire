<?php

namespace Victoire\Bundle\I18nBundle\Resolver;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

/**
* A class to guess the locale form URL
*/
class LocaleResolver
{
    const PATTERNDOMAIN         = 'domain';
    const PATTERNPARAMETERINURL = 'inurl';

	protected $localePattern;
	protected $localePatternTable;
	protected $defaultLocale;
	protected $em;Ò

	/**
	* Constructor
	*
	* @param string $localePattern 
	* @param string $localPatternTable
	* @param string $defaultLocale
	*/
	public function __construct($localePattern, $localePatternTable, $defaultLocale, EntityManager $em) 
	{
		$this->localePattern = $localePattern;
		$this->localePatternTable = $localePatternTable;
		$this->defaultLocale = $defaultLocale;
		$this->em = $em;
	}

	/**
	* @param GetResponseEvent $event
	*/
	public function onKernelRequest(GetResponseEvent $event)
    {
		if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        } else {
        	$request = $event->getRequest();
        	$victoireLocale = $request->getSession()->get('victoire_locale');
        	
        	if (!isset($victoireLocale)) {

	        	switch ($this->localePattern) {
	        		case self::PATTERNDOMAIN : 
	        		    $locale = $this->resolveFromDomain($request);
	        		    $request->getSession()->set('victoire_locale', $locale);
	        	        break;

	        	     case self::PATTERNPARAMETERINURL : 
	        		    $locale = $this->resolveAsParameterInUrl($request);
	        		    $request->getSession()->set('victoire_locale', $locale);
	        	        break;
	        	    default : 
	        	        break; 
        	    }   
        	}
        }
    }

    /**
    * @param Request $request
    *
    * @return string 
    */
	public function resolveFromDomain(Request $request) 
	{
		$host = $request->getHttpHost();
        $domain = substr($host, strrpos($host, '.')+1);

        return $this->localePatternTable[$domain];
	}

	/**
    * @param Request $request
    *
    * @return string 
    */
	public function resolveAsParameterInUrl(Request $request) 
	{
		$uri = $request->getRequestUri();
		if(strstr($uri, '/app_dev.php/')) {
			$uri = str_replace('/app_dev.php/', '', $uri);
		} else {
			$uri = ltrim ($uri, '/');
		}

		$endLocale  = strpos($uri, '/');
		if (!empty($domain)) {
		   $domain = substr($uri, 0, $endLocale);
		   return $this->localePatternTable[$domain];
		} else {
			return $this->defaultLocale;
		}
	}
}
