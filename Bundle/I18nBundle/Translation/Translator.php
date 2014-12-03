<?php

namespace Victoire\Bundle\I18nBundle\Translation;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Config\ConfigCache;

class Translator extends BaseTranslator 
{

    protected $container;
    protected $options = array(
        'cache_dir' => 'test',
        'debug'     => true,
    );
    protected $loaderIds;

    public function __construct(ContainerInterface $container, MessageSelector $selector, $loaderIds = array(), array $options = array())
    {
        parent::__construct($container, $selector, $loaderIds, $options);
    }

	/**
     * {@inheritdoc}
     *
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }
        if (null === $domain) {
            $domain = 'messages';
        } elseif ('victoire' === $domain) {
        	$locale = $this->getVictoireLocale();
        }
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        return strtr($this->catalogues[$locale]->get((string) $id, $domain), $parameters);
    }
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }
        if (null === $domain) {
            $domain = 'messages';
        } elseif ('victoire' === $domain) {
        	$locale = $this->getVictoireLocale();
        }
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }
        $id = (string) $id;
        $catalogue = $this->catalogues[$locale];
        while (!$catalogue->defines($id, $domain)) {
            if ($cat = $catalogue->getFallbackCatalogue()) {
                $catalogue = $cat;
                $locale = $catalogue->getLocale();
            } else {
                break;
            }
        }
        return strtr($this->selector->choose($catalogue->get($id, $domain), (int) $number, $locale), $parameters);
    }

    public function setContainer(ContainerInterface $container) 
    {
        $this->container = $container;
    }

    public function getLocale()
    {
        $this->locale = $this->container->get('request')->getLocale();

        return $this->locale;
    }
    
    public function getVictoireLocale() 
    {
        $this->locale = $this->container->get('request')->getSession()->get('victoire_locale');

        return $this->locale;
    }
    
}
