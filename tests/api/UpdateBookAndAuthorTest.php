<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class UpdateBookAndAuthorTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private const JSON_HEADER = ['Content-Type' => 'application/ld+json'];
    private const PATCH_HEADER = ['Content-Type' => 'application/merge-patch+json'];

    private function createResource($client, string $uri, array $data): string
    {
        $response = $client->request('POST', $uri, [
            'headers' => self::JSON_HEADER,
            'json' => $data,
        ]);

        $this->assertResponseStatusCodeSame(201);

        return $response->toArray()['@id'];
    }

    public function testUpdateBookAndAuthor(): void
    {
        $client = static::createClient();
        $authorIri = $this->createResource($client, '/api/authors', [
            'name' => 'Mohammed Choukri',
        ]);

        $bookIri = $this->createResource($client, '/api/books', [
            'title'           => 'Jane Book',
            'publicationDate' => '2025-11-25',
            'author'          => $authorIri,
        ]);

        $updatedBookResponse = $client->request('PATCH', $bookIri, [
            'headers' => self::PATCH_HEADER,
            'json'    => [
                'title'           => 'Jane Book Updated',
                'publicationDate' => '2025-12-01',
                'author'          => $authorIri,
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $updatedBookData = $updatedBookResponse->toArray();
        $this->assertEquals('Jane Book Updated', $updatedBookData['title']);
        $this->assertEquals('2025-12-01T00:00:00+00:00', $updatedBookData['publicationDate']);

        $updatedAuthorResponse = $client->request('PATCH', $authorIri, [
            'headers' => self::PATCH_HEADER,
            'json'    => [
                'name' => 'Mohammed Choukri Updated',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertEquals(
            'Mohammed Choukri Updated',
            $updatedAuthorResponse->toArray()['name']
        );
    }
}
