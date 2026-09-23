<?php

namespace Tests\Feature;

use App\Models\Nomination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NominationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withServerVariables(['SERVER_NAME' => 'localhost', 'HTTP_HOST' => 'localhost']);
    }

    public function test_nomination_form_page_loads(): void
    {
        $response = $this->get('/nomination');

        $response->assertStatus(200);
    }

    public function test_guest_can_submit_nomination_and_it_is_stored(): void
    {
        $response = $this->post(route('nominations.store'), [
            'company' => 'Test Company',
            'contact' => 'Jane Doe',
            'jobtitle' => 'CEO',
            'email' => 'jane@example.com',
            'confirm_email' => 'jane@example.com',
            'phone' => '+971500000000',
            'country' => 'UAE',
            'category' => 'Best Broker',
            'consent1' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('nominations', [
            'company' => 'Test Company',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_nomination_rejects_mismatched_emails(): void
    {
        $response = $this->post(route('nominations.store'), [
            'company' => 'Test Company',
            'email' => 'jane@example.com',
            'confirm_email' => 'other@example.com',
        ]);

        $response->assertSessionHasErrors('confirm_email');
        $this->assertDatabaseCount('nominations', 0);
    }

    public function test_guest_cannot_access_admin_nominations(): void
    {
        $response = $this->get('/admin/nominations');

        $response->assertRedirect('/admin/login');
    }
}
