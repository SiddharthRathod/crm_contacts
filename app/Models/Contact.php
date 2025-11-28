<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'gender', 'profile_image', 'additional_file'
    ];

    protected $appends = [];

    public function customFieldValues()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    public function getCustomFieldsAttribute()
    {
        return $this->customFieldValues()->with('definition')->get()->mapWithKeys(function ($v) {
            return [$v->definition->name ?? $v->custom_field_definition_id => $v->value];
        })->toArray();
    }
}
