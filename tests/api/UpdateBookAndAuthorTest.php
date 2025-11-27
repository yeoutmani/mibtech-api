<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class UpdateBookAndAuthorTest extends ApiTestCase
{
    // Keep kernel booted across requests for consistency
    protected static ?bool $alwaysBootKernel = true;

    public function testUpdateBookAndAuthor(): void
    {
        // Create an API client
        $client = static::createClient();


        // --------------------------------------------------------
        // 1) CREATE AN AUTHOR
        // --------------------------------------------------------

        $authorResponse = $client->request('POST', '/api/authors', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'name' => 'Mohammed Choukri',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);

        // Extract the author IRI
        $authorData = $authorResponse->toArray();
        $authorIri  = $authorData['@id'];


        // --------------------------------------------------------
        // 2) CREATE A BOOK LINKED TO THAT AUTHOR
        // --------------------------------------------------------

        $bookResponse = $client->request('POST', '/api/books', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'title'           => 'Jane Book',
                'publicationDate' => '2025-11-25',
                'author'          => $authorIri,
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);

        // Extract book IRI
        $bookData = $bookResponse->toArray();
        $bookIri  = $bookData['@id'];


        // --------------------------------------------------------
        // 3) UPDATE THE BOOK (PATCH request)
        // --------------------------------------------------------

        $updatedBookResponse = $client->request('PATCH', $bookIri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json', // Required for PATCH
            ],
            'json' => [
                'title'           => 'Jane Book Updated',
                'publicationDate' => '2025-12-01',
                'author'          => $authorIri,
            ],
        ]);

        // PATCH must return 200 OK or 202 Accepted depending on config
        $this->assertResponseIsSuccessful();

        // Extract the updated book data
        $updatedBookData = $updatedBookResponse->toArray();

        $this->assertEquals('Jane Book Updated', $updatedBookData['title']);
        $this->assertEquals('2025-12-01T00:00:00+00:00', $updatedBookData['publicationDate']);


        // --------------------------------------------------------
        // 4) UPDATE THE AUTHOR
        // --------------------------------------------------------

        $updatedAuthorResponse = $client->request('PATCH', $authorIri, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json'    => [
                'name' => 'Mohammed Choukri Updated',
            ],
        ]);

        $this->assertResponseIsSuccessful();

        // Extract updated author data
        $updatedAuthorData = $updatedAuthorResponse->toArray();

        // Check that update was applied
        $this->assertEquals('Mohammed Choukri Updated', $updatedAuthorData['name']);
    }
}
