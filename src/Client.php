<?php declare(strict_types=1);

namespace Raidkeeper\Api\Battlenet;

use Illuminate\Support\Facades\Cache;

class Client
{
    protected string $region;
    protected string $namespace;
    protected Token  $token;
    protected string $clientId;
    protected string $clientSecret;
    protected string $locale = 'en_US';
    public string $url;

    /**
     * @var array<string>
     */
    public array  $headers;

    /**
     * @var string
     */
    protected $oauthToken;

    public function __construct(string $region, string $clientId, string $clientSecret, string $oauthToken = '')
    {
        // Saving basic values for object
        $this->region       = $region;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->oauthToken   = $oauthToken;

        // Building constituent parameters prior to calls
        $this->headers   = array();
        $this->headers[] = 'User-Agent: Raidkeeper <gitlab.com/raidkeeper/raidkeeper>';

        // Adding Client token to object
        $this->token = new Token($this->region, $this->clientId, $this->clientSecret);
    }

    public function get(): \Error|ApiResponse
    {
        $cache = Cache::get('data_'.$this->url);
        if ($cache != null) {
            return new ApiResponse($cache, $this->getLocale());
        }

        $curl     = static::curl($this->url, $this->headers);
        $response = curl_exec($curl);
        $code     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $this->parseResponse($response, $code);
    }

    public function parseResponse(mixed $response, int $code): \Error|ApiResponse
    {
        if (! is_string($response)) {
            return new \Error('Api call has returned a non-valid response.', $code);
        }

        $json = json_decode($response);
        if (isset($json->code) && isset($json->detail)) {
            return new \Error($json->detail, $json->code);
        }

        Cache::put('data_'.$this->url, $json, now()->addMinutes(55));
        return new ApiResponse($json, $this->getLocale());
    }

    /**
     * @param string $url
     * @param Array<string> $hdrs
     * @param Array<string> $postFields
     * @param string $usrPwd
     */
    public static function curl(
        string $url,
        array $hdrs,
        array $postFields = [],
        string $usrPwd = ''
    ): \CurlHandle {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $hdrs);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        if (!empty($postFields)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        }
        if ($usrPwd != '') {
            curl_setopt($curl, CURLOPT_USERPWD, $usrPwd);
        }
        return $curl;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function slugify(string $name): string
    {
        $name = str_replace(' ', '-', $name);
        $name = str_replace("'", '', $name);
        return mb_strtolower($name, 'UTF-8');
    }

    /**
     * API calling methods
     */

    public function fromUrl(string $url): void
    {
        $this->url = $url;
        $this->addClientTokenHeader();
    }

    public function fromEndpoint(string $namespace, string $endpoint): Client
    {
        $this->namespace = $namespace;
        $this->buildUrl($endpoint);
        $this->addBattlenetHeader();
        $this->addClientTokenHeader();
        return $this;
    }

    public function fromOAuthEndpoint(string $namespace, string $endpoint): void
    {
        $this->namespace = $namespace;
        $this->buildUrl($endpoint);
        $this->addBattlenetHeader();
        $this->addOAuthHeader();
    }

    protected function buildUrl(string $endpoint): void
    {
        $this->url = 'https://'.$this->region.'.api.blizzard.com/'.$this->namespace.'/'.$endpoint;
    }

    /**
     * Header client methods
     */

    protected function addBattlenetHeader(): void
    {
        $this->headers[] = 'Battlenet-Namespace: '.$this->namespace.'-'.$this->region;
    }

    protected function addClientTokenHeader(): void
    {
        $this->addTokenHeader($this->token->getToken());
    }

    protected function addOAuthHeader(): void
    {
        $this->addTokenHeader($this->oauthToken);
    }

    protected function addTokenHeader(string $tokenString): void
    {
        $this->headers[] = 'Authorization: Bearer '.$tokenString;
    }

    /**
     * Child accessor methods
     */
    public function loadCharacter(string $name, string $realm): Character
    {
        return new Character($name, $this->slugify($realm), $this);
    }
}
