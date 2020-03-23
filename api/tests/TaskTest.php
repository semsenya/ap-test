<?php


namespace App\Tests;


use App\Entity\Task;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class TaskTest extends CustomApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(401);

        $client = static::createClient();
        $token = $this->login();
        $client = static::createClient();
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
            'hydra:totalItems' => 20,
            'hydra:view' => [
                '@id' => '/tasks?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/tasks?page=1',
                'hydra:last' => '/tasks?page=4',
                'hydra:next' => '/tasks?page=2',
            ],
        ]);

        // Because test fixtures are automatically loaded between each test, you can assert on them
        $this->assertCount(5, $response->toArray()['hydra:member']);

        // Asserts that the returned JSON is validated by the JSON Schema generated for this resource by API Platform
        // This generated JSON Schema is also used in the OpenAPI spec!
        $this->assertMatchesResourceCollectionJsonSchema(Task::class);
    }
}
