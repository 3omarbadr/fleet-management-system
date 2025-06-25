@extends('admin.layout')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Scheduled Trips</h2>
                    <a href="#" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Schedule New Trip
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trip</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bus</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departure</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrival</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($scheduledTrips as $scheduledTrip)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $scheduledTrip->id }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $scheduledTrip->trip->name }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $scheduledTrip->bus->name }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $scheduledTrip->departure_time->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $scheduledTrip->arrival_time->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($scheduledTrip->status === 'scheduled') bg-green-100 text-green-800 
                                            @elseif($scheduledTrip->status === 'completed') bg-gray-100 text-gray-800
                                            @elseif($scheduledTrip->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst($scheduledTrip->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">
                                        {{ $scheduledTrip->bookings()->count() }}
                                    </td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">
                                        <div class="flex space-x-2">
                                            @if($scheduledTrip->status === 'scheduled')
                                                <form action="{{ route('admin.trips.scheduled.cancel', $scheduledTrip) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="text-yellow-600 hover:text-yellow-900"
                                                            onclick="return confirm('Are you sure you want to cancel this trip?')">
                                                        Cancel
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <form action="{{ route('admin.trips.scheduled.destroy', $scheduledTrip) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900"
                                                        onclick="return confirm('Are you sure you want to delete this trip? This action cannot be undone.')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 border-b text-sm text-gray-500 text-center">No scheduled trips found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="mt-4">
                    {{ $scheduledTrips->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 