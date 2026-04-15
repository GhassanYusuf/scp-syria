<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParkingLotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Enforce via middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('parking_lots', 'name')->ignore($this->route('parkingLot')),
            ],
            'address' => 'required|string|max:500',
            'total_capacity' => 'required|integer|min:1|max:10000',
            'price_per_hour' => 'required|numeric|min:0.01|max:1000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'working_hours' => 'required|string|max:100',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الموقف مطلوب.',
            'name.unique' => 'يوجد موقف بهذا الاسم مسبقاً.',
            'address.required' => 'العنوان مطلوب.',
            'total_capacity.required' => 'السعة مطلوبة.',
            'price_per_hour.required' => 'سعر الساعة مطلوب.',
            'latitude.required' => 'خط العرض مطلوب.',
            'longitude.required' => 'خط الطول مطلوب.',
        ];
    }
}
?>

