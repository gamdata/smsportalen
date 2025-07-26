<?php

namespace gdata\smsportalen\test;

use gdata\smsportalen\Response;
use PHPUnit\Framework\TestCase;

/**
 * Test that the Response class behaves as expected
 */
class ResponseTest extends TestCase {

    public function testResponseOk() {
        $data = [
            'status'=>200,
            'message'=>'OK',
            'scheduled_recipients_count'=>1,
        ];
        $response = new Response(json_encode($data));

        $this->assertEquals(200, $response->status);
        $this->assertEquals('OK', $response->message);
        $this->assertEquals(1, $response->scheduled_recipients_count);
    }

    /**
     * Test that invalid payload throws Exception
     * @return void
     */
    public function testResponseInvalidString() {
        $data = 'test';
        $this->expectException(\Exception::class);
        new Response($data);
    }

    public function testResponseInteger() {
        $data = 1;
        $this->expectException(\Exception::class);
        new Response($data);
    }

    public function testResponseArray() {
        $data = [];
        $this->expectException(\Exception::class);
        new Response($data);
    }

    public function testMissingAttributeStatus() {
        $data = [
            'message'=>'OK',
            'scheduled_recipients_count'=>1,
        ];
        $this->expectException(\Exception::class);
        new Response(json_encode($data));
    }

    public function testMissingAttributeMessage() {
        $data = [
            'status'=>200,
            'scheduled_recipients_count'=>1,
        ];
        $this->expectException(\Exception::class);
        new Response(json_encode($data));
    }

    public function testMissingAttributeScheduledRecipientsCount() {
        $data = [
            'message'=>'OK',
            'status'=>200
        ];
        $this->expectException(\Exception::class);
        new Response(json_encode($data));
    }




}
