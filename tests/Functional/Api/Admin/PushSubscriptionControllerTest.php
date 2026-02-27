<?php

namespace App\Tests\Functional\Api\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PushSubscriptionControllerTest extends WebTestCase
{
    public function testSubscribeRequiresAdmin(): void
    {
        $client = static::createClient([], ['REMOTE_ADDR' => '127.0.0.1']);
        $client->request('POST', '/api/admin/push-subscription');
        $this->assertResponseStatusCodeSame(302);
    }

    public function testSubscribeSuccess(): void
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

        $client->request('POST', '/api/admin/push-subscription', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-token',
            'keys' => [
                'p256dh' => 'BIP...' ,
                'auth' => 'AUTH...'
            ]
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Abonnement enregistré.', $data['message']);
    }
}
