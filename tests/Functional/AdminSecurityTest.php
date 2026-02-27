<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminSecurityTest extends WebTestCase
{
    public function testAdminAccessBlockedByIp(): void
    {
        // IP not in whitelist (127.0.0.1, ::1)
        $client = static::createClient([], ['REMOTE_ADDR' => '1.2.3.4']);
        $client->request('GET', '/admin/dashboard');

        // It should be 404 Not Found for discrete blocking
        $this->assertResponseStatusCodeSame(404);
    }

    public function testAdminAccessAllowedByIpButRequiresLogin(): void
    {
        // IP in whitelist
        $client = static::createClient([], ['REMOTE_ADDR' => '127.0.0.1']);
        $client->request('GET', '/admin/dashboard');

        // It should redirect to login (302) or be 401 if it's an API, 
        // but here it's a web route so redirect to /admin/login is expected
        $this->assertResponseRedirects('/login');
    }
}
