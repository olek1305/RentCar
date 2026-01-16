<?php

namespace Tests\Feature;

use App\Models\CurrencySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    #[Test]
    public function it_displays_currency_index_page_for_authenticated_user()
    {
        CurrencySetting::factory()->eur()->default()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.currencies.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.currencies.index');
        $response->assertViewHas('currencies');
        $response->assertViewHas('defaultCurrency');
    }

    #[Test]
    public function it_redirects_guest_from_currency_index()
    {
        $response = $this->get(route('admin.currencies.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function it_returns_default_currency_fallback()
    {
        // No currencies in database
        $default = CurrencySetting::getDefaultCurrency();

        $this->assertEquals('USD', $default->currency_code);
        $this->assertEquals('$', $default->currency_symbol);
    }

    #[Test]
    public function it_gets_default_currency_from_database()
    {
        CurrencySetting::factory()->eur()->default()->create();
        CurrencySetting::factory()->usd()->create();

        $default = CurrencySetting::getDefaultCurrency();

        $this->assertEquals('EUR', $default->currency_code);
        $this->assertTrue($default->is_default);
    }

    #[Test]
    public function it_prepares_amount_for_stripe()
    {
        $currency = CurrencySetting::factory()->eur()->default()->create();

        // Bind currency to app
        app()->instance('currency', $currency);

        $result = $currency->prepareForStripe(10.50);

        $this->assertEquals(1050, $result['amount']);
        $this->assertEquals('eur', $result['currency']);
    }
}
