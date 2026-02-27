<?php

namespace App\Tests\Functional\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnalyticsControllerTest extends WebTestCase
{
    public function testAnalyticsRequiresAdmin(): void
    {
        $client = static::createClient([], ['REMOTE_ADDR' => '127.0.0.1']);
        $client->request('GET', '/admin/analytics');
        $this->assertResponseStatusCodeSame(302);
    }

    public function testAnalyticsPageLoads(): void
    {
        $client = static::createClient([], ['REMOTE_ADDR' => '127.0.0.1']);
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');

        $admin = $em->getRepository(User::class)->findOneBy(['email' => 'admin@test.com']);
        if (!$admin) {
            $admin = new User();
            $admin->setEmail('admin@test.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $em->persist($admin);
            $em->flush();
        }
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/analytics');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'ANALYTICS_MODULE');
        // Check if charts are rendered
        $this->assertSelectorExists('canvas');
    }
}
