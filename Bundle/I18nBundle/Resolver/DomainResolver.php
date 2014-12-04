<?php

namespace Victoire\Bundle\I18nBundle\Resolver;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;

class DomainResolver
{
	
	protected $container;

	/**
	* Constructor
	* @param ContainerInterface $container
	*
	*/
	public function __construct(ContainerInterface $container) 
	{
		$this->container = $container;
	}

	/**
	* @param GetResponseEvent $event
	* method called on kernelRequest it sets the domain in request depending on locale
	* it also set the victoire_locale wich is the locale of the application admin
	*/
	public function onKernelRequest(GetResponseEvent $event)
    {
    	if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        } else {
        	$request = $event->getRequest();
        	$locale = $request->getLocale();
        	$host = $this->getHost($locale);
        	if (null!== $host) {
				$this->container->get('router')->getContext()->setHost($host);
			} 
        }
    }

	/**
	* @param $locale a locale
	*
	* method called to retreive the host from a locale, the host corresponding to locals is noramly defined in the config of the
	* bundle
	* @return string $host
	*/
	public function getHost($locale) 
    {
        $host = null;
        $localePattern = $this->container->getParameter('victoire_i18n.locale_pattern');
        if ('domain' === $localePattern) {
            $localePatternTable = $this->container->getParameter('victoire_i18n.locale_pattern_table');

            foreach ($localePatternTable as $key => $val) {
                if($locale === $val) {
                    $host = $key;
                }
            }
        }

        return $host;
    }
}
