<?php

/**
 * OpenID OAuth2 client
 *
 * @package   OAuth2-OpenID
 * @author    Manuele Vaccari <manuele.vaccari@gmail.com>
 * @copyright Copyright (c) 2017-2020 Manuele Vaccari <manuele.vaccari@gmail.com>
 * @license   https://github.com/D3strukt0r/oauth2-openid/blob/master/LICENSE.txt GNU General Public License v3.0
 * @link      https://github.com/D3strukt0r/oauth2-openid
 */

namespace D3strukt0r\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * The Class in which all user information will be stored.
 */
class OpenIDProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * Default host.
     *
     * @var string
     */
    protected $host = 'https://openid.manuele-vaccari.ch';

    /**
     * Gets host.
     *
     * @return string returns the host
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Sets host. Can be used for example when you testing the service-account in localhost.
     *
     * @param string $host The domain for accessing the user data
     *
     * @return $this returns the class itself, for doing multiple things at once
     */
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string returns the URL for authorization
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->host . '/oauth/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params Special parameters
     *
     * @return string returns the URL to retrieve the access token
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->host . '/oauth/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token The received access token from the server
     *
     * @return string returns the URL to retrieve the resources
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->host . '/oauth/resource';
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array returns the default scopes
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * Get the string used to separate scopes.
     *
     * @return string returns the separator required for the scopes
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Checks a provider response for errors.
     *
     * @param ResponseInterface $response The response from the server
     * @param array|string      $data     Parsed response data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        $errorMessage = null;
        if ($response->getStatusCode() >= 400) {
            $errorMessage = isset($data['message']) ? $data['message'] : $response->getReasonPhrase();
        } elseif (isset($data['error'])) {
            $errorMessage = isset($data['error']) ? $data['error'] : $response->getReasonPhrase();
        }

        if (null !== $errorMessage) {
            throw new IdentityProviderException($errorMessage, $response->getStatusCode(), $response->getBody());
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array       $response Response data from server
     * @param AccessToken $token    The used access token
     *
     * @return ResourceOwnerInterface returns the resources
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new OpenIDResourceOwner($response);
    }
}
