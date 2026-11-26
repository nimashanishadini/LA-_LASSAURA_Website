<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-rooms');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('villas')->ignore($this->route('room'))],
            'category' => ['required', Rule::in(['family', 'double'])],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'max_adults' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_children' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_guests' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['required', 'boolean'],
            'amenities' => ['sometimes', 'array'],
            'amenities.*' => ['integer', 'distinct', 'exists:amenities,id'],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if ($this->boolean('is_active') && ($this->input('max_adults') === null || $this->input('max_children') === null || $this->input('max_guests') === null)) {
                $validator->errors()->add('is_active', 'Set all guest limits before publishing this room.');
            }
            if ($this->filled('max_guests') && ($this->integer('max_adults') > $this->integer('max_guests') || $this->integer('max_children') > $this->integer('max_guests') || ($this->filled('max_adults') && $this->filled('max_children') && $this->integer('max_guests') > $this->integer('max_adults') + $this->integer('max_children')))) {
                $validator->errors()->add('max_guests', 'Total capacity must fit the adult and child limits.');
            }
        }];
    }
}
