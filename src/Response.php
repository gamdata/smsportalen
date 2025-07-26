<?php

namespace gdata\smsportalen;

use Exception;

/**
 * Represents the response from the API at smsportalen.no
 */
class Response {

    /**
     * The status as integer
     * @var int
     */
    public $status;

    /**
     * The response message from API
     * @var string
     */
    public $message;

    /**
     * The number of recipients that was recieved
     * @var int
     */
    public $scheduled_recipients_count;

    /**
     * The raw data response from API
     * @var string
     */
    private $rawData;


    /**
     * Constructor
     * @param string $data JSON string with results from smsportalen.no/message/free
     * @throws Exception
     */
    public function __construct($data) {
        // Expecting string to be JSON string
        $array = []; // init
        if (is_string($data)) {
            $array = json_decode($data, true);
            if (!is_array($array)) {
                throw new Exception('The data could not be parsed as a response from smsportalen.no');
            }
        }
        else {
            throw new Exception('The argument should be a JSON string');
        }

        $this->rawData = $data;
        $data = ['status','message','scheduled_recipients_count'];

        // Set valid attributes
        foreach ($data as $key) {
            if (array_key_exists($key, $array)) {
                $this->$key = $array[$key];
            }
            else {
                $msg = "The expected attribute '$key' was not returned by the API";
                throw new Exception($msg);
            }
        }
    }

    /**
     * Get the raw data returned from API
     * @return string
     */
    public function getRawData() {
        return $this->rawData;
    }

}