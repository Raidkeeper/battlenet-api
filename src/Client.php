<?php declare(strict_types=1);

namespace Raidkeeper\Api\Battlenet;

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

    public function get(): Error|\stdClass
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        $code     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (! is_string($response)) {
            return new Error('API call has returned a non-valid response.', $code);
	} else {
	    $json = json_decode($response);
	    return isset($json->code) ? new Error($json->detail, $json->code) : $json;
        }
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
        return strtolower($name);
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
