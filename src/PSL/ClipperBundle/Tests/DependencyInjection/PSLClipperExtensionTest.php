<?php

// src/PSL/ClipperBundle/Tests/DependencyInjection/PSLClipperExtensionTest.php

namespace PSL\ClipperBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use PSL\ClipperBundle\DependencyInjection\PSLClipperExtension;

/**
 * PSLClipperExtensionTest test.
 */
class PSLClipperExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var PSLClipperExtension
     */
    private $extension;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new PSLClipperExtension();
    }

    public function tearDown()
    {
        unset($this->container, $this->extension);
    }

    public function testGoogleSpradsheetService()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('google_spreadsheet'));
    }

    public function testRPanelService()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('rpanel'));
    }

    public function testSurveyBuilderService()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('survey_builder'));
    }

    public function testLimeSurvey()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('limesurvey'));
    }

    public function testFWSSOUserProvider()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('fw_sso_user_provider'));
    }

    public function testFWSSOWebservice()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('fw_sso_webservices'));
    }

    public function testDrupalPasswordEncoder()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('drupal_password_encoder'));
    }

    public function testLimeSurveyPending()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('clipper.listener.order_complete'));
    }

    public function testLimeSurveyCreated()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('clipper.listener.limesurvey_created'));
    }

    public function testRpanelComplete()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('clipper.listener.rpanel_complete'));
    }

    public function testLimeSurveyComplete()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('clipper.listener.limesurvey_complete'));
    }

    public function testLimeSurveyResponses()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('clipper.listener.limesurvey_responses'));
    }
}
