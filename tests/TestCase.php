<?php

namespace Skorp\SameSite\Tests;
use Illuminate\Http\Response;
use Skorp\SameSite\SameSiteIncompatibleClientsProvider;
use Illuminate\Routing\Router;

use Symfony\Component\HttpFoundation\Cookie;


class TestCase extends \Orchestra\Testbench\TestCase {

    protected function getPackageProviders($app)
    {
        return [SameSiteIncompatibleClientsProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {

        //set config
        $app['config']->set('samesite.groups', [
            'api',
            'web'
        ]);
        $app['config']->set('samesite.except', [
            'ex-cookie-test'
        ]);


        $router = $app['router'];

        $this->routeAdd($router);
    }

    protected function routeAdd(Router $router) {

        $router->get('cookie',function(Response $request) {
            $cookie = new Cookie('cookie-test',
                'value',
                26000,
                '/',
                null,
                false,
                true,
                false,
                Cookie::SAMESITE_NONE
            );
            return response('test')->withCookie($cookie);
        });

        $router->get('cookie-except',function(Response $request) {
            $cookie = new Cookie('ex-cookie-test',
                'value',
                26000,
                '/',
                null,
                false,
                true,
                false,
                Cookie::SAMESITE_NONE
            );
            return response('test')->withCookie($cookie);
        });
    }

}
