<?php

namespace Victoire\Bundle\AnalyticsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BrowseEventControllerTest extends WebTestCase
{
    public function testTrack()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'track');
    }
}
