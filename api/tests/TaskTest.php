<?php


namespace App\Tests;


use App\Entity\Task;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class TaskTest extends CustomApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(401);

        $token = $this->getToken($client, 'user1@example.com', 'test');
        $response = $client->request('GET', '/tasks', [
            'auth_bearer' => $token
        ]);

        $this->assertResponseIsSuccessful();
        // Asserts that the returned content type is JSON-LD (the default)
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Asserts that the returned JSON is a superset of this one
        $this->assertJsonContains([
            '@context' => '/contexts/Task',
            '@id' => '/tasks',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 10, // user can view only own tasks
            'hydra:view' => [
                '@id' => '/tasks?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/tasks?page=1',
                'hydra:last' => '/tasks?page=2',
                'hydra:next' => '/tasks?page=2',
            ],
        ]);

        // Because test fixtures are automatically loaded between each test, you can assert on them
        $this->assertCount(5, $response->toArray()['hydra:member']);

        // Asserts that the returned JSON is validated by the JSON Schema generated for this resource by API Platform
        // This generated JSON Schema is also used in the OpenAPI spec!
        $this->assertMatchesResourceCollectionJsonSchema(Task::class);
    }

    public function testCreateTask(): void
    {
        $client = self::createClient();
        $client->request('POST', '/tasks', [
            'json' => [],
        ]);
        $this->assertResponseStatusCodeSame(401);


        $token = $this->getToken($client, 'user1@example.com', 'test');
        $response =  $client->request('POST', '/tasks', [
            'auth_bearer' => $token,
            'json' => [
                'name' => 'test',
                'description' => 'test test',
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);
        $data = $response->toArray();
        $this->assertArrayHasKey('@id', $data);
    }

    public function testUpdateTask()
    {
        $client = self::createClient();
        $em = $this->getEntityManager();
        $user1 = $em->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $iri = static::findIriBy(Task::class, ['name' => 'task_7']);


        $client->request('PUT', $iri, [
            'json' => [
                'completed' => true
            ],
        ]);
        $this->assertResponseStatusCodeSame(401);

        $token = $this->getToken($client, 'user1@example.com', 'test');
        $client = static::createClient();
        $client->request('PUT', $iri, [
            'auth_bearer' => $token,
            'json' => [
                'completed' => true
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $token2 = $this->getToken($client, 'user2@example.com', 'test');
        $client = static::createClient();
        $client->request('PUT', $iri, [
            'auth_bearer' => $token2,
            'json' => [
                'completed' => true
            ],
        ]);
        $this->assertResponseStatusCodeSame(404);

    }
}
