<?php

namespace Victoire\Bundle\SitemapBundle\Tests;

use Symfony\Component\DomCrawler\Crawler;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\SeoBundle\Entity\PageSeo;
use Victoire\Tests\Functional\VictoireWebTestCase;

class SitemapTest extends VictoireWebTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->resetSchema();
        $this->loadFixtures();
    }

    public function testPageIndexation()
    {
        /* @var BasePage $homepage */
        $homepage = $this->entityManager->getRepository('VictoirePageBundle:BasePage')->findOneByHomepage('en');

        //English first
        $pageSeo = new PageSeo();
        $pageSeo->setCurrentLocale('en');
        $pageSeo->setSitemapIndexed(false);
        $pageSeo->setSitemapPriority(0.8);
        $this->entityManager->persist($pageSeo);

        $page = new Page();
        $page->setCurrentLocale('en');
        $page->setName('Indexed page');
        $page->setSeo($pageSeo);
        $page->setParent($homepage);
        $this->entityManager->persist($page);

        //French
        $page->setCurrentLocale('fr');
        $page->setName('Page indexée');
        $pageSeo->setCurrentLocale('fr');
        $pageSeo->setSitemapIndexed(true);
        $pageSeo->setSitemapPriority(0.3);

        $this->entityManager->flush();
        $this->resetViewsReference();

        $englishAssertions = [
            [
                'loc'        => '/en/',
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ],
            [
                'loc'        => '/en/english-test',
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ],
        ];

        $frenchAssertions = [
            [
                'loc'        => '/fr/',
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ],
            [
                'loc'        => '/fr/test',
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ],
            [
                'loc'        => '/fr/page-indexee',
                'changefreq' => 'monthly',
                'priority'   => '0.3',
            ],
        ];

        $englishPages = $this->getSitemapPages('en');
        $this->runAssertions($englishPages, $englishAssertions);

        $frenchPages = $this->getSitemapPages('fr');
        $this->runAssertions($frenchPages, $frenchAssertions);
    }

    /**
     * @return Crawler
     */
    private function getSitemapPages($locale = 'en')
    {
        $client = $this->createClient();
        $this->logIn($client, ['ROLE_ADMIN']);
        $client->request('GET', '/'.$locale.'/sitemap.xml');

        return $client->getCrawler()->filter('urlset > url');
    }

    /**
     * @param Crawler $pages
     * @param array   $assertions
     */
    private function runAssertions(Crawler $pages, array $assertions)
    {
        foreach ($assertions as $i => $v) {
            $this->assertStringEndsWith($assertions[$i]['loc'], $pages->eq($i)->filter('loc')->text());
            $this->assertEquals($assertions[$i]['changefreq'], $pages->eq($i)->filter('changefreq')->text());
            $this->assertEquals($assertions[$i]['priority'], $pages->eq($i)->filter('priority')->text());
        }
    }
}
