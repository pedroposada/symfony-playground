<?php

namespace PSL\ClipperBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * WebTestCase partly taken from Acme Bundle and Liip.
 */
abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Client
     */
    protected $authenticatedClient;

    /**
     * @var string
     */
    protected $authorizationHeaderPrefix = 'Bearer';

    /**
     * @var array
     */
    protected $params;

    /**
     * @var appTestDebugProjectContainer
     */
    protected $container;

    /**
     * @var TraceableEventDispatcher
     */
    protected $dispatcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->loadFixtures(array(
            'PSL\ClipperBundle\DataFixtures\ORM\LoadFirstQGroups',
            'PSL\ClipperBundle\DataFixtures\ORM\LoadFirstQProjects',
        ));

        $this->client = static::makeClient();
        $this->container = $this->client->getContainer();
        $this->params = $this->container->getParameter('clipper');
        $this->dispatcher = $this->container->get('event_dispatcher');
        $this->authenticatedClient = static::createAuthenticatedClient('uuser', 'userpass');
    }

    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function createAuthenticatedClient($username = 'user', $password = 'password')
    {
        // We do not use static::makeClient(true) because
        // LiipFunctionalTestBundle do not support HTTP_Authorization. They
        // only have PHP_AUTH_USER and PHP_AUTH_PW header. Thus we make our
        // own JWT compatiable authenticated client.
        $client = static::makeClient();
        $client->request(
            'POST',
            $this->getUrl('api_login_admin_check'),
            array(
                'username' => $username,
                'password' => $password,
            )
        );

        $response = $client->getResponse();
        $data     = json_decode($response->getContent(), true);

        return static::makeClient(
            false,
            array('HTTP_Authorization' => sprintf('%s %s', $this->authorizationHeaderPrefix, $data['token']))
        );
    }

    /**
     * Executes a request on the given url and returns the response contents.
     *
     * This method also asserts the request was successful.
     *
     * @param string $path path of the requested page
     * @param string $method The HTTP method to use, defaults to GET
     * @param bool $authentication Whether to use authentication, defaults to false
     * @param bool $success to define whether the response is expected to be successful
     *
     * @return string
     */
    public function fetchContent($path, $method = 'GET', $authentication = false, $success = true)
    {
        if ($authentication) {
            $this->client->request($method, $path);
        } else {
            $this->authenticatedClient->request($method, $path);
        }

        $content = $this->client->getResponse()->getContent();
        if (is_bool($success)) {
            $this->isSuccessful($this->client->getResponse(), $success);
        }

        return $content;
    }

    /**
     * @param Response $response
     * @param int      $statusCode
     * @param bool     $checkValidJson
     */
    protected function assertJsonResponse(Response $response, $statusCode = 200, $checkValidJson = true)
    {
        $this->assertEquals($statusCode, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), $response->headers);

        if ($checkValidJson) {
            $decode = json_decode($response->getContent(), true);
            $this->assertTrue(
                ($decode !== null && $decode !== false),
                'is response valid json: [' . $response->getContent() . ']'
            );
        }
    }

    /**
     * Assert is protected by firewall (user authentication).
     *
     * @param string $method        The request method
     * @param string $uri           The URI to fetch
     * @param array  $parameters    The Request parameters
     * @param array  $files         The files
     * @param array  $server        The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param string $content       The raw body data
     * @param bool   $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return Crawler
     */
    public function assertBehindFirewall($method, $uri, array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
        $this->client->request(
            $method,
            $uri,
            $parameters,
            $files,
            $server,
            $changeHistory
        );

        // Decode response content data.
        $content = json_decode($this->client->getResponse()->getContent(), true);

        // Assert that authentication is needed.
        $this->assertEquals(
            array(
                'code' => 401,
                'message' => 'Invalid credentials',
            ),
            $content
        );
    }
}
