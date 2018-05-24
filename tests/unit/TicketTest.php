<?php

use App\Concert;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function a_ticket_can_be_reserved()
    {
        // Arrange
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        // Act
        $ticket->reserve();

        // Assert
        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    function a_ticket_can_be_released()
    {
        // Arrange
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();
        $this->assertEquals($order->id, $ticket->order_id);

        // Act
        $ticket->release();

        // Assert
        $this->assertNull($ticket->fresh()->order_id);
    }
}
