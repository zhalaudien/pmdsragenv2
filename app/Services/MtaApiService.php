<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MtaApiService
{
    protected string $baseUrl;
    protected string $apiToken;
    protected int $timeout;
    protected bool $enabled;
    protected string $perwakilanUuid;
    protected ?array $lastRateLimit = null;

    public function __construct()
    {
        $this->baseUrl        = config('mta.baseUrl', 'https://api.mta.or.id/api/v1');
        $this->apiToken       = config('mta.apiToken', '');
        $this->timeout        = (int) config('mta.timeout', 15);
        $this->enabled        = (bool) config('mta.enabled', true);
        $this->perwakilanUuid = config('mta.perwakilanUuid', '0190a61a-053a-73d7-8495-2fe9b50ae338');
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiToken);
    }

    public function getLastRateLimit(): ?array
    {
        return $this->lastRateLimit;
    }

    public function getSragenUuid(): string
    {
        return $this->perwakilanUuid;
    }

    /**
     * Kirim HTTP Request ke API MTA
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Integrasi API MTA sedang dinonaktifkan di konfigurasi.',
                'code'    => 503,
            ];
        }

        $url = str_starts_with($endpoint, 'http')
            ? $endpoint
            : (rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/'));

        try {
            $client = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept'      => 'application/json',
                    'X-API-Token' => $this->apiToken,
                    'User-Agent'  => 'Pemuda-MTA-Sragen/2.0-Laravel',
                ]);

            if (isset($options['headers'])) {
                $client->withHeaders($options['headers']);
            }

            $query = $options['query'] ?? [];

            $response = match (strtoupper($method)) {
                'GET'  => $client->get($url, $query),
                'POST' => $client->post($url, $options['body'] ?? $query),
                default => $client->send($method, $url, $options),
            };

            $statusCode = $response->status();
            $body = $response->body();

            if ($response->hasHeader('X-RateLimit-Limit') || $response->hasHeader('X-RateLimit-Remaining')) {
                $this->lastRateLimit = [
                    'limit'     => $response->header('X-RateLimit-Limit'),
                    'remaining' => $response->header('X-RateLimit-Remaining'),
                ];
            }

            $jsonData = $response->json();

            if ($jsonData === null && !empty($body)) {
                return [
                    'success'    => false,
                    'message'    => 'Format response dari server MTA bukan JSON yang valid (HTTP ' . $statusCode . ').',
                    'raw'        => substr($body, 0, 500),
                    'statusCode' => $statusCode,
                ];
            }

            $jsonData = $jsonData ?? [];
            $jsonData['statusCode'] = $statusCode;

            if ($statusCode === 401) {
                $jsonData['message'] = $jsonData['message'] ?? 'Token API MTA tidak valid atau belum diatur.';
                $jsonData['success'] = false;
            } elseif ($statusCode === 403) {
                $jsonData['message'] = $jsonData['message'] ?? 'Akses ditolak: IP tidak diizinkan atau izin akses belum diberikan.';
                $jsonData['success'] = false;
            } elseif ($statusCode === 429) {
                $jsonData['message'] = $jsonData['message'] ?? 'Batas kuota request tercapai (Rate limit 60 req/menit).';
                $jsonData['success'] = false;
            }

            return $jsonData;
        } catch (\Throwable $e) {
            Log::error('[MtaApiService] Request Error: ' . $e->getMessage());
            return [
                'success'    => false,
                'message'    => 'Koneksi ke API MTA gagal: ' . $e->getMessage(),
                'statusCode' => 500,
            ];
        }
    }

    public function testConnection(): array
    {
        $res = $this->getStatistik();
        return [
            'connected'     => ($res['success'] ?? false) === true,
            'statusCode'    => $res['statusCode'] ?? 500,
            'message'       => $res['message'] ?? (($res['success'] ?? false) ? 'Koneksi API MTA Terhubung Normal' : 'Gagal terhubung'),
            'rateLimit'     => $this->lastRateLimit,
            'data'          => $res['data'] ?? null,
            'baseUrl'       => $this->baseUrl,
            'tokenMasked'   => substr($this->apiToken, 0, 6) . '...' . substr($this->apiToken, -4),
        ];
    }

    public function getStatistik(): array
    {
        return $this->request('GET', 'statistik');
    }

    public function getPerwakilanList(?string $search = null, int $limit = 50): array
    {
        $query = [];
        if (!empty($search)) {
            $query['search'] = $search;
        }
        if ($limit > 0) {
            $query['limit'] = min($limit, 200);
        }

        return $this->request('GET', 'perwakilan', ['query' => $query]);
    }

    public function getPerwakilanDetail(string $uuid): array
    {
        return $this->request('GET', 'perwakilan/' . urlencode($uuid));
    }

    public function getCabangList(?string $perwakilanUuid = null, ?string $search = null, int $limit = 100): array
    {
        $query = [];
        if (!empty($perwakilanUuid)) {
            $query['perwakilan'] = $perwakilanUuid;
        }
        if (!empty($search)) {
            $query['search'] = $search;
        }
        if ($limit > 0) {
            $query['limit'] = min($limit, 200);
        }

        return $this->request('GET', 'cabang', ['query' => $query]);
    }

    public function getCabangDetail(string $uuid): array
    {
        return $this->request('GET', 'cabang/' . urlencode($uuid));
    }

    public function getCabangWarga(string $cabangUuid, int $page = 1, int $perPage = 25, ?string $gender = null): array
    {
        $query = [
            'page'     => max(1, $page),
            'per_page' => min(max(1, $perPage), 100),
        ];

        if (!empty($gender) && in_array(strtoupper($gender), ['L', 'P'], true)) {
            $query['kelamin'] = strtoupper($gender);
        }

        return $this->request('GET', 'cabang/' . urlencode($cabangUuid) . '/warga', ['query' => $query]);
    }

    public function getPerwakilanSragenDetail(): array
    {
        return $this->getPerwakilanDetail($this->getSragenUuid());
    }

    public function getCabangSragenList(): array
    {
        $detail = $this->getPerwakilanSragenDetail();
        if (($detail['success'] ?? false) && isset($detail['data']['cabang'])) {
            return [
                'success' => true,
                'data'    => $detail['data']['cabang'],
                'total'   => count($detail['data']['cabang']),
            ];
        }

        return $this->getCabangList($this->getSragenUuid(), null, 200);
    }

    public function getWargaList(array $params = []): array
    {
        $allowedParams = ['page', 'per_page', 'search', 'kelamin', 'status', 'perwakilan', 'cabang', 'order_by', 'direction'];
        $query = [];

        foreach ($allowedParams as $param) {
            if (isset($params[$param]) && $params[$param] !== '') {
                $query[$param] = $params[$param];
            }
        }

        if (!isset($query['perwakilan']) && !isset($query['cabang'])) {
            $query['perwakilan'] = $this->getSragenUuid();
        }

        return $this->request('GET', 'warga', ['query' => $query]);
    }

    public function searchWarga(string $query, array $params = []): array
    {
        $queryParams = [
            'q' => trim($query),
        ];

        if (!empty($params['limit'])) {
            $queryParams['limit'] = min(max(1, (int) $params['limit']), 50);
        }
        if (!empty($params['kelamin'])) {
            $queryParams['kelamin'] = $params['kelamin'];
        }
        if (!empty($params['cabang'])) {
            $queryParams['cabang'] = $params['cabang'];
        } elseif (!empty($params['cabang_uuid'])) {
            $queryParams['cabang'] = $params['cabang_uuid'];
        }

        $queryParams['perwakilan'] = !empty($params['perwakilan']) ? $params['perwakilan'] : $this->getSragenUuid();

        return $this->request('GET', 'warga/search', ['query' => $queryParams]);
    }

    public function getWargaDetail(string $uuid): array
    {
        return $this->request('GET', 'warga/' . urlencode($uuid));
    }
}
