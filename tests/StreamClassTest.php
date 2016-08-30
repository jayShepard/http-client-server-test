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
        $url_link = 'http://www.pillrcompany.com/interns/test?psr=true';
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
}