<?php

class QPayApi
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private ?string $token = null;
    private int $tokenExpiry = 0;

    public function __construct()
    {
        $this->baseUrl = rtrim(Configuration::get('QPAY_BASE_URL') ?: 'https://merchant.qpay.mn', '/');
        $this->username = Configuration::get('QPAY_USERNAME') ?: '';
        $this->password = Configuration::get('QPAY_PASSWORD') ?: '';
    }

    private function getToken(): string
    {
        if ($this->token && time() < $this->tokenExpiry) return $this->token;

        $ch = curl_init($this->baseUrl . '/v2/auth/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password),
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($body, true);
        $this->token = $data['access_token'] ?? '';
        $this->tokenExpiry = time() + ($data['expires_in'] ?? 3600) - 30;
        return $this->token;
    }

    private function request(string $method, string $endpoint, array $body = []): ?array
    {
        $token = $this->getToken();
        $ch = curl_init($this->baseUrl . $endpoint);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
        ];
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function createInvoice(array $data): ?array
    {
        return $this->request('POST', '/v2/invoice', $data);
    }

    public function checkPayment(string $invoiceId): ?array
    {
        return $this->request('POST', '/v2/payment/check', [
            'object_type' => 'INVOICE',
            'object_id' => $invoiceId,
        ]);
    }
}
