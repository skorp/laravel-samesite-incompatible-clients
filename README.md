# Laravel SameSite incompatible clients Middleware

<a href="https://github.com/skorp/laravel-samesite-incompatible-clients/blob/master/LICENSE"><img src="https://img.shields.io/github/license/skorp/laravel-samesite-incompatible-clients"></a>

## About
Some user agents are known to be incompatible with the `SameSite=None` attribute. <br>
This middleware for Laravel will try to determine and overwrite the Cookie value.


##Installation

You can install this package via composer using this command:

```bash
composer require "skorp/laravel-samesite-incompatible-clients"
```

Service provider will register itself.

you can publish the config file to make some changes.

```bash
php artisan vendor:publish --provider="Skorp\SameSite\SameSiteIncompatibleClientsProvider"
```


#### Links about SameSite-Cookie:
https://www.chromium.org/updates/same-site/incompatible-clients<br>
https://web.dev/samesite-cookie-recipes/<br>
https://www.netsparker.com/blog/web-security/same-site-cookie-attribute-prevent-cross-site-request-forgery<br>
https://www.thinktecture.com/identity/samesite/prepare-your-identityserver/

###Next step
- Tests
- cleanup the code


Your feedback is welcome.
