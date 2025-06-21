<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Auth::user()->events()->latest()->paginate(10);
        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date|after:today',
            'venue' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'guest_count' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'requirements' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $validated['user_id'] = Auth::id();

        Event::create($validated);

        return redirect()->route('events.index')->with('success', 'Event created successfully!');
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);
        $vendors = Vendor::all()->groupBy('category');
        return view('events.show', compact('event', 'vendors'));
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'guest_count' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'requirements' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.show', $event)->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted successfully!');
    }
}
