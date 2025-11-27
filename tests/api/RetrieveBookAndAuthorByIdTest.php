<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class RetrieveBookAndAuthorByIdTest extends ApiTestCase
{
    // Ensures the Symfony Kernel is always booted between tests
    protected static ?bool $alwaysBootKernel = true;

    public function testRetrieveBookAndAuthorById(): void
    {
        // Create an API client to send HTTP requests
        $client = static::createClient();


        // --------------------------------------------------------
        // 1) CREATE AN AUTHOR
        // --------------------------------------------------------

        $authorResponse = $client->request('POST', '/api/authors', [
            'headers' => ['Content-Type' => 'application/ld+json'], // JSON-LD
            'json'    => [
                'name' => 'Mohammed Choukri', // Only required property
            ],
        ]);

        // Assert the Author was correctly created
        $this->assertResponseStatusCodeSame(201);

        // Convert the JSON-LD response into an associative array
        $authorData = $authorResponse->toArray();

        // Extract the Author's unique IRI (ex: "/api/authors/3")
        $authorIri = $authorData['@id'];


        // --------------------------------------------------------
        // 2) CREATE A BOOK LINKED TO THIS AUTHOR
        // --------------------------------------------------------

        $bookResponse = $client->request('POST', '/api/books', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'title'           => 'Jane Book',
                'publicationDate' => '2025-11-25',
                'author'          => $authorIri,   // Link using the IRI
            ],
        ]);

        // Ensure the Book creation was successful
        $this->assertResponseStatusCodeSame(201);

        // Convert JSON response to array
        $bookData = $bookResponse->toArray();

        // Extract the Book's unique IRI
        $bookIri = $bookData['@id'];


        // --------------------------------------------------------
        // 3) RETRIEVE THE BOOK BY IRI
        // --------------------------------------------------------

        $getBookResponse = $client->request('GET', $bookIri, [
            'headers' => ['Accept' => 'application/ld+json'],
        ]);

        // GET request should return HTTP 200
        $this->assertResponseIsSuccessful();

        // Convert book JSON-LD to array
        $getBookData = $getBookResponse->toArray();

        // Validate retrieved title
        $this->assertEquals('Jane Book', $getBookData['title']);

        // Validate the embedded author's IRI
        $this->assertEquals($authorIri, $getBookData['author']['@id']);


        // --------------------------------------------------------
        // 4) RETRIEVE THE AUTHOR BY IRI
        // --------------------------------------------------------

        $getAuthorResponse = $client->request('GET', $authorIri, [
            'headers' => ['Accept' => 'application/ld+json'],
        ]);

        // GET request should return HTTP 200 OK
        $this->assertResponseIsSuccessful();

        // Convert response to array
        $getAuthorData = $getAuthorResponse->toArray();

        // Validate the retrieved author name
        $this->assertEquals('Mohammed Choukri', $getAuthorData['name']);
    }
}
