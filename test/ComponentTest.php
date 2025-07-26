<?php

namespace gdata\smsportalen\test;

use gdata\smsportalen\Component;
use PHPUnit\Framework\TestCase;
use yii\base\Exception;
use yii\console\Application;

class ComponentTest extends TestCase {

    /** @var Application A working instance of the application with sms component */
    private $app;

    public function setUp():void {
        // Set up a console application to test the SMS component
        $config = [
            'class' => 'gdata\smsportalen\Component',
            'username' => 'username',
            'token' => 'nf4398f93hf934hf943hfoiewuhfiuew',
            'priority' => 5,
        ];
        $this->app = $this->getNewApplication($config);
    }

    /**
     *
     * @param array $smsConfig
     * @return Application
     * @throws \yii\base\InvalidConfigException
     */
    private function getNewApplication(array $smsConfig) {
        return new Application([
            'id' => 'unittest',
            'basePath'=>__DIR__,
            'components' => [
                'sms' => $smsConfig
            ]
        ]);
    }

    /**
     * Get the component from the Yii application
     * @return Component
     */
    private function getComponent() {
        return $this->app->sms;
    }

    public function testInstance() {
        $this->assertInstanceOf(Component::class, $this->getComponent());
    }

    public function testMissingUsername() {
        $config = [
            'class' => 'gdata\smsportalen\Component',
            'token' => 'nf4398f93hf934hf943hfoiewuhfiuew',
        ];
        $app = $this->getNewApplication($config);

        try {
            $sms = $app->sms; /* @var $sms \gdata\smsportalen\Component */
            $sms->init();
            $this->fail('Fails to throw exception when username is missing');
        }
        catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testMissingToken() {
        $config = [
            'class' => 'gdata\smsportalen\Component',
            'username' => 'your-username',
        ];
        $app = $this->getNewApplication($config);

        try {
            $sms = $app->sms; /* @var $sms \gdata\smsportalen\Component */
            $sms->init();
            $this->fail('Fails to throw exception when token is missing');
        }
        catch (Exception $e) {
            $this->assertTrue(true);
        }

    }



}
