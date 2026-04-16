<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ParkingLot;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to this request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'parking_lot_id' => 'required|exists:parking_lots,id',
            'customer_name'  => 'required|string|max:255',
            'phone'          => 'required|string|max:30',
            'vehicle_plate'  => 'nullable|string|max:20',
            'start_time'     => 'required|date|after:now',
            'end_time'       => 'required|date|after:start_time',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    /**
     * Configure the validator after the validation rules have run.
     */
    protected function passedValidation(): void
    {
        $lot = ParkingLot::findOrFail($this->parking_lot_id);
        $activeCount = $lot->carRegistries()->active()->count();

        if ($lot->total_capacity <= $activeCount) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'parking_lot_id' => ['الموقف ممتلئ حالياً. يرجى اختيار موقف آخر أو المحاولة لاحقاً.'],
            ])->status(409);
        }
    }
}
?>

