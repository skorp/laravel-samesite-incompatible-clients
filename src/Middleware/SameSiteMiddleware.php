<?php
namespace Skorp\SameSite\Middleware;


use Closure;
use Skorp\Dissua\SameSite;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class SameSiteMiddleware
{

    protected $userAgent = null;

    public function handle($request, Closure $next)
    {
        $response =  $next($request);

        $this->userAgent = $request->header('User-Agent');
        $shouldSendSameSiteNone = SameSite::handle($this->userAgent);
        if(!$shouldSendSameSiteNone) {
            $sameSiteCookies = $this->getSameSiteCookies($response);

            if($sameSiteCookies) {
                $response = $this->resetSameSiteValue($response, $sameSiteCookies);
            }
        }
        return $response;
    }

    protected function getSameSiteCookies(Response $response) : array {
        $cookies = $response->headers->getCookies();
        $sameSiteCookies = array();
        if(count($cookies)>0) {
            foreach($cookies as $cookie) {
                if ($this->isDisabled($cookie->getName())) {
                    continue;
                }
                if($cookie->getSameSite() ){
                    $sameSiteCookies[] = $cookie;
                }
            }
        }
        return $sameSiteCookies;
    }


    protected function resetSameSiteValue(Response $response, array $cookies) : Response{


        foreach($cookies as $cookie) {
            $response->headers->setCookie($this->reset($cookie));
        }

        return $response;
    }


    private function reset(Cookie $cookie) : Cookie{
        return new Cookie(
            $cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(),
            $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(),
            $cookie->isHttpOnly(), $cookie->isRaw(), null
        );
    }


    private function isDisabled($name) {
        return in_array($name, config('samesite.except'));
    }

}
