<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Auth::user()->bookings()->with(['event', 'vendor'])->latest()->paginate(10);
        return view('bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $event = Event::findOrFail($request->event_id);
        $vendor = Vendor::findOrFail($request->vendor_id);
        
        $this->authorize('view', $event);

        return view('bookings.create', compact('event', 'vendor'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'vendor_id' => 'required|exists:vendors,id',
            'booking_date' => 'required|date|after:today',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $event = Event::findOrFail($validated['event_id']);
        $this->authorize('view', $event);

        // Check if booking already exists
        $existingBooking = Booking::where('event_id', $validated['event_id'])
                                 ->where('vendor_id', $validated['vendor_id'])
                                 ->where('status', '!=', 'cancelled')
                                 ->first();

        if ($existingBooking) {
            return back()->withErrors(['vendor_id' => 'This vendor is already booked for this event.']);
        }

        $validated['user_id'] = Auth::id();

        Booking::create($validated);

        return redirect()->route('bookings.index')->with('success', 'Booking created successfully!');
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        return view('bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $booking->update($validated);

        return back()->with('success', 'Booking status updated successfully!');
    }
}
