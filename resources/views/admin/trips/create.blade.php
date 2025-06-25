@extends('admin.layout')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Create New Trip</h2>
                    <a href="{{ route('admin.trips.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Trips
                    </a>
                </div>

                <form method="POST" action="{{ route('admin.trips.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Trip Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="origin_station_id" class="block text-sm font-medium text-gray-700">Origin Station</label>
                            <select name="origin_station_id" id="origin_station_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    required>
                                <option value="">Select Origin Station</option>
                                @foreach($stations as $station)
                                    <option value="{{ $station->id }}" {{ old('origin_station_id') == $station->id ? 'selected' : '' }}>
                                        {{ $station->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('origin_station_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="destination_station_id" class="block text-sm font-medium text-gray-700">Destination Station</label>
                            <select name="destination_station_id" id="destination_station_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    required>
                                <option value="">Select Destination Station</option>
                                @foreach($stations as $station)
                                    <option value="{{ $station->id }}" {{ old('destination_station_id') == $station->id ? 'selected' : '' }}>
                                        {{ $station->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('destination_station_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trip Stops (in order)</label>
                        <div id="stops-container">
                            @if(old('stops'))
                                @foreach(old('stops') as $index => $stopId)
                                    <div class="flex items-center mb-2 stop-item">
                                        <select name="stops[]" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-2" required>
                                            <option value="">Select Station</option>
                                            @foreach($stations as $station)
                                                <option value="{{ $station->id }}" {{ $stopId == $station->id ? 'selected' : '' }}>
                                                    {{ $station->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" onclick="removeStop(this)" class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded">Remove</button>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex items-center mb-2 stop-item">
                                    <select name="stops[]" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-2" required>
                                        <option value="">Select Station</option>
                                        @foreach($stations as $station)
                                            <option value="{{ $station->id }}">{{ $station->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="removeStop(this)" class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded">Remove</button>
                                </div>
                            @endif
                        </div>
                        <button type="button" onclick="addStop()" class="bg-green-500 hover:bg-green-700 text-white px-3 py-1 rounded mt-2">Add Stop</button>
                        @error('stops')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Trip
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function addStop() {
    const container = document.getElementById('stops-container');
    const stopItem = container.querySelector('.stop-item').cloneNode(true);
    stopItem.querySelector('select').value = '';
    container.appendChild(stopItem);
}

function removeStop(button) {
    const container = document.getElementById('stops-container');
    if (container.children.length > 1) {
        button.parentElement.remove();
    } else {
        alert('At least one stop is required.');
    }
}
</script>
@endsection
