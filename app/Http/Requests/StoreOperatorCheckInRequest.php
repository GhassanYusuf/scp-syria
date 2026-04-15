<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperatorCheckInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Enforce via operator middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parking_lot_id' => 'required|exists:parking_lots,id',
            'vehicle_plate' => 'required|string|max:50',
            'user_name' => 'nullable|string|max:255',
            'user_phone' => 'nullable|string|max:20',
            'duration_hours' => 'required|numeric|min:0.25|max:24',
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_plate.required' => 'رقم اللوحة مطلوب.',
            'parking_lot_id.exists' => 'موقف السيارات غير موجود.',
            'duration_hours.required' => 'مدة الإقامة مطلوبة.',
        ];
    }
}
?>

