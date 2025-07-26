<?php

namespace gdata\smsportalen;

use yii\base\Exception;

/**
 * This is a wrapper for the Api class for sending SMS via smsportalen.no.
 * The wrapper is for Yii Framework v2.
 *
 * Please note that the Yii Framework is not required in this package to avoid
 * users
 */
class Component extends \yii\base\Component {

    /**
     * Null means default value which will be set in the Api class.
     * @var null|string
     */
    public $baseUrl = null;

    /**
     * The username you got from smsportalen.no, required!
     * @var string
     */
    public $username;

    /**
     * The access token you got from smsportalen.no, required!
     * @var string
     */
    public $token;

    /**
     * Priority of the SMS-es for the provider
     * @var int
     */
    public $priority = 1;

    /**
     * A cached Api object, created the first time you send SMS.
     * @var APi|null
     */
    private $api = null;


    /**
     * Initialize the component
     * @return void
     * @throws Exception
     */
    public function init() {
        parent::init();

        if (empty($this->username)) {
            throw new Exception('Username is required for SMS component.');
        }
        if (empty($this->token)) {
            throw new Exception('Token is required for SMS component.');
        }
    }

    /**
     * Send SMS.
     * @param string[] $recipients An array of phone numbers
     * @param string $message The SMS content
     * @param int|null $priority The priority of the SMS, if null, then defaults to priority in the configuration
     *                           of this component.
     * @return Response
     * @throws \Exception Please read the constructor and send method of Api for documentation of the exceptions thrown.
     */
    public function send(array $recipients, $message, $priority=null) {
        if (is_null($this->api)) {
            $this->api = new Api($this->username, $this->token, $this->baseUrl);
        }
        return $this->api->send($recipients, $message, $priority);
    }


}