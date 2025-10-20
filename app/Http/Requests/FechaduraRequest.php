<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FechaduraRequest extends FormRequest
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
        $rules = [
            'local' => 'required',
            'ip' => ['required', 'ipv4'],
            'porta' => 'required|integer|min:1|max:65535', 
            'usuario' => 'required',
            'senha' => 'sometimes|required'
        ];

        if ($this->method() == 'PATCH' || $this->method() == 'PUT') {
            $rules['ip'] = ['required', 'ipv4', 'unique:fechaduras,ip,' . $this->fechadura->id];
        }
    
        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'ip' => str_replace('-','',$this->ip)
        ]);
    }

    public function messages()
    {
        return [
            'local.required' => 'O local da fechadura é obrigatório',
            'ip.required' => 'O IP é obrigatório',
            'ip.ipv4' => 'Digite um IP válido',
            'usuario.required' => 'O usuário é obrigatório',
            'senha.required' => 'A senha é obrigatória'
        ];
    }
}
