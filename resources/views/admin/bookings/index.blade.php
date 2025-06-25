@extends('admin.layout')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Manage Bookings</h2>
                    <div class="text-sm text-gray-600">
                        Total Bookings: {{ $bookings->total() ?? $bookings->count() }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trip</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seat</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $booking->id }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $booking->user->name }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $booking->scheduledTrip->trip->name }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $booking->seat->seat_number }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">
                                        {{ $booking->startStation->name }} → {{ $booking->endStation->name }}
                                    </td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($booking->status === 'confirmed') bg-green-100 text-green-800 
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $booking->created_at->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">
                                        @if($booking->status === 'confirmed')
                                            <a href="#" class="text-red-600 hover:text-red-900">Cancel</a>
                                        @else
                                            <span class="text-gray-400">No actions</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 border-b text-sm text-gray-500 text-center">No bookings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($bookings, 'links'))
                    <div class="mt-6">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 