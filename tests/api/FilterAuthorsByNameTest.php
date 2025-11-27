<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class FilterAuthorsByNameTest extends ApiTestCase
{
    // Ensures Symfony’s kernel is always booted for consistent tests
    protected static ?bool $alwaysBootKernel = true;

    public function testFilterAuthorsByName(): void
    {
        // Create a client to simulate HTTP requests to the API
        $client = static::createClient();


        // --------------------------------------------------------
        // 1) CREATE MULTIPLE AUTHORS
        // --------------------------------------------------------

        // List of author names we will insert
        $names = ['Alice', 'Bob', 'Charlie'];

        foreach ($names as $name) {
            // Send a POST request to create each Author
            $client->request('POST', '/api/authors', [
                'headers' => ['Content-Type' => 'application/ld+json'],
                'json'    => [
                    'name' => $name, // Only required field
                ],
            ]);

            // Ensure each Author is successfully created
            $this->assertResponseStatusCodeSame(201);
        }


        // --------------------------------------------------------
        // 2) FILTER AUTHORS BY NAME "Bob"
        // --------------------------------------------------------

        // API Platform allows filtering using query parameters if the entity is configured with filters.
        $response = $client->request(
            'GET',
            '/api/authors?name=Bob',
            ['headers' => ['Accept' => 'application/ld+json']]
        );

        // Ensure the request completed successfully with 200 OK
        $this->assertResponseIsSuccessful();

        // Convert the Hydra response to array
        $data = $response->toArray();


        // --------------------------------------------------------
        // 3) ASSERTIONS ON FILTERED RESULTS
        // --------------------------------------------------------

        // Ensure at least one result was returned
        $this->assertGreaterThan(0, $data['totalItems']);

        // Ensure *every* returned author has exactly the name "Bob"
        foreach ($data['member'] as $author) {
            $this->assertEquals('Bob', $author['name']);
        }
    }
}
