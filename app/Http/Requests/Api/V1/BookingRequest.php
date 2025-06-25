<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by Sanctum middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scheduled_trip_id' => 'required|integer|exists:scheduled_trips,id',
            'seat_id' => 'required|integer|exists:seats,id',
            'start_station_id' => 'required|integer|exists:stations,id',
            'end_station_id' => 'required|integer|exists:stations,id|different:start_station_id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'scheduled_trip_id.required' => 'The scheduled trip ID is required.',
            'scheduled_trip_id.exists' => 'The selected scheduled trip does not exist.',
            'seat_id.required' => 'The seat ID is required.',
            'seat_id.exists' => 'The selected seat does not exist.',
            'start_station_id.required' => 'The start station ID is required.',
            'start_station_id.exists' => 'The selected start station does not exist.',
            'end_station_id.required' => 'The end station ID is required.',
            'end_station_id.exists' => 'The selected end station does not exist.',
            'end_station_id.different' => 'The end station must be different from the start station.',
        ];
    }
}
