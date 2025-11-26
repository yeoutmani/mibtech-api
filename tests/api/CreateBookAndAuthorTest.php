<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class CreateBookAndAuthorTest extends ApiTestCase
{
    // Ensures the Symfony kernel is always booted for each test
    protected static ?bool $alwaysBootKernel = true;

    public function testCreateBookAndAuthor(): void
    {
        // Create a client that will simulate API requests
        $client = static::createClient();

        // --------------------------------------------------------
        // 1) CREATE AN AUTHOR
        // --------------------------------------------------------

        $authorResponse = $client->request('POST', '/api/authors', [
            'headers' => ['Content-Type' => 'application/ld+json'], // JSON-LD request
            'json'    => [
                'name' => 'Mohammed Choukri', // Only required field in Author
            ],
        ]);

        // Assert that the Author was successfully created
        $this->assertResponseStatusCodeSame(201);

        // Convert JSON response to array
        $authorData = $authorResponse->toArray();

        // Extract the IRI (ex: "/api/authors/1")
        $authorIri = $authorData['@id'];

        // --------------------------------------------------------
        // 2) CREATE A BOOK AND LINK IT TO THE AUTHOR
        // --------------------------------------------------------

        $bookResponse = $client->request('POST', '/api/books', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'title'           => 'My Book',
                'publicationDate' => '2023-11-01',
                'author'          => $authorIri, // Link to the created Author
            ],
        ]);

        // The Book must also be created successfully
        $this->assertResponseStatusCodeSame(201);

        // Convert API response to array
        $bookData = $bookResponse->toArray();

        // --------------------------------------------------------
        // 3) ASSERTIONS
        // --------------------------------------------------------

        // Validate the returned book title
        $this->assertEquals('My Book', $bookData['title']);

        // Validate the normalized publication date
        // API Platform converts "2023-11-01" → "2023-11-01T00:00:00+00:00"
        $this->assertEquals('2023-11-01T00:00:00+00:00', $bookData['publicationDate']);

        // Ensure the Book response contains the correct author IRI
        // bookData['author'] is an object containing @id, name, etc.
        $this->assertEquals($authorIri, $bookData['author']['@id']);
    }
}
