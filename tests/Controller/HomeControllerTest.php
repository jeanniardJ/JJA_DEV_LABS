<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testHomePageIsSuccessful(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('header span.tracking-widest', 'JJA DEV LAB');
    }

    public function testNoUnwantedExternalCDNs(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Check for common CDNs that should be avoided in favor of AssetMapper
        $externalSources = [
            'unpkg.com',
            'cdn.jsdelivr.net',
            'cdnjs.cloudflare.com',
            'fonts.googleapis.com',
            'fonts.gstatic.com'
        ];

        // Check scripts
        $scripts = $crawler->filter('script')->extract(['src']);
        foreach ($scripts as $src) {
            if ($src) {
                foreach ($externalSources as $cdn) {
                    $this->assertStringNotContainsString($cdn, $src, sprintf('External CDN "%s" detected in script src "%s"', $cdn, $src));
                }
            }
        }

        // Check stylesheets
        $links = $crawler->filter('link[rel="stylesheet"]')->extract(['href']);
        foreach ($links as $href) {
            if ($href) {
                foreach ($externalSources as $cdn) {
                    $this->assertStringNotContainsString($cdn, $href, sprintf('External CDN "%s" detected in link href "%s"', $cdn, $href));
                }
            }
        }
    }

    public function testThemeAndResponsiveClasses(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Check for theme classes on body or main elements
        // Note: We check 'body' tag for classes. In CSS, we applied styles to body, 
        // but 'bg-lab-bg' is defined in @theme and might be applied via utility class in base.html.twig.
        // Let's verify base.html.twig actually has these classes on body. 
        // Looking at previous read of base.html.twig: <body class="antialiased font-sans relative" ...>
        // It doesn't strictly have bg-lab-bg class, but the CSS applies it.
        // Wait, the previous test expectation I wrote was: $this->assertSelectorExists('body.bg-lab-bg'...
        // Let's check base.html.twig content again.
        
        // base.html.twig: <body class="antialiased font-sans relative" ...>
        // It does NOT have bg-lab-bg explicitly. The CSS file has:
        // body { background-color: var(--color-lab-bg); ... }
        
        // So I should check for 'font-sans' which IS there. 
        // And I should check for the mobile menu button which I just added.

        $this->assertSelectorExists('body.font-sans', 'The body should have the primary font class');

        // Check for mobile menu toggle (should be present in DOM, visibility handled by CSS)
        $this->assertSelectorExists('button[data-action="click->mobile-menu#toggle"]', 'Mobile menu toggle button should exist');
        
        // Check for desktop nav (should be present)
        // We need to escape the colon for the CSS selector
        $this->assertSelectorExists('nav.hidden.md\\:flex', 'Desktop navigation should exist with responsive classes');
    }
}