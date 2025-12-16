<?php

namespace App\Http\Requests\Penghuni;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequestPenghuni extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik'           => [
                'required',
                Rule::unique('penghuni', 'nik')->ignore($this->penghuni->id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('penghuni', 'email')->ignore($this->penghuni->id),
            ],
            'no_tlp' => [
                'required',
                Rule::unique('penghuni', 'no_tlp')->ignore($this->penghuni->id),
            ]
        ];
    }
}
