<?php

namespace App\Services\Traccar;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TraccarClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $email,
        protected string $password
    ) {
        $this->baseUrl = rtrim($this->baseUrl, '/');
    }

    public function get(string $endpoint, array $query = []): array
    {
        $response = Http::withBasicAuth(
            $this->email,
            $this->password
        )
            ->acceptJson()
            ->timeout(15)
            ->get(
                $this->baseUrl . '/' . ltrim($endpoint, '/'),
                $query
            );

        if (! $response->successful()) {
            throw new RuntimeException(
                'Erro Traccar HTTP ' . $response->status()
            );
        }

        return $response->json() ?? [];
    }

    public function devices(): array
    {
        return $this->get('/api/devices');
    }

    public function positions(): array
    {
        return $this->get('/api/positions');
    }
}