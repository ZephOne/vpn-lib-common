<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LetsConnect\Common\Tests\Http;

use LetsConnect\Common\Http\CallbackHook;
use LetsConnect\Common\Http\Request;
use LetsConnect\Common\Http\Response;
use LetsConnect\Common\Http\Service;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function testGet()
    {
        $request = new TestRequest(
            [
                'REQUEST_URI' => '/foo',
            ]
        );

        $service = new Service();
        $service->get('/foo', function (Request $request) {
            $response = new Response(201, 'application/json');
            $response->setBody('{}');

            return $response;
        });
        $service->post('/bar', function (Request $request) {
            return new Response();
        });
        $response = $service->run($request);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('{}', $response->getBody());
    }

    /**
     * @return void
     */
    public function testMissingDocument()
    {
        $request = new TestRequest(
            [
                'REQUEST_URI' => '/bar',
            ]
        );

        $service = new Service();
        $service->get('/foo', function (Request $request) {
            $response = new Response(201, 'application/json');
            $response->setBody('{}');

            return $response;
        });
        $response = $service->run($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('{"error":"\"\/bar\" not found"}', $response->getBody());
    }

    /**
     * @return void
     */
    public function testUnsupportedMethod()
    {
        $request = new TestRequest(
            [
                'REQUEST_METHOD' => 'DELETE',
                'REQUEST_URI' => '/bar',
            ]
        );

        $service = new Service();
        $service->get('/foo', function (Request $request) {
            return new Response();
        });
        $service->post('/foo', function (Request $request) {
            return new Response();
        });
        $response = $service->run($request);

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('GET,POST', $response->getHeader('Allow'));
        $this->assertSame('{"error":"method \"DELETE\" not allowed"}', $response->getBody());
    }

    /**
     * @return void
     */
    public function testHooks()
    {
        $request = new TestRequest(
            [
                'REQUEST_URI' => '/foo',
            ]
        );

        $service = new Service();
        $callbackHook = new CallbackHook(
            function (Request $request) {
                return '12345';
            },
            function (Request $request, Response $response) {
                $response->addHeader('Foo', 'Bar');

                return $response;
            }
        );
        $service->addBeforeHook('test', $callbackHook);
        $service->addAfterHook('test', $callbackHook);

        $service->get('/foo', function (Request $request, array $hookData) {
            $response = new Response();
            $response->setBody($hookData['test']);

            return $response;
        });
        $response = $service->run($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('12345', $response->getBody());
        $this->assertSame('Bar', $response->getHeader('Foo'));
    }

    /**
     * @return void
     */
    public function testHookResponse()
    {
        $request = new TestRequest(
            [
                'REQUEST_URI' => '/foo',
            ]
        );
        $service = new Service();
        $callbackHook = new CallbackHook(
            function (Request $request) {
                return new Response(201);
            }
        );
        $service->addBeforeHook('test', $callbackHook);

        $service->get('/foo', function (Request $request, array $hookData) {
            return new Response();
        });
        $response = $service->run($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testHookDataPassing()
    {
        $request = new TestRequest([]);
        $service = new Service();
        $service->addBeforeHook(
            'test',
            new CallbackHook(
                function (Request $request, array $hookData) {
                    // this should be available in the next before hook
                    return '12345';
                }
            )
        );
        $service->addBeforeHook(
            'test2',
            new CallbackHook(
                function (Request $request, array $hookData) {
                    $response = new Response();
                    $response->setBody($hookData['test']);

                    return $response;
                }
            )
        );
        $response = $service->run($request);
        $this->assertSame('12345', $response->getBody());
    }

    /**
     * @return void
     */
    public function testBrowserNotFoundWithoutTpl()
    {
        $request = new TestRequest(
            [
                'REQUEST_URI' => '/bar',
                'HTTP_ACCEPT' => 'text/html',
            ]
        );

        $service = new Service();
        $service->get('/foo', function (Request $request) {
            return new Response(200);
        });
        $response = $service->run($request);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('404: "/bar" not found', $response->getBody());
    }

    /**
     * @return void
     */
    public function testBrowserNotFoundWithTpl()
    {
        $request = new TestRequest(
            [
                'REQUEST_URI' => '/bar',
                'HTTP_ACCEPT' => 'text/html',
            ]
        );

        $service = new Service(new TestHtmlTpl());
        $service->get('/foo', function (Request $request) {
            return new Response(200);
        });
        $response = $service->run($request);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('<html><head><title>404</title></head><body><h1>Error (404)</h1><p>"/bar" not found</p></body></html>', $response->getBody());
    }
}
