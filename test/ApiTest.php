<?php

namespace gdata\smsportalen\test;

use gdata\smsportalen\Api;
use gdata\smsportalen\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApiTest extends TestCase {

    /**
     * @return void
     */
    public function testApiWrongToken() {
        $api = new Api('tempuser','wrongtoken');
        $response =  $api->send(['95835372'], 'Message from unit test');
        $this->assertInstanceOf(Response::class, $response, 'Test that response has correct class');
        $this->assertEquals(401, $response->status, 'Test that response has 401 error when authentication fails');
        $this->assertEquals(0, $response->scheduled_recipients_count, 'Test that response has 0 scheduled_recipients_count when authentication fails');
    }

    /**
     * Test that parsing of the base URL is correct
     */
    public function testBaseUrlParse() {
        $api = new Api('tempuser','wrongtoken');

        // Set up so that we can read back the private property "baseUrl"
        $reflection = new ReflectionClass($api);
        $property = $reflection->getProperty('baseUrl');
        $property->setAccessible(true);

        $this->assertEquals('https://smsportalen.no', $property->getValue($api));
    }

    public function testValidatePhonenumber() {
        $tests = [
            // Test valid cellphone numbers without country code
            '90000000'=>true,
            '99999999'=>true,
            '40000000'=>true,
            '49999999'=>true,

            // Test valid cellphone numbers with country code
            '4790000000'=>false,
            '4799999999'=>false,
            '4740000000'=>false,
            '4749999999'=>false,

            // test valid data numbers without country codes
            '580000000000'=>true,
            '589999999999'=>true,

            // Test valida data numbers with country code
            '47580000000000'=>false,
            '47589999999999'=>false,

            // Test with non-digits
            '479000000a'=>false,

            // test with whitespace (should fail)
            ' 4790000000'=>false,
            '4790000000 '=>false,
        ];

        $api = new Api('tempuser','wrongtoken');
        $class = new ReflectionClass($api); /* @var $class ReflectionClass */
        $method = $class->getMethod('isValidPhonenumber'); /* @var $method ReflectionMethod */
        $method->setAccessible(true);

        foreach ($tests as $recipient => $expected) {
            $result = $expected ? 'valid' : 'invalid';
            $errorMessage = "Test that '{$recipient}' should be $result phone number";
            $actual = $method->invokeArgs($api, [$recipient]);
            $this->assertEquals($expected, $actual, $errorMessage);
        }
    }

    public function testPriority() {
        $api = new Api('tempuser', 'wrongtoken');
        $class = new ReflectionClass($api);
        $method = $class->getMethod('validatePriority');
        $method->setAccessible(true);

        $property = $class->getProperty('validPriorities');
        $property->setAccessible(true);

        // Test that all valid proiorities returns the same value in method validatePriority()
        $properties = $property->getValue($api);
        foreach ($properties as $property) {
            $this->assertEquals($property, $method->invokeArgs($api, [$property]));
        }

        // test that the string version of all valid properties return the integer value
        foreach ($properties as $property) {
            $value = (string)$property;
            $this->assertEquals($value, $method->invokeArgs($api, [$value]));
        }

        // test
        // TODO: This dies not work ,because "a" becomes 0
        //$constant = $class->getConstant('DEFAULT_PRIORITY');
        //$this->assertEquals($constant, $method->invokeArgs($api, ['a']));
    }

    /**
     *
     * @return void
     * @todo cannot test ping, because ping fails at API side
     */
    public function XtestPing() {
        //$api = new Api('tempuser', 'wrongtoken');
        //$api->ping();
    }

    /**
     * Test each of the exceptions
     * @return void
     */
    public function testExceptionUrlParse() {
        // Test failing url parse
        try {
            $api = new Api('tempuser', 'wrongtoken', 'invalid url');
            $this->fail('Code fails to throw exception when base url is invalid');
        }
        catch (\Exception $e) {
            $this->assertEquals(Api::EXCEPTION_CODE_URL_PARSE_FAIL, $e->getCode());
        }
    }

    public function testExceptionRecipientLimit() {
        // Test exceeding the recipient count
        $api = new Api('tempuser', 'wrongtoken');
        $limit = Api::RECIPIENT_LIMIT;

        // Create a list of valid phonenumbers that exceed the limit of recipients
        $recipients = [];
        $start = 900_00_000;
        $last = $start+$limit+1; // one more than limit
        for ($i = $start; $i <= $last; $i++) {
            $recipients[] = (string)$i;
        }

        try {
            $api->send($recipients, 'Message from unit test');
            $this->fail('Code fails to throw exception when number of recipients exceed the limit');
        }
        catch (\Exception $e) {
            $this->assertEquals(Api::EXCEPTION_CODE_RECIPIENT_LIMIT, $e->getCode());
        }
    }

    public function testExceptionInvalidPhonenumber() {
        $api = new Api('tempuser', 'wrongtoken');
        $recipients = ['10000000'];
        try {
            $api->send($recipients, 'Message from unit test');
            $this->fail('Code fails to throw exception when invalid phone number');
        }
        catch (\Exception $e) {
            $this->assertEquals(Api::EXCEPTION_CODE_PHONENUMBER_INVALID, $e->getCode());
        }
    }

}
