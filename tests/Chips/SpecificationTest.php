<?php
/**
 * SpecificationTest.php
 * User: moyo
 * Date: 2019-04-02
 * Time: 13:39
 */

namespace Carno\HRPC\Accel\Tests\Chips;

use Carno\HRPC\Accel\Chips\Specification;
use Carno\HTTP\Standard\Request;
use Carno\HTTP\Standard\Response;
use Carno\HTTP\Standard\Streams\Body;
use Carno\HTTP\Standard\Uri;
use PHPUnit\Framework\TestCase;

class SpecificationTest extends TestCase
{
    use Specification;

    public function testRequest()
    {
        $req1 = new Request(
            'POST',
            new Uri('http', $host = 'ns.g.s', null, '/ivk/hello/world'),
            $hds = ['X-A' => ['1'], 'X-B' => ['S']],
            new Body($bd = 'moyo')
        );

        $hds['Host'] = [$host];

        $j = '{"server":"ns.g.s","service":"hello","method":"world","status":0,"meta":{"X-A":"1","X-B":"S","Host":"ns.g.s"},"payload":"bW95bw=="}';

        $s = $this->request2s($req1);

        $this->assertEquals($j, $s);

        $req2 = $this->s2request($j);

        $this->assertEquals($req2->getMethod(), 'POST');
        $this->assertEquals((string)$req2->getUri(), 'http://ns.g.s:80/invoke/hello/world');
        $this->assertEquals($req2->getHeaders(), $hds);
        $this->assertEquals((string)$req2->getBody(), $bd);
    }

    public function testResponse()
    {
        $rsp1 = new Response($code = 200, $hds = ['X-1' => ['A'], 'X2' => ['3']], $bd = 'world');

        $j = '{"server":"","service":"","method":"","status":200,"meta":{"X-1":"A","X2":"3"},"payload":"d29ybGQ="}';

        $s = $this->response2s($rsp1);

        $this->assertEquals($j, $s);

        $rsp2 = $this->s2response($s);

        $this->assertEquals($rsp2->getStatusCode(), $code);
        $this->assertEquals($rsp2->getHeaders(), $hds);
        $this->assertEquals((string)$rsp2->getBody(), $bd);
    }
}
