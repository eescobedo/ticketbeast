<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewConcertListingTest extends TestCase
{
    /** @test */
    public function user_can_view_a_concert_list()
    {
        // Arrange
        // Create a concert
        $concert = Concert::create([
            'title' => 'The Red Chord',
            'subtitle' => 'with animosity and Lethargy',
            'date' => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17196',
            'additional_information' => 'For tickets, call (555) 555-5555.'
        ]);

        // Act
        // View the concert listing
        $this->visit('/concerts/' . $concert->id);

        // Assert
        // See the concert details
        $this->see('The Red Chord');
        $this->see('with animosity and Lethargy');
        $this->see('December 13, 2016 8:00pm');
        $this->see('3250');
        $this->see('The Mosh Pit');
        $this->see('123 Example Lane');
        $this->see('Laraville');
        $this->see('ON');
        $this->see('17196');
        $this->see('For tickets, call (555) 555-5555.');
    }
}
