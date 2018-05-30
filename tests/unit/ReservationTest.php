<?php


use App\Concert;
use App\Reservation;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReservationTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    function calculating_the_total_cost()
    {
        // Arrange
//        $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(3);
//        $tickets = $concert->findTickets(3);
        $tickets = collect([
            (object)['price' => 1200],
            (object)['price' => 1200],
            (object)['price' => 1200]
        ]);

        // Act
        $reservation = new Reservation($tickets);

        // Assert
        $this->assertEquals(3600, $reservation->totalCost());
    }

    /** @test */
    function reserved_tickets_are_released_when_a_reservation_is_cancelled()
    {
        // Arrange
        $ticket1 = Mockery::mock(Ticket::class);
        $ticket1->shouldReceive('release')->once();

        $ticket2 = Mockery::mock(Ticket::class);
        $ticket2->shouldReceive('release')->once();

        $ticket3 = Mockery::mock(Ticket::class);
        $ticket3->shouldReceive('release')->once();


        $tickets = collect([$ticket1, $ticket2, $ticket3]);
        $reservation = new Reservation($tickets);

        // Act
        $reservation->cancel();


        // Assert
    }
}