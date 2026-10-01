<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Fixtures;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Подмена PSR-18 клиента для MAX API.
 *
 * Сервисы laravel-max-client (MaxChatProfileService, MaxUserProfileService)
 * объявлены final, поэтому подменить их нельзя — подменяется транспорт, и
 * проверяется настоящий путь: запрос ApiClient, запись в реестр, чтение записи
 * страницей Filament.
 */
final class MockHttpClient implements ClientInterface
{
    public int $callCount = 0;

    public ?RequestInterface $lastRequest = null;

    /** @var list<RequestInterface> */
    public array $requests = [];

    /** @var list<ResponseInterface> */
    private array $responses = [];

    private int $index = 0;

    /**
     * @param list<ResponseInterface> $responses
     */
    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    public function queue(ResponseInterface $response): void
    {
        $this->responses[] = $response;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;
        $this->requests[] = $request;
        ++$this->callCount;

        $response = $this->responses[$this->index] ?? null;

        if (!$response instanceof ResponseInterface) {
            return new Response(200, [], '{}');
        }

        ++$this->index;

        return $response;
    }
}
