<?php

namespace Skorp\SameSite\Tests;
use Illuminate\Contracts\Http\Kernel;
use Skorp\SameSite\Middleware\SameSiteMiddleware;


class SameSiteMiddlewareTest extends TestCase {

    use UserAgents;
    protected $url = 'cookie';
    protected $url_except = 'cookie-except';


    protected function getEnvironmentSetUp($app) {

        $kernel = $app->make(Kernel::class);
        $kernel->prependMiddleware(SameSiteMiddleware::class);

        parent::getEnvironmentSetUp($app);
    }


    /** @test */
    public function should_return_samesite_cookie_value_none() {

        $response = $this->get( $this->url, [
            'HTTP_USER_AGENT' => self::$chromeValidUserAgent,
        ]);
        $cookie = $response->headers->getCookies()[0];

        $this->assertEquals('none',$cookie->getSameSite());

        $response = $this->get( $this->url_except, [
            'HTTP_USER_AGENT' => self::$chromeValidUserAgent,
        ]);
        $cookie = $response->headers->getCookies()[0];

        $this->assertEquals('none',$cookie->getSameSite());

    }


    /** @test  */
    public function should_return_samesite_cookie_value_null() {
        /**
         * chrome 56
         */
        $response = $this->get( $this->url, [
            'HTTP_USER_AGENT' => self::$chromeInvalidUserAgent1,
        ]);

        $cookie = $response->headers->getCookies()[0];
        $this->assertNull($cookie->getSameSite());

        /**
         * chrome 66
         */
        $response = $this->get( $this->url, [
            'HTTP_USER_AGENT' => self::$chromeInvalidUserAgent2,
        ]);

        $cookie = $response->headers->getCookies()[0];
        $this->assertNull($cookie->getSameSite());

        /** IOS */
        $response = $this->get( $this->url, [
            'HTTP_USER_AGENT' => self::$iosInvalidUserAgent,
        ]);

        $cookie = $response->headers->getCookies()[0];
        $this->assertNull($cookie->getSameSite());

        /** Safari */
        $response = $this->get( $this->url, [
            'HTTP_USER_AGENT' => self::$macOSInvalidUserAgent,
        ]);

        $cookie = $response->headers->getCookies()[0];
        $this->assertNull($cookie->getSameSite());

        /** UC Browser */
        $response = $this->get( $this->url, [
            'HTTP_USER_AGENT' => self::$UCBrowserInvalidUserAgent,
        ]);

        $cookie = $response->headers->getCookies()[0];
        $this->assertNull($cookie->getSameSite());

        /** MAC embedded */
        $response = $this->get( $this->url, [
            'HTTP_USER_AGENT' => self::$MACEmbeddedInvalidUserAgent,
        ]);

        $cookie = $response->headers->getCookies()[0];
        $this->assertNull($cookie->getSameSite());

    }


    /** @test */
    public function should_return_samesite_cookie_value_none_incompatible_ua_excepted_cookie() {

        $response = $this->get( $this->url_except, [
            'HTTP_USER_AGENT' => self::$chromeInvalidUserAgent1,
        ]);
        $cookie = $response->headers->getCookies()[0];
        $this->assertEquals('none',$cookie->getSameSite());
    }


}
