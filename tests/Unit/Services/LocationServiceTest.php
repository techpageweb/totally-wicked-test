<?php

namespace Tests\Unit\Services;

use App\Services\RickAndMortyService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocationServiceTest extends TestCase
{
    private RickAndMortyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RickAndMortyService::class);
    }

    // --- getLocations ---

    public function test_get_locations_returns_results_and_info(): void
    {
        Http::fake(['*/location*' => Http::response($this->locationListResponse())]);

        $result = $this->service->getLocations();

        $this->assertArrayHasKey('info', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(2, $result['results']);
        $this->assertEquals(126, $result['info']['count']);
    }

    public function test_get_locations_passes_filter_params(): void
    {
        Http::fake(['*/location*' => Http::response($this->locationListResponse())]);

        $this->service->getLocations(['name' => 'Earth', 'type' => 'Planet', 'dimension' => 'Dimension C-137']);

        Http::assertSent(fn ($request) =>
            str_contains(urldecode($request->url()), 'name=Earth') &&
            str_contains(urldecode($request->url()), 'type=Planet') &&
            str_contains(urldecode($request->url()), 'dimension=Dimension C-137')
        );
    }

    public function test_get_locations_returns_empty_array_on_api_error(): void
    {
        Http::fake(['*/location*' => Http::response([], 500)]);

        $result = $this->service->getLocations();

        $this->assertEmpty($result);
    }

    public function test_get_locations_caches_results(): void
    {
        Http::fake(['*/location*' => Http::response($this->locationListResponse())]);

        $this->service->getLocations(['page' => 1]);
        $this->service->getLocations(['page' => 1]);

        Http::assertSentCount(1);
    }

    // --- getLocation ---

    public function test_get_location_returns_single_location(): void
    {
        Http::fake(['*/location/1' => Http::response($this->singleLocationResponse())]);

        $result = $this->service->getLocation(1);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Earth (C-137)', $result['name']);
        $this->assertEquals('Planet', $result['type']);
    }

    public function test_get_location_returns_empty_array_on_404(): void
    {
        Http::fake(['*/location/999' => Http::response(['error' => 'Location not found'], 404)]);

        $result = $this->service->getLocation(999);

        $this->assertEmpty($result);
    }

    public function test_get_location_caches_result(): void
    {
        Http::fake(['*/location/1' => Http::response($this->singleLocationResponse())]);

        $this->service->getLocation(1);
        $this->service->getLocation(1);

        Http::assertSentCount(1);
    }

    // --- fixtures ---

    private function locationListResponse(): array
    {
        return [
            'info'    => ['count' => 126, 'pages' => 7, 'next' => 'https://rickandmortyapi.com/api/location?page=2', 'prev' => null],
            'results' => [
                $this->singleLocationResponse(),
                array_merge($this->singleLocationResponse(), ['id' => 2, 'name' => 'Abadango', 'type' => 'Cluster']),
            ],
        ];
    }

    private function singleLocationResponse(): array
    {
        return [
            'id'        => 1,
            'name'      => 'Earth (C-137)',
            'type'      => 'Planet',
            'dimension' => 'Dimension C-137',
            'residents' => [
                'https://rickandmortyapi.com/api/character/38',
                'https://rickandmortyapi.com/api/character/45',
            ],
            'url'       => 'https://rickandmortyapi.com/api/location/1',
            'created'   => '2017-11-10T12:42:04.162Z',
        ];
    }
}
