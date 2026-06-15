<?php

namespace Tests\Unit\Services;

use App\Exceptions\ApiConnectionException;
use App\Services\EpisodeService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EpisodeServiceTest extends TestCase
{
    private EpisodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EpisodeService::class);
    }

    // --- getEpisodes ---

    public function test_get_episodes_returns_results_and_info(): void
    {
        Http::fake(['*/episode*' => Http::response($this->episodeListResponse())]);

        $result = $this->service->getEpisodes();

        $this->assertArrayHasKey('info', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertCount(2, $result['results']);
        $this->assertEquals(51, $result['info']['count']);
    }

    public function test_get_episodes_passes_filter_params(): void
    {
        Http::fake(['*/episode*' => Http::response($this->episodeListResponse())]);

        $this->service->getEpisodes(['name' => 'Pilot', 'episode' => 'S01E01']);

        Http::assertSent(fn ($request) =>
            str_contains($request->url(), 'name=Pilot') &&
            str_contains($request->url(), 'episode=S01E01')
        );
    }

    public function test_get_episodes_throws_on_api_error(): void
    {
        Http::fake(['*/episode*' => Http::response([], 500)]);

        $this->expectException(ApiConnectionException::class);
        $this->service->getEpisodes();
    }

    public function test_get_episodes_returns_empty_on_no_results(): void
    {
        Http::fake(['*/episode*' => Http::response(['error' => 'There is nothing here'], 404)]);

        $result = $this->service->getEpisodes(['name' => 'zzznomatch']);

        $this->assertEmpty($result);
    }

    public function test_get_episodes_caches_results(): void
    {
        Http::fake(['*/episode*' => Http::response($this->episodeListResponse())]);

        $this->service->getEpisodes(['page' => 1]);
        $this->service->getEpisodes(['page' => 1]);

        Http::assertSentCount(1);
    }

    // --- getEpisode ---

    public function test_get_episode_returns_single_episode(): void
    {
        Http::fake(['*/episode/1' => Http::response($this->singleEpisodeResponse())]);

        $result = $this->service->getEpisode(1);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Pilot', $result['name']);
        $this->assertEquals('S01E01', $result['episode']);
    }

    public function test_get_episode_returns_empty_array_on_404(): void
    {
        Http::fake(['*/episode/999' => Http::response(['error' => 'Episode not found'], 404)]);

        $result = $this->service->getEpisode(999);

        $this->assertEmpty($result);
    }

    public function test_get_episode_caches_result(): void
    {
        Http::fake(['*/episode/1' => Http::response($this->singleEpisodeResponse())]);

        $this->service->getEpisode(1);
        $this->service->getEpisode(1);

        Http::assertSentCount(1);
    }

    // --- getMultipleEpisodes ---

    public function test_get_multiple_episodes_returns_array_for_multiple_ids(): void
    {
        Http::fake([
            '*/episode/1,2,3' => Http::response([
                ['id' => 1, 'name' => 'Pilot', 'episode' => 'S01E01'],
                ['id' => 2, 'name' => 'Lawnmower Dog', 'episode' => 'S01E02'],
                ['id' => 3, 'name' => 'Anatomy Park', 'episode' => 'S01E03'],
            ]),
        ]);

        $result = $this->service->getMultipleEpisodes([1, 2, 3]);

        $this->assertCount(3, $result);
        $this->assertEquals('Pilot', $result[0]['name']);
    }

    public function test_get_multiple_episodes_wraps_single_result_in_array(): void
    {
        Http::fake([
            '*/episode/1' => Http::response(['id' => 1, 'name' => 'Pilot', 'episode' => 'S01E01']),
        ]);

        $result = $this->service->getMultipleEpisodes([1]);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Pilot', $result[0]['name']);
    }

    public function test_get_multiple_episodes_returns_empty_array_for_no_ids(): void
    {
        $result = $this->service->getMultipleEpisodes([]);

        $this->assertEmpty($result);
        Http::assertNothingSent();
    }

    // --- error handling ---

    public function test_throws_api_connection_exception_on_connection_failure(): void
    {
        Http::fake([
            '*/episode*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection refused'),
        ]);

        $this->expectException(ApiConnectionException::class);
        $this->service->getEpisodes();
    }

    // --- fixtures ---

    private function episodeListResponse(): array
    {
        return [
            'info'    => ['count' => 51, 'pages' => 3, 'next' => 'https://rickandmortyapi.com/api/episode?page=2', 'prev' => null],
            'results' => [
                $this->singleEpisodeResponse(),
                array_merge($this->singleEpisodeResponse(), ['id' => 2, 'name' => 'Lawnmower Dog', 'episode' => 'S01E02']),
            ],
        ];
    }

    private function singleEpisodeResponse(): array
    {
        return [
            'id'         => 1,
            'name'       => 'Pilot',
            'air_date'   => 'December 2, 2013',
            'episode'    => 'S01E01',
            'characters' => [
                'https://rickandmortyapi.com/api/character/1',
                'https://rickandmortyapi.com/api/character/2',
            ],
            'url'        => 'https://rickandmortyapi.com/api/episode/1',
            'created'    => '2017-11-10T12:56:33.798Z',
        ];
    }
}
