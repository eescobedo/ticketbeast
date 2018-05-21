<?php

use App\Order;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    function creating_an_order_from_tickets_and_email_and_amount()
    {
        // Arrange
        $concert = factory(Concert::class)->create()->addTickets(5);

        // Act
        $order = Order::forTickets($concert->findTickets(3), 'john@example.com', 3600);

        // Assert
        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(2, $concert->ticketsRemaining());
    }

    /** @test */
    function converting_to_an_array()
    {
        // Arrange
        $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(5);
        $order = $concert->orderTickets('jane@example.com', 5);

        // Act
        $result = $order->toArray();

        // Assert
        $this->assertEquals([
            'email'           => 'jane@example.com',
            'ticket_quantity' => 5,
            'amount'          => 6000
        ], $result);
    }

    /** @test */
    function tickets_are_relased_when_an_order_is_cancelled()
    {
        // Arrange
        $concert = factory(Concert::class)->create()->addTickets(10);

        // Act
        $order = $concert->orderTickets('jane@example.com', 5);

        // Assert
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull(Order::find($order->id));
    }
}
