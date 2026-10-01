<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Webauthn Views
    |--------------------------------------------------------------------------
    |
    | The package default routes for the login and registration views are not
    | used: the login page and the key management page are part of the
    | application itself.
    |
    */

    'views' => false,

    /*
    |--------------------------------------------------------------------------
    | Redirect routes
    |--------------------------------------------------------------------------
    |
    | After a successful Webauthn login the user is redirected to the
    | after-login route, which forwards to the dashboard matching the
    | user's role. After registering a key, we stay on the key management
    | page.
    |
    */

    'redirects' => [
        'login' => '/auth/passkey/after-login',
        'register' => '/auth/passkeys',
        'key-confirmation' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Userless (one-tap / typeless) login
    |--------------------------------------------------------------------------
    |
    | When enabled, users can sign in with a passkey without typing their
    | email first. Passkeys registered as discoverable (resident) credentials
    | are offered directly by the authenticator. Newly registered passkeys
    | are always discoverable; passkeys registered before enabling this mode
    | must be re-registered to be usable without email.
    |
    */

    'userless' => true,
    'resident_key' => 'required',
    'user_verification' => 'required',

];
