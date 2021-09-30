<?php

namespace Raidkeeper\Api\Battlenet;

use Illuminate\Support\Facades\Cache;

class Token
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $region;
    protected string $accessToken;
    protected string $tokenExpiration;

    /**
     * @var Error|null
     */
    protected $error;

    public function __construct(string $region, string $clientId, string $clientSecret)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->region       = $region;
        $this->refresh();
    }

    protected function refresh(): void
    {
        $cache = Cache::get('rk:battlenet:'.$this->region.':token:expiration');

        if ($cache == null || $cache <= (time() - 60)) {
            $this->handshake();
        }
        $this->tokenExpiration = Cache::get('rk:battlenet:'.$this->region.':token:expiration');
        $this->accessToken     = Cache::get('rk:battlenet:'.$this->region.':token');
    }

    protected function handshake(): void
    {
        $url = 'https://'.$this->region.'.battle.net/oauth/token';
        $headers = [
            'User-Agent: RaidKeeper <gitlab.com/raidkeeper/raidkeeper>',
        ];

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL            => $url,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POST           => 1,
                CURLOPT_POSTFIELDS     => ['grant_type'=>'client_credentials'],
                CURLOPT_USERPWD        => $this->clientId . ":" . $this->clientSecret,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_SSL_VERIFYPEER => 0,
            ),
        );

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($status == 200 && is_string($response)) {
            $data = json_decode($response);
            $token = $data->access_token;
            $expiration = time() + $data->expires_in;
            Cache::put('rk:battlenet:'.$this->region.':token:expiration', $expiration, now()->addHours(23));
            Cache::put('rk:battlenet:'.$this->region.':token', $token, now()->addHours(23));
            $this->error = null;
        } else {
            $this->error = new Error('Unable to refresh client access token', $status);
        }
    }

    public function getToken(): string
    {
        return $this->accessToken;
    }

    public function getError(): Error|null
    {
        return $this->error;
    }

    public function hasError(): bool
    {
        return $this->error === null ? false : true;
    }
}
