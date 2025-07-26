<?php

namespace gdata\smsportalen;

/**
 * A library for sending SMS via smsportalen.no
 */
class Api {

    const VERSION = "0.1.1-beta";

    /**
     * The base URL for the API. This base url contains scheme + "://" + and hostname, example "https://www.example.com"
     * @var string
     */
    private $baseUrl;

    private $username;
    private $token;

    private $validPriorities = [1,2,3,4,5,6,7,8,9];

    private $debug = false;

    const DEFAULT_PRIORITY = 2;

    /** @var int The maximum number of recipients */
    const RECIPIENT_LIMIT = 5000;

    // ------------------------------------------------------------------------------------
    // A collection of codes that exceptions will use to identify the case of the exception:
    // ------------------------------------------------------------------------------------

    /** @var int If recipient limit is exceeded */
    const EXCEPTION_CODE_RECIPIENT_LIMIT = 1;
    /** @var int If given URL to the API is invalid, and parsing of it fails */
    const EXCEPTION_CODE_URL_PARSE_FAIL = 2;
    /** @var int Any parse error for the response from API */
    const EXCEPTION_CODE_RESPONSE_PARSE_FAIL = 3;
    /** @var int If any phone number has validation error */
    const EXCEPTION_CODE_PHONENUMBER_INVALID = 4;


    /**
     * Constructor
     * @param string $username
     * @param string $token
     * @param string $baseUrl
     */
    public function __construct($username, $token, $baseUrl = 'https://smsportalen.no') {
        $this->username = $username;
        $this->token = $token;
        $this->setBaseUrl($baseUrl);
    }


    /**
     * Make sure that base URL is valid URL, then store it to $this->baseUrl
     * @param string $baseUrl
     * @return void
     * @throws \Exception If URL could not be parsed
     */
    private function setBaseUrl($baseUrl) {
        $parts = parse_url($baseUrl);
        if (!is_array($parts)) {
            throw new \Exception('Could not parse the URL given: ' . $baseUrl, self::EXCEPTION_CODE_URL_PARSE_FAIL);
        }
        else if (!array_key_exists('host', $parts)) {
            throw new \Exception('No host found: ' . $baseUrl, self::EXCEPTION_CODE_URL_PARSE_FAIL);
        }
        else {
            // make sure scheme and hostname is set (not last slash)
            $this->baseUrl = 'https://' . $parts['host'];
        }
    }

    /**
     * Check if phone numbers are valid before sending to API
     * @param string $phonenumber A phone number
     * @return bool
     */
    private function isValidPhonenumber($phonenumber) {
        $length = strlen($phonenumber);

        if (!ctype_digit($phonenumber)) {
            return false;
        }
        else if ($length == 8) {
            $pre = substr($phonenumber, 0, 1);
            return (in_array($pre, ['4', '9']));
        }
        else if ($length == 12) {
            return substr($phonenumber, 0, 2)  === '58';
        }
        else {
            return false;
        }
    }

    /**
     * Validate all phonenumbers. Throw exception if any numbers are not valid.
     * This function will also make sure the limit of recipients are met.
     * @param array $phonenumbers
     * @return void
     * @throws \Exception If any of the numbers are not valid or the limit of recipients are exceeded.
     */
    private function validatePhonenumbers(array $phonenumbers) {
        $count = count($phonenumbers);
        if ($count > self::RECIPIENT_LIMIT) {
            $msg = "Recipients limit of ".self::RECIPIENT_LIMIT." exceeded, you are trying to send to $count recipients.";
            throw new \Exception($msg, self::EXCEPTION_CODE_RECIPIENT_LIMIT);
        }

        $invalid = [];
        foreach ($phonenumbers as $phonenumber) {
            if (!$this->isValidPhonenumber($phonenumber)) {
                $invalid[] = $phonenumber;
            }
        }
        if (!empty($invalid)) {
            $msg = 'Found invalid phonenumbers, SMS was not sent. Invalid numbers: ' . implode(', ', $invalid);
            throw new \Exception($msg, self::EXCEPTION_CODE_PHONENUMBER_INVALID);
        }
    }

    /**
     * Validate the priority value
     * @param int $priority
     * @return int
     */
    private function validatePriority($priority) {
        // Make sure the value is integer
        if (!is_int($priority)) { $priority = intval($priority); }

        // Check if value is valid value
        if (in_array($priority, $this->validPriorities, true)) {
            return $priority;
        }
        else {
            return self::DEFAULT_PRIORITY;
        }
    }

    /**
     * Turn debug mode on or off.
     * Debug mode will simulate sending SMS with a made up reponse.
     * @param bool $value
     * @return self
     */
    public function setDebugMode($value=true) {
        $this->debug = $value;
        return $this;
    }

    /**
     * Get a reponse for the simulation of sending SMS (debug mode)
     * @param string[] $recipients The recipients list for setting the corret number in response
     * @return Response
     * @throws \Exception
     */
    private function getSimulationResponse(array $recipients) {
        $responseData = [
            'status'=>200,
            'message'=>'200 OK',
            'scheduled_recipients_count'=>count($recipients),
        ];
        return new Response(json_encode($responseData));
    }


    /**
     * Send an SMS
     * @param string[] $phonenumbers An array of phonenumbers to send SMS to, max 5000 recipients.
     * @param string $message The message of the SMS
     * @param int $priority The priority of the SMS at the provider, 0-9 where 9 is the highest
     * @return Response
     * @throws \Exception If there are any errors, an exception will be thrown. This function will throw exception
     *                    if phone numbers does not validate, if you exceed the recipient count limit or any
     *                    other communication error with the API. Also if the API fails in its response, an exception
     *                    will be thrown. Look at the exceptions code and compare with exception constants in this class
     *                    to identify the case of the exception.
     */
    public function send(array $phonenumbers, $message, $priority=0) {
        $this->validatePhonenumbers($phonenumbers);
        $priority = $this->validatePriority($priority);

        if ($this->debug) {
            return $this->getSimulationResponse($phonenumbers);
        }
        else {
            $uri = $this->baseUrl . '/message/free';
            $payload = [
                'recipients' => $phonenumbers,
                'content' => $message,
                'priority' => $priority,
            ];
            $jsonPayload = json_encode($payload);

            $response = \Httpful\Request::post($uri)->
                basicAuth($this->username, $this->token)->
                sends('application/json')->
                parseWith(function ($body) {
                    return json_decode($body, true);
                })->
                body($jsonPayload)->
                send();
            try {
                $answer = new Response($response->raw_body);
                return $answer;
            } catch (\Exception $e) {
                // Throw exception from response further, but add a code that specifies where the error happened
                throw new \Exception($e->getMessage(), self::EXCEPTION_CODE_RESPONSE_PARSE_FAIL);
            }
        }
    }

    /**
     * Ping the API
     * @return bool True if ping is successfull
     * @throws \Httpful\Exception\ConnectionErrorException
     * @todo does not work, error at API
     */
    public function ping() {
        $uri = $this->baseUrl.'/api/ping';
        //var_dump($uri);
        $response = \Httpful\Request::get($uri)->
            expectsText()->
            send();
        //var_dump($response);
    }

}