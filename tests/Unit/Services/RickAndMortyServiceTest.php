<?php

namespace Tests\Unit\Services;

use App\Services\RickAndMortyService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RickAndMortyServiceTest extends TestCase
{
    private RickAndMortyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RickAndMortyService::class);
    }

    // --- getCharacters ---

    public function test_get_characters_returns_results_and_info(): void
    {
        Http::fake(['*/character*' => Http::response($this->characterListResponse())]);

        $result = $this->service->getCharacters();

        $this->assertArrayHasKey('info', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(2, $result['results']);
        $this->assertEquals(826, $result['info']['count']);
    }

    public function test_get_characters_passes_filter_params(): void
    {
        Http::fake(['*/character*' => Http::response($this->characterListResponse())]);

        $this->service->getCharacters(['name' => 'Rick', 'status' => 'Alive']);

        Http::assertSent(fn ($request) =>
            str_contains($request->url(), 'name=Rick') &&
            str_contains($request->url(), 'status=Alive')
        );
    }

    public function test_get_characters_returns_empty_array_on_api_error(): void
    {
        Http::fake(['*/character*' => Http::response([], 500)]);

        $result = $this->service->getCharacters();

        $this->assertEmpty($result);
    }

    public function test_get_characters_caches_results(): void
    {
        Http::fake(['*/character*' => Http::response($this->characterListResponse())]);

        $this->service->getCharacters(['page' => 1]);
        $this->service->getCharacters(['page' => 1]);

        Http::assertSentCount(1);
    }

    // --- getCharacter ---

    public function test_get_character_returns_single_character(): void
    {
        Http::fake(['*/character/1' => Http::response($this->singleCharacterResponse())]);

        $result = $this->service->getCharacter(1);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Rick Sanchez', $result['name']);
        $this->assertEquals('Alive', $result['status']);
    }

    public function test_get_character_returns_empty_array_on_404(): void
    {
        Http::fake(['*/character/999' => Http::response(['error' => 'Character not found'], 404)]);

        $result = $this->service->getCharacter(999);

        $this->assertEmpty($result);
    }

    public function test_get_character_caches_result(): void
    {
        Http::fake(['*/character/1' => Http::response($this->singleCharacterResponse())]);

        $this->service->getCharacter(1);
        $this->service->getCharacter(1);

        Http::assertSentCount(1);
    }

    // --- error handling ---

    public function test_returns_empty_array_on_connection_exception(): void
    {
        Http::fake([
            '*/character*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection refused'),
        ]);

        $result = $this->service->getCharacters();

        $this->assertEmpty($result);
    }

    // --- fixtures ---

    private function characterListResponse(): array
    {
        return [
            'info'    => ['count' => 826, 'pages' => 42, 'next' => 'https://rickandmortyapi.com/api/character?page=2', 'prev' => null],
            'results' => [
                $this->singleCharacterResponse(),
                array_merge($this->singleCharacterResponse(), ['id' => 2, 'name' => 'Morty Smith']),
            ],
        ];
    }

    private function singleCharacterResponse(): array
    {
        return [
            'id'       => 1,
            'name'     => 'Rick Sanchez',
            'status'   => 'Alive',
            'species'  => 'Human',
            'type'     => '',
            'gender'   => 'Male',
            'origin'   => ['name' => 'Earth (C-137)', 'url' => 'https://rickandmortyapi.com/api/location/1'],
            'location' => ['name' => 'Citadel of Ricks', 'url' => 'https://rickandmortyapi.com/api/location/3'],
            'image'    => 'https://rickandmortyapi.com/api/character/avatar/1.jpeg',
            'episode'  => ['https://rickandmortyapi.com/api/episode/1', 'https://rickandmortyapi.com/api/episode/2'],
            'url'      => 'https://rickandmortyapi.com/api/character/1',
            'created'  => '2017-11-04T18:48:46.250Z',
        ];
    }
}
