<?php

use App\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseTicketsTest extends TestCase
{

    use DatabaseMigrations;

    private $paymentGateway;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params)
    {
        $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->decodeResponseJson());
    }

    /** @test */
    function customer_can_purchase_concert_tickets()
    {
        // Arrange
        // Create a concert
        $concert = factory(Concert::class)->create(['ticket_price' => 3250]);

        // Act
        // Purchase a concert tickets
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertResponseStatus(201);

        // Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create();

        // Act
        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('email');
    }

    /** @test */
    public function email_must_be_valid_to_purchase_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create();

        // Act
        $this->orderTickets($concert, [
            'email'           => 'not-an-email-address',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('email');
    }

    /** @test */
    function ticket_quantity_is_required_to_purchase_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create();

        // Act
        $this->orderTickets($concert, [
            'email'         => 'john@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create();

        // Act
        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'john@example.com',
            'ticket_quantity' => 0,
            'payment_totken'  => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    function payment_token_is_required()
    {
        // Arrange
        $concert = factory(Concert::class)->create();

        // Act
        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3
        ]);

        // Assert
        $this->assertValidationError('payment_token');
    }
}