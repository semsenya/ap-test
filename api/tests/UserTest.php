<?php


namespace App\Tests;


use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class UserTest extends CustomApiTestCase
{
    use RefreshDatabaseTrait;

    public function testCreateUser(): void
    {
        $client = self::createClient();

        $client->request('POST', '/users', [
            'json' => [
                'email' => 'test@example.com',
                'password' => 'test'
            ]
        ]);
        $this->assertResponseStatusCodeSame(201);
    }

    public function testLogin(): void
    {
        $client = self::createClient();
        $this->createUser('test@example.com', 'test');
        $token = $this->getToken($client, 'test@example.com', 'test');
    }
}
