<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class FilterBooksByPublicationDateTest extends ApiTestCase
{
    // Always boot the Symfony kernel for consistent tests
    protected static ?bool $alwaysBootKernel = true;

    public function testFilterBooksByPublicationDate(): void
    {
        // Create an API client to send requests
        $client = static::createClient();


        // --------------------------------------------------------
        // 1) CREATE ONE AUTHOR (used by all our test books)
        // --------------------------------------------------------

        $authorResponse = $client->request('POST', '/api/authors', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'name' => 'Author Filter', // Used to identify test books later
            ],
        ]);

        // Assert author creation was successful
        $this->assertResponseStatusCodeSame(201);

        // Extract the IRI: e.g. "/api/authors/12"
        $authorIri = $authorResponse->toArray()['@id'];


        // --------------------------------------------------------
        // 2) CREATE MULTIPLE BOOKS WITH DIFFERENT PUBLICATION DATES
        // --------------------------------------------------------

        $dates = ['2022-01-01', '2023-05-10', '2025-11-25'];

        foreach ($dates as $date) {
            $client->request('POST', '/api/books', [
                'headers' => ['Content-Type' => 'application/ld+json'],
                'json'    => [
                    'title'           => 'Book ' . $date,             // Unique title for debugging
                    'publicationDate' => $date,             // Date used for filtering
                    'author'          => $authorIri,                 // Link to the created author
                ],
            ]);

            // Ensure each book was created
            $this->assertResponseStatusCodeSame(201);
        }


        // --------------------------------------------------------
        // 3) FILTER BOOKS PUBLISHED AFTER 2023-05-09
        // --------------------------------------------------------

        // This uses API Platform's DateFilter syntax:
        //   publicationDate[after]=YYYY-MM-DD
        $response = $client->request('GET', '/api/books?publicationDate[after]=2023-05-09', [
            'headers' => ['Accept' => 'application/ld+json'],
        ]);

        // Ensure API returned HTTP 200 OK
        $this->assertResponseIsSuccessful();

        // Convert JSON-LD/Hydra response to an array
        $data = $response->toArray();


        // --------------------------------------------------------
        // 4) ASSERTIONS ON FILTERED RESULTS
        // --------------------------------------------------------

        // There must be at least ONE book returned
        $this->assertGreaterThan(0, $data['totalItems']);

        // Expected normalized dates (API Platform converts dates to full ISO format)
        $expectedDates = [
            '2023-05-10T00:00:00+00:00',
            '2025-11-25T00:00:00+00:00',
        ];

        // Iterate over returned books
        foreach ($data['member'] as $book) {

            // Filter only books that belong to the author created in this test
            if (isset($book['author']['name']) && 'Author Filter' === $book['author']['name']) {

                // Ensure the book’s publicationDate is one of the expected values
                $this->assertContains($book['publicationDate'], $expectedDates);
            }
        }
    }
}
