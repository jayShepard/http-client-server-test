<?php
/**
 * Created by PhpStorm.
 * User: jay
 * Date: 8/30/16
 * Time: 10:37 AM
 */

require __DIR__ . '/../vendor/autoload.php';

use \pillr\library\http\Stream as Stream;

class StreamTest extends \PHPUnit_Framework_TestCase{

    public function testStream()
    {
        $url_link = 'http://google.com';
        $stream = new Stream($url_link);

        $this->assertEquals(
            $stream->eof(),
            false
        );

        $this->assertEquals(
            $stream->tell(),
            0
        );

    }
    public function test_stream_rewind(){
        $url_link = 'http://google.com';
        $stream = new Stream($url_link);

        $stream->seek(100);
        $this->assertEquals(
          $stream->tell(),
            100
        );

        $stream->rewind();
        $this->assertEquals(
            $stream->tell(),
            0
        );
    }
}