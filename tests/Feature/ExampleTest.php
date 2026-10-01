<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_routes_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect(route('auth.login'));
    }
}
