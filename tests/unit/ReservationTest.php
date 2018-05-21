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
}