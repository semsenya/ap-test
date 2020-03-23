<?php


namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class CustomApiTestCase extends ApiTestCase
{
    protected function createUser(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $encoded = self::$container->get('security.password_encoder')
            ->encodePassword($user, $password);
        $user->setPassword($encoded);
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
        return $user;
    }

    protected function getToken(Client $client, string $email, string $password) :string
    {
        $response = $client->request('POST', '/authentication_token', [
            'json' => [
                'email' => $email,
                'password' => $password
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
        return $data['token'];
    }

    protected function createUserAndGetToken(Client $client, string $email, string $password): string
    {
        $this->createUser($email, $password);
        return $this->getToken($client, $email, $password);
    }

    protected function getEntityManager()
    {
        return self::$container->get(EntityManagerInterface::class);
    }

    protected function loginUser1()
    {
        return $this->login('user1@example.com');
    }

    protected function loginUser2()
    {
        return $this->login('user2@example.com');
    }

    private function login(string $email): string
    {
        $response = static::createClient()->request('POST', '/authentication_token', ['json' => [
            'email' => $email,
            'password' => 'test',
        ]]);

        return $response->toArray()['token'];
    }
}
