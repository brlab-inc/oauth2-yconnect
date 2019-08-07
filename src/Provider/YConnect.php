<?php
/**
 * Created by PhpStorm.
 * User: polidog
 * Date: 2016/11/09
 */

namespace BRlab\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use BRlab\OAuth2\Client\Provider\Exception\YConnectIdentityProviderException;

class YConnect extends AbstractProvider
{
    const API_DOMAIN = 'https://auth.login.yahoo.co.jp';

    protected $_openidConfiguration;

    public $version = 'v2';

    /**
     * YConnect constructor.
     *
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (isset($options['version'])) {
            $this->version = $options['version'];
        }
    }

    /**
     * @return array
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function discovery()
    {
        if (!$this->_openidConfiguration) {
            $method = self::METHOD_GET;
            $url = $this->getApiBaseUrl() . '/.well-known/openid-configuration';
            $options = [];
            $request = $this->getRequest($method, $url, $options);
            $this->_openidConfiguration = $this->getParsedResponse($request);
        }
        return $this->_openidConfiguration;
    }

    /**
     * @return string
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function getBaseAuthorizationUrl()
    {
        $config = $this->discovery();
        return $config['authorization_endpoint'];
    }

    /**
     * @param mixed|null $token
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        if ($token != null) {
            return ['Authorization' => 'Bearer ' . $token];
        }
        return [];
    }

    /**
     * @param array $params
     * @return string
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        $config = $this->discovery();
        return $config['token_endpoint'];
    }

    /**
     * @param array $params
     * @return mixed
     */
    protected function getAccessTokenOptions(array $params)
    {
        $options = parent::getAccessTokenOptions([
            'code' => $params['code'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => $params['redirect_uri'],
        ]);

        $options['headers']['Authorization'] = 'Basic ' . base64_encode($params['client_id'] . ':' . $params['client_secret']);
        return $options;
    }

    /**
     * @param AccessToken $token
     * @return string
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $config = $this->discovery();
        return $config['userinfo_endpoint'] . '';
    }

    /**
     * @param AccessToken $token
     * @return \BRlab\OAuth2\Client\Provider\YConnectResourceOwner
     */
    public function getResourceOwner(AccessToken $token)
    {
        return parent::getResourceOwner($token);
    }

    /**
     * @param string $method
     * @param string $url
     * @param \League\OAuth2\Client\Token\AccessTokenInterface|string $token
     * @param array $options
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getAuthenticatedRequest($method, $url, $token, array $options = [])
    {
        return $this->createRequest($method, $url, $token, $options);
    }

    /**
     * @return array
     */
    protected function getDefaultScopes()
    {
        $config = $this->discovery();
        return array(implode(' ', $config['scopes_supported']));
//        return [
//            'openid profile',
////            'profile',
//        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw YConnectIdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw YConnectIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * @param array $response
     * @param AccessToken $token
     * @return YConnectResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new YConnectResourceOwner($response);
    }

    /**
     * @return string
     */
    protected function getApiBaseUrl()
    {
        return static::API_DOMAIN . "/yconnect/" . $this->version;
    }

}
