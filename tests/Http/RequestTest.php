<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LC\Common\Tests\Http;

use LC\Common\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetServerName()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('vpn.example', $request->getServerName());
    }

    /**
     * @return void
     */
    public function testGetRequestMethod()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('GET', $request->getRequestMethod());
    }

    /**
     * @return void
     */
    public function testGetPathInfo()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/foo/bar',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/foo/bar', $request->getPathInfo());
    }

    /**
     * @return void
     */
    public function testMissingPathInfo()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getPathInfo());
    }

    /**
     * @return void
     */
    public function testNoPathInfo()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/index.php',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getPathInfo());
    }

    /**
     * @return void
     */
    public function testRequireQueryParameter()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?user_id=foo',
                'SCRIPT_NAME' => '/index.php',
            ],
            [
                'user_id' => 'foo',
            ]
        );
        $this->assertSame('foo', $request->requireQueryParameter('user_id'));
    }

    /**
     * @return void
     */
    public function testOptionalQueryParameter()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?user_id=foo',
                'SCRIPT_NAME' => '/index.php',
            ],
            [
                'user_id' => 'foo',
            ]
        );
        $this->assertNull($request->optionalQueryParameter('foo'));
    }

    /**
     * @return void
     */
    public function testGetMissingQueryParameter()
    {
        $this->expectException('LC\Common\Http\Exception\HttpException');
        $this->expectExceptionMessage('missing query parameter "user_id"');
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $request->requireQueryParameter('user_id');
    }

    /**
     * @return void
     */
    public function testGetPostParameter()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ],
            [],
            [
                'user_id' => 'foo',
            ]
        );
        $this->assertSame('foo', $request->requirePostParameter('user_id'));
    }

    /**
     * @return void
     */
    public function testRequireHeader()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'HTTP_ACCEPT' => 'text/html',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('text/html', $request->requireHeader('HTTP_ACCEPT'));
    }

    /**
     * @return void
     */
    public function testOptionalHeader()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'HTTP_ACCEPT' => 'text/html',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('text/html', $request->optionalHeader('HTTP_ACCEPT'));
        $this->assertNull($request->optionalHeader('HTTP_FOO'));
    }

    /**
     * @return void
     */
    public function testRequestUri()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('http://vpn.example/', $request->getUri());
    }

    /**
     * @return void
     */
    public function testHttpsRequestUri()
    {
        $request = new Request(
            [
                'REQUEST_SCHEME' => 'https',
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '443',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('https://vpn.example/', $request->getUri());
    }

    /**
     * @return void
     */
    public function testNonStandardPortRequestUri()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '8080',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('http://vpn.example:8080/', $request->getUri());
    }

    /**
     * @return void
     */
    public function testGetRootSimple()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getRoot());
    }

    /**
     * @return void
     */
    public function testGetRootSame()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/connection',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getRoot());
    }

    /**
     * @return void
     */
    public function testGetRootPathInfo()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/admin/foo/bar',
                'SCRIPT_NAME' => '/admin/index.php',
            ]
        );
        $this->assertSame('/admin/', $request->getRoot());
    }

    /**
     * @return void
     */
    public function testScriptNameInRequestUri()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/admin/index.php/foo/bar',
                'SCRIPT_NAME' => '/admin/index.php',
            ]
        );
        $this->assertSame('/admin/', $request->getRoot());
        $this->assertSame('/foo/bar', $request->getPathInfo());
    }

    /**
     * @return void
     */
    public function testGetRootQueryString()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?foo=bar',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getRoot());
        $this->assertSame('/', $request->getPathInfo());
    }

    /**
     * @return void
     */
    public function testGetRootPathInfoQueryString()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => '80',
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/admin/foo/bar?foo=bar',
                'SCRIPT_NAME' => '/admin/index.php',
            ]
        );
        $this->assertSame('/admin/', $request->getRoot());
        $this->assertSame('/foo/bar', $request->getPathInfo());
    }
}
