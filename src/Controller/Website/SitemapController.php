<?php

namespace App\Controller\Website;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function sitemap(Request $request): Response
    {
        $urls = [];

        // Pages statiques principales
        $routes = [
            'app_home',
            'app_lab_stations',
            'app_technical_notes',
        ];

        foreach ($routes as $route) {
            $urls[] = [
                'loc' => $this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'weekly',
                'priority' => ($route === 'app_home') ? '1.0' : '0.8',
            ];
        }

        $response = new Response(
            $this->renderView('seo/sitemap.xml.twig', [
                'urls' => $urls,
            ]),
            200
        );
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    #[Route('/robots.txt', name: 'app_robots', defaults: ['_format' => 'txt'])]
    public function robots(Request $request): Response
    {
        $response = new Response(
            $this->renderView('seo/robots.txt.twig', [
                'sitemap_url' => $this->generateUrl('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]),
            200
        );
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
}
