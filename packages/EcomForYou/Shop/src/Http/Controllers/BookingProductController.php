<?php

namespace EcomForYou\Shop\Http\Controllers;

use EcomForYou\BookingProduct\Helpers\AppointmentSlot as AppointmentSlotHelper;
use EcomForYou\BookingProduct\Helpers\DefaultSlot as DefaultSlotHelper;
use EcomForYou\BookingProduct\Helpers\EventTicket as EventTicketHelper;
use EcomForYou\BookingProduct\Helpers\RentalSlot as RentalSlotHelper;
use EcomForYou\BookingProduct\Helpers\TableSlot as TableSlotHelper;
use EcomForYou\BookingProduct\Repositories\BookingProductRepository;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingProductController extends Controller
{
    protected array $bookingHelpers = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected BookingProductRepository $bookingProductRepository,
        protected DefaultSlotHelper $defaultSlotHelper,
        protected AppointmentSlotHelper $appointmentSlotHelper,
        protected RentalSlotHelper $rentalSlotHelper,
        protected EventTicketHelper $eventTicketHelper,
        protected TableSlotHelper $tableSlotHelper
    ) {
        $this->bookingHelpers = [
            'default' => $this->defaultSlotHelper,
            'appointment' => $this->appointmentSlotHelper,
            'rental' => $this->rentalSlotHelper,
            'event' => $this->eventTicketHelper,
            'table' => $this->tableSlotHelper,
        ];
    }

    /**
     * Get available slots for the given product and the date.
     */
    public function index(int $id): JsonResource
    {
        $bookingProduct = $this->bookingProductRepository->find($id);

        return new JsonResource([
            'data' => $this->bookingHelpers[$bookingProduct->type]->getSlotsByDate($bookingProduct, request()->date),
        ]);
    }
}
