<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LC\Common\Tests\Http;

use LC\Common\Http\CsrfProtectionHook;
use PHPUnit\Framework\TestCase;

class CsrfProtectionHookTest extends TestCase
{
    /**
     * @return void
     */
    public function testGoodPostReferrer()
    {
        $request = new TestRequest(
            [
                'HTTP_ACCEPT' => 'text/html',
                'REQUEST_METHOD' => 'POST',
                'HTTP_REFERER' => 'http://vpn.example/foo',
            ]
        );

        $referrerCheckHook = new CsrfProtectionHook();
        $this->assertTrue($referrerCheckHook->executeBefore($request, []));
    }

    /**
     * @return void
     */
    public function testGoodPostOrigin()
    {
        $request = new TestRequest(
            [
                'HTTP_ACCEPT' => 'text/html',
                'REQUEST_METHOD' => 'POST',
                'HTTP_ORIGIN' => 'http://vpn.example',
            ]
        );

        $referrerCheckHook = new CsrfProtectionHook();
        $this->assertTrue($referrerCheckHook->executeBefore($request, []));
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $request = new TestRequest(
            [
                'HTTP_ACCEPT' => 'text/html',
            ]
        );

        $referrerCheckHook = new CsrfProtectionHook();
        $this->assertFalse($referrerCheckHook->executeBefore($request, []));
    }

    /**
     * @return void
     */
    public function testCheckPostNoReferrer()
    {
        $this->expectException('LC\Common\Http\Exception\HttpException');
        $this->expectExceptionMessage('CSRF protection failed, no HTTP_ORIGIN or HTTP_REFERER');
        $request = new TestRequest(
            [
                'REQUEST_METHOD' => 'POST',
                'HTTP_ACCEPT' => 'text/html',
            ]
        );

        $referrerCheckHook = new CsrfProtectionHook();
        $referrerCheckHook->executeBefore($request, []);
    }

    /**
     * @return void
     */
    public function testCheckPostWrongReferrer()
    {
        $this->expectException('LC\Common\Http\Exception\HttpException');
        $this->expectExceptionMessage('CSRF protection failed: unexpected HTTP_REFERER');
        $request = new TestRequest(
            [
            'REQUEST_METHOD' => 'POST',
            'HTTP_REFERER' => 'http://www.attacker.org/foo',
            'HTTP_ACCEPT' => 'text/html',
            ]
        );

        $referrerCheckHook = new CsrfProtectionHook();
        $referrerCheckHook->executeBefore($request, []);
    }

    /**
     * @return void
     */
    public function testCheckPostWrongOrigin()
    {
        $this->expectException('LC\Common\Http\Exception\HttpException');
        $this->expectExceptionMessage('CSRF protection failed: unexpected HTTP_ORIGIN');
        $request = new TestRequest(
            [
            'REQUEST_METHOD' => 'POST',
            'HTTP_ORIGIN' => 'http://www.attacker.org',
            'HTTP_ACCEPT' => 'text/html',
            ]
        );

        $referrerCheckHook = new CsrfProtectionHook();
        $referrerCheckHook->executeBefore($request, []);
    }

    /**
     * @return void
     */
    public function testNonBrowser()
    {
        $request = new TestRequest(
            [
                'REQUEST_METHOD' => 'POST',
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        $referrerCheckHook = new CsrfProtectionHook();
        $referrerCheckHook->executeBefore($request, []);
        $this->assertTrue(true);
    }
}
