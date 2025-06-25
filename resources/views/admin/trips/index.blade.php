@extends('admin.layout')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Manage Trips</h2>
                    <a href="{{ route('admin.trips.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add New Trip
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origin</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                                <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trips as $trip)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $trip->id }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $trip->name }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $trip->originStation->name }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">{{ $trip->destinationStation->name }}</td>
                                    <td class="px-6 py-4 border-b text-sm text-gray-900">
                                        <a href="{{ route('admin.trips.edit', $trip) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <form method="POST" action="{{ route('admin.trips.destroy', $trip) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this trip?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 border-b text-sm text-gray-500 text-center">No trips found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($trips->hasPages())
                    <div class="mt-6">
                        {{ $trips->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
