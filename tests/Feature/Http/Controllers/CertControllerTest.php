<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Cert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CertController
 */
final class CertControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        $certs = Cert::factory()->count(3)->create();

        $response = $this->get(route('certs.index'));

        $response->assertOk();
        $response->assertViewIs('cert.index');
        $response->assertViewHas('certs');
    }


    #[Test]
    public function create_displays_view(): void
    {
        $response = $this->get(route('certs.create'));

        $response->assertOk();
        $response->assertViewIs('cert.create');
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CertController::class,
            'store',
            \App\Http\Requests\CertStoreRequest::class
        );
    }

    #[Test]
    public function store_saves_and_redirects(): void
    {
        $response = $this->post(route('certs.store'));

        $response->assertRedirect(route('certs.index'));
        $response->assertSessionHas('cert.id', $cert->id);

        $this->assertDatabaseHas(certs, [ /* ... */ ]);
    }


    #[Test]
    public function show_displays_view(): void
    {
        $cert = Cert::factory()->create();

        $response = $this->get(route('certs.show', $cert));

        $response->assertOk();
        $response->assertViewIs('cert.show');
        $response->assertViewHas('cert');
    }


    #[Test]
    public function edit_displays_view(): void
    {
        $cert = Cert::factory()->create();

        $response = $this->get(route('certs.edit', $cert));

        $response->assertOk();
        $response->assertViewIs('cert.edit');
        $response->assertViewHas('cert');
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CertController::class,
            'update',
            \App\Http\Requests\CertUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $cert = Cert::factory()->create();

        $response = $this->put(route('certs.update', $cert));

        $cert->refresh();

        $response->assertRedirect(route('certs.index'));
        $response->assertSessionHas('cert.id', $cert->id);
    }


    #[Test]
    public function destroy_deletes_and_redirects(): void
    {
        $cert = Cert::factory()->create();

        $response = $this->delete(route('certs.destroy', $cert));

        $response->assertRedirect(route('certs.index'));

        $this->assertSoftDeleted($cert);
    }
}
