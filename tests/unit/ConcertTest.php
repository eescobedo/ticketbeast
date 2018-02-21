<?php

use App\Concert;
use app\Exceptions\NotEnoughTicketsException;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function can_get_formatted_date()
    {
        // Create a concert with a known date
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm')
        ]);

        // Verify the date is formatted as expected
        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    public function can_get_formatted_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-12 17:00:00')
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    /** @test */
    public function concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB = factory(Concert::class)->create(['published_at' => Carbon::parse('-2 week')]);
        $unpublishedConcert = factory(Concert::class)->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    public function can_order_concert_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create()->addTickets(3);

        // Act
        $order = $concert->orderTickets('jane@example.com', 3);

        // Assert
        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
    }

    /** @test */
    function can_add_tickets()
    {
        // Arrange
        $concert = factory(Concert::class)->create();

        // Act
        $concert->addTickets(50);

        // Assert
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        // Arrange
        $concert = factory(Concert::class)->create()->addTickets(50);

        // Act
        $concert->orderTickets('jane@example.com', 30);

        // Assert
        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        // Arrange
        $concert = factory(Concert::class)->create()->addTickets(10);

        // Act
        try {
            $concert->orderTickets('jane@example.com', 11);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('jane@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        // Assert
        $this->fail("Order succeeded even though there were not enough tickets remaining.");
    }

    /** @test */
    function cannot_order_tickets_that_have_already_been_purchased()
    {
        // Arrange
        $concert = factory(Concert::class)->create()->addTickets(10);
        $concert->orderTickets('jane@example.com', 8);

        // Act
        try {
            $concert->orderTickets('john@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('jhon@example.com'));
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        // Assert
        $this->fail("Order succeeded even though there were not enough tickets remaining.");
    }
}
