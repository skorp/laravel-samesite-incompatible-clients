<?php
namespace Skorp\SameSite\Middleware;


use Closure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class SameSiteMiddleware
{
    /**
     * Handle an outgoing SameSite cookies
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */

    protected $userAgent = null;

    public function handle($request, Closure $next)
    {
        $response =  $next($request);

        $this->userAgent = $request->header('User-Agent');

        $shouldSendSameSiteNone = $this->shouldSendSameSiteNone();
        if(!$shouldSendSameSiteNone) {
            $sameSiteCookies = $this->getSameSiteCookies($response);

            if($sameSiteCookies) {
                $response = $this->resetSameSiteValue($response, $sameSiteCookies);
            }

        }
        return $response;
    }


    private function shouldSendSameSiteNone() : bool {
        return !$this->isSameSiteNoneIncompatible();
    }


    private function isSameSiteNoneIncompatible() : bool {
        return $this->hasWebKitSameSiteBug() || $this->dropsUnrecognizedSameSiteCookies();
    }


    private function hasWebKitSameSiteBug() : bool {
        return $this->isIosVersion(12) ||
           ($this->isMacosxVersion(10, 14) &&
            ($this->isSafari() || $this->isMacEmbeddedBrowser()));
    }


    private function dropsUnrecognizedSameSiteCookies() : bool {
        if ($this->isUcBrowser())
            return !$this->isUcBrowserVersionAtLeast(12, 13, 2);

        return  $this->isChromiumBased() &&
                $this->isChromiumVersionAtLeast(51) &&
                !$this->isChromiumVersionAtLeast(67);
    }


    private function isChromiumBased() : bool  {
        $regex = '/Chrom(e|ium)/';
        return preg_match($regex,$this->userAgent);
    }


    private function isChromiumVersionAtLeast($version)  : bool {
        $regex = '/Chrom[^ \/]+\/(\d+)[\.\d]*/';
        preg_match($regex,$this->userAgent,$matches);
        return ($matches[1]??null) >= $version;
    }


    private function isIosVersion($major) : bool {
        $regex = "/\(iP.+; CPU .*OS (\d+)[_\d]*.*\) AppleWebKit\//";
        preg_match($regex,$this->userAgent,$matches);
        return ($matches[1]??null) == $major;
    }


    private function isMacosxVersion($major,$minor) : bool {
        $regex = "/\(Macintosh;.*Mac OS X (\d+)_(\d+)[_\d]*.*\) AppleWebKit\//";
        preg_match($regex,$this->userAgent,$matches);

        return (($matches[1]??null) == $major   && (($matches[2]??null) == $minor));
    }


    private function isSafari() : bool {
        $regex = "/Version\/.* Safari\//";
    return preg_match($regex,$this->userAgent) && ! $this->isChromiumBased();
    }


    private function isMacEmbeddedBrowser() : bool {
        $regex = "#/^Mozilla\/[\.\d]+ \(Macintosh;.*Mac OS X [_\d]+\) AppleWebKit\/[\.\d]+ \(KHTML, like Gecko\)$#";
        return preg_match($regex,$this->userAgent);
    }


    private function isUcBrowser()  : bool {
        $regex = '/UCBrowser\//';
        return preg_match($regex,$this->userAgent);
    }


    private function isUcBrowserVersionAtLeast($major,$minor,$build) : bool {

        $regex = "/UCBrowser\/(\d+)\.(\d+)\.(\d+)[\.\d]* /";

        preg_match($regex,$this->userAgent,$matches);

        $major_version = $matches[1] ?? null;
        $minor_version = $matches[2] ?? null;
        $build_version = $matches[3] ?? null;

        if ($major_version != $major)
            return $major_version > $major;
        if ($minor_version != $minor)
            return $minor_version > $minor;
        return $build_version >= $build;
    }


    private function getSameSiteCookies(Response $response) : array {
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


    private function resetSameSiteValue(Response $response, array $cookies) : Response{


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
