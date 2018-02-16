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
        $concert = Concert::find($concertId);

        // Charging the customer
        $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

        // Creating the order
        $concert->orderTickets(request('email'), request('ticket_quantity'));

        return response()->json([], 201);
    }
}
