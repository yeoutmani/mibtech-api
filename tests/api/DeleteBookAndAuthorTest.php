<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class DeleteBookAndAuthorTest extends ApiTestCase
{
    // Ensures the Symfony kernel is always booted for each test
    protected static ?bool $alwaysBootKernel = true;

    public function testDeleteBookAndAuthor(): void
    {
        // Create a client that will simulate API requests
        $client = static::createClient();

        // --------------------------------------------------------
        // 1) CREATE AN AUTHOR
        // --------------------------------------------------------

        $authorResponse = $client->request('POST', '/api/authors', [
            'headers' => ['Content-Type' => 'application/ld+json'], // JSON-LD request
            'json'    => [
                'name' => 'Mohammed Choukri', // Required field for Author
            ],
        ]);

        // Assert the Author was created successfully
        $this->assertResponseStatusCodeSame(201);

        // Convert JSON-LD response into a PHP array
        $authorData = $authorResponse->toArray();

        // Extract the author's IRI (e.g. "/api/authors/1")
        $authorIri = $authorData['@id'];


        // --------------------------------------------------------
        // 2) CREATE A BOOK LINKED TO THAT AUTHOR
        // --------------------------------------------------------

        $bookResponse = $client->request('POST', '/api/books', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'title'           => 'Jane Book',
                'publicationDate' => '2025-11-25',
                'author'          => $authorIri, // Link Book → Author using IRI
            ],
        ]);

        // Assert that the Book was successfully created
        $this->assertResponseStatusCodeSame(201);

        // Convert JSON response to array
        $bookData = $bookResponse->toArray();

        // Extract the book's IRI (e.g. "/api/books/1")
        $bookIri = $bookData['@id'];


        // --------------------------------------------------------
        // 3) DELETE THE BOOK
        // --------------------------------------------------------

        // Send a DELETE request to the Book's IRI
        $client->request('DELETE', $bookIri);

        // A successful deletion should return HTTP 204
        $this->assertResponseStatusCodeSame(204);


        // --------------------------------------------------------
        // 4) VERIFY THAT THE BOOK NO LONGER EXISTS
        // --------------------------------------------------------

        // Attempt to GET the deleted Book
        $client->request('GET', $bookIri);

        // Expected: 404 Not Found
        $this->assertResponseStatusCodeSame(404);


        // --------------------------------------------------------
        // 5) DELETE THE AUTHOR
        // --------------------------------------------------------

        // Now delete the Author
        $client->request('DELETE', $authorIri);

        // The API should return HTTP 204
        $this->assertResponseStatusCodeSame(204);


        // --------------------------------------------------------
        // 6) VERIFY THAT THE AUTHOR NO LONGER EXISTS
        // --------------------------------------------------------

        // Attempt to GET the deleted Author
        $client->request('GET', $authorIri);

        // Expected: 404 Not Found
        $this->assertResponseStatusCodeSame(404);
    }
}
