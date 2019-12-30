<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LC\Common\Tests\HttpClient;

use LC\Common\HttpClient\ServerClient;
use PHPUnit\Framework\TestCase;

class ServerClientTest extends TestCase
{
    /**
     * @return void
     */
    public function testGet()
    {
        $httpClient = new TestHttpClient();
        $serverClient = new ServerClient($httpClient, 'serverClient');
        $this->assertTrue($serverClient->get('foo'));
    }

    /**
     * @return void
     */
    public function testQueryParameter()
    {
        $httpClient = new TestHttpClient();
        $serverClient = new ServerClient($httpClient, 'serverClient');
        $this->assertTrue($serverClient->get('foo', ['foo' => 'bar']));
    }

    /**
     * @return void
     */
    public function testError()
    {
        $this->expectException('LC\Common\HttpClient\Exception\HttpClientException');
        $this->expectExceptionMessage('[400] GET "serverClient/error": errorValue');
        $httpClient = new TestHttpClient();
        $serverClient = new ServerClient($httpClient, 'serverClient');
        $serverClient->get('error');
    }

    /**
     * @return void
     */
    public function testPost()
    {
        $httpClient = new TestHttpClient();
        $serverClient = new ServerClient($httpClient, 'serverClient');
        $this->assertTrue($serverClient->post('foo', ['foo' => 'bar']));
    }
}
