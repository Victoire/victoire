<?php

namespace Victoire\Bundle\I18nBundle\Resolver;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;

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

	/**
	* Constructor
	*
	* @param string $localePattern 
	* @param string $localPatternTable
	* @param string $defaultLocale
	*/
	public function __construct($localePattern, $localePatternTable, $defaultLocale) 
	{
		$this->localePattern = $localePattern;
		$this->localePatternTable = $localePatternTable;
		$this->defaultLocale = $defaultLocale;
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
        		$request->getSessions()->set('victoire_locale', 'fr');
        	}
        	$locale = $request->getLocale();
        	
        	if (!isset($locale)) {

	        	switch ($this->localePattern) {
	        		case self::PATTERNDOMAIN : 
	        		    $locale = $this->resolveFromDomain($request);
	        		    $request->setLocale($locale);
	        	        break;

	        	     case self::PATTERNPARAMETERINURL : 
	        		    $locale = $this->resolveAsParameterInUrl($request);
	        		    $request->setLocale($locale);
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
