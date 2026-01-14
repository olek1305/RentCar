<?php

namespace Tests\Feature;

use App\Models\CurrencySetting;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        // Create default currency for tests
        CurrencySetting::factory()->eur()->default()->create();
    }

    #[Test]
    public function it_displays_orders_index_for_authenticated_user()
    {
        Order::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
        $response->assertViewHas('orders');
    }

    #[Test]
    public function it_redirects_guest_from_orders_index()
    {
        $response = $this->get(route('admin.orders.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function it_filters_orders_by_status()
    {
        Order::factory()->pending()->count(2)->create();
        Order::factory()->paid()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.index', ['status' => 'paid']));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) {
            return $orders->count() === 3;
        });
    }

    #[Test]
    public function it_filters_orders_by_email()
    {
        Order::factory()->create(['email' => 'john@example.com']);
        Order::factory()->create(['email' => 'jane@example.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.index', ['email' => 'john']));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) {
            return $orders->count() === 1;
        });
    }

    #[Test]
    public function it_displays_order_details()
    {
        $order = Order::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order));

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.show');
        $response->assertViewHas('order');
    }

    #[Test]
    public function it_paginates_orders_list()
    {
        Order::factory()->count(25)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) {
            return $orders->count() === 10; // Default pagination
        });
    }

    #[Test]
    public function order_can_send_payment_link_for_correct_statuses()
    {
        $pendingOrder = Order::factory()->pending()->create();
        $confirmedOrder = Order::factory()->create(['status' => 'confirmed']);
        $paidOrder = Order::factory()->paid()->create();

        $this->assertTrue($pendingOrder->canSendPaymentLink());
        $this->assertTrue($confirmedOrder->canSendPaymentLink());
        $this->assertFalse($paidOrder->canSendPaymentLink());
    }

    #[Test]
    public function order_can_be_finished_for_correct_statuses()
    {
        $paidOrder = Order::factory()->paid()->create();
        $completedOrder = Order::factory()->completed()->create();
        $pendingOrder = Order::factory()->pending()->create();

        $this->assertTrue($paidOrder->canBeFinished());
        $this->assertTrue($completedOrder->canBeFinished());
        $this->assertFalse($pendingOrder->canBeFinished());
    }
}
