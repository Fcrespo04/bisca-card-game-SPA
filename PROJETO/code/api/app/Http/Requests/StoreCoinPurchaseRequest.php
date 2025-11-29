<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoinPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_type' => 'required|in:MBWAY,PAYPAL,IBAN,MB,VISA',
            // Value is in Euros (1 to 99)
            'euros' => 'required|integer|min:1|max:99', 
            'payment_reference' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $type = $this->input('payment_type');
                    
                    $isValid = match ($type) {
                        'MBWAY' => preg_match('/^9\d{8}$/', $value), // 9 digits, start with 9
                        'PAYPAL' => filter_var($value, FILTER_VALIDATE_EMAIL), // Valid email
                        'IBAN' => preg_match('/^[A-Z]{2}\d{23}$/', $value), // 2 letters + 23 digits
                        'MB' => preg_match('/^\d{5}-\d{9}$/', $value), // 5 digits - 9 digits
                        'VISA' => preg_match('/^4\d{15}$/', $value), // 16 digits, start with 4
                        default => false,
                    };

                    if (!$isValid) {
                        $fail("The $attribute format is invalid for payment type $type.");
                    }
                },
            ],
        ];
    }
}