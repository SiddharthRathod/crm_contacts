<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id', 'custom_field_definition_id', 'value', 'source_contact_id'
    ];

    public function sourceContact()
    {
        return $this->belongsTo(Contact::class, 'source_contact_id');
    }

    public function definition()
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'custom_field_definition_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
