<?php

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    function tickets_are_relased_when_an_order_is_cancelled()
    {
        // Arrange
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        // Act
        $order = $concert->orderTickets('jane@example.com', 5);

        // Assert
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull(Order::find($order->id));
    }
}
