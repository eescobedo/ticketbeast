<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Concert;

class ConcertOrdersController extends Controller
{

    private $paymentGateway;

    /**
     * ConcertOrdersController constructor.
     *
     * @param $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $this->validate(request(), [
            'email'           => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'payment_token'   => ['required']
        ]);

        $concert = Concert::find($concertId);

        // Charging the customer
        $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

        // Creating the order
        $concert->orderTickets(request('email'), request('ticket_quantity'));

        return response()->json([], 201);
    }
}
