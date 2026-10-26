<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_la_raiz_redirige_al_registro_publico(): void
    {
        $this->get('/')->assertRedirect(route('registro.personal'));
    }
}
