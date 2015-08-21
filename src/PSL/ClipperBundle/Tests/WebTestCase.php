<?php

namespace PSL\ClipperBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

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
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->client = static::createClient();
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
        $client = static::createClient();
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

        return static::createClient(
            array(),
            array('HTTP_Authorization' => sprintf('%s %s', $this->authorizationHeaderPrefix, $data['token']))
        );
    }

    /**
     * Extracts the location from the given route.
     *
     * @param string $route  The name of the route
     * @param array $params  Set of parameters
     * @param boolean $absolute
     *
     * @return string
     */
    protected function getUrl($route, $params = array(), $absolute = false)
    {
        $client = static::createClient();
        return $client->getContainer()->get('router')->generate($route, $params, $absolute);
    }

    /**
     * Checks the success state of a response
     *
     * @param Response $response Response object
     * @param bool $success to define whether the response is expected to be successful
     * @param string $type
     *
     * @return void
     */
    public function isSuccessful($response, $success = true, $type = 'text/html')
    {
        try {
            $crawler = new Crawler();
            $crawler->addContent($response->getContent(), $type);
            if (! count($crawler->filter('title'))) {
                $title = '['.$response->getStatusCode().'] - '.$response->getContent();
            } else {
                $title = $crawler->filter('title')->text();
            }
        } catch (\Exception $e) {
            $title = $e->getMessage();
        }

        if ($success) {
            $this->assertTrue($response->isSuccessful(), 'The Response was not successful: '.$title);
        } else {
            $this->assertFalse($response->isSuccessful(), 'The Response was successful: '.$title);
        }
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
