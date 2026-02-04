<?php

namespace App\Tests\Controller\Website;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SitemapControllerTest extends WebTestCase
{
    public function testSitemapXml(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sitemap.xml');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'text/xml; charset=UTF-8');
        
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('<loc>http://localhost/</loc>', $content);
        $this->assertStringContainsString('<lastmod>', $content);
    }

    public function testRobotsTxt(): void
    {
        $client = static::createClient();
        $client->request('GET', '/robots.txt');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertStringContainsString('User-agent: *', $client->getResponse()->getContent());
        $this->assertStringContainsString('Sitemap:', $client->getResponse()->getContent());
    }
}
