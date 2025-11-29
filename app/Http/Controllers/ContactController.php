<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\ContactCustomFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $contacts = $query->orderByDesc('id')->paginate(10);
        $customDefinitions = CustomFieldDefinition::all();

        if ($request->ajax()) {
            return view('contacts._rows', compact('contacts'));
        }

        $unMergedContacts = Contact::where('is_merged', false)->get();

        return view('contacts.index', compact('contacts', 'customDefinitions', 'unMergedContacts'));
    }

    public function addContact() {
        $customDefinitions = CustomFieldDefinition::all();
        return view('contacts.add', compact('customDefinitions'));
    }

    public function checkContactEmail(Request $request)
    {
        $email = $request->email;
        $contactId = $request->contact_id;
        $query = Contact::where('email', $email);
        if ($contactId) {
            $query->where('id', '!=', $contactId);
        }
        $exists = $query->exists();
        return response()->json(!$exists);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                        'name' => 'required|string|max:255',
                        'email' => 'required|email|max:255|unique:contacts,email',
                        'phone' => 'required|string|max:50',
                        'gender' => 'required|in:male,female,other',
                        'profile_image' => 'nullable|file|image|max:2048',
                        'additional_file' => 'nullable|file|max:5120',
                        'custom_fields' => 'nullable|array'
                    ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['success' => false, 'message' => implode('</br>', $errors)]);
        }

        $data = $validator->validated();

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('contacts/profile_images', 'public');
        }
        if ($request->hasFile('additional_file')) {
            $data['additional_file'] = $request->file('additional_file')->store('contacts/files', 'public');
        }
        
        try {            
            $contact = Contact::create($data);
            if ($request->filled('custom_fields')) {
                foreach ($request->custom_fields as $definitionId => $value) {
                    if ($value === null || $value === '') continue;
                    ContactCustomFieldValue::create([
                    'contact_id' => $contact->id,
                    'custom_field_definition_id' => $definitionId,
                    'value' => is_array($value) ? json_encode($value) : $value,
                    ]);
                }
            }
            return response()->json(['success' => true, 'message' => 'Contact created successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create contact: ' . $e->getMessage()], 500);
        }
        
    }

    public function show($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->load('customFieldValues.definition');
        $contactArray = $contact->toArray();
        $customs = [];
        foreach ($contact->customFieldValues as $v) {
            $name = $v->definition->name ?? $v->custom_field_definition_id;
            if (!isset($customs[$name])) $customs[$name] = [];
            $customs[$name][] = ['value' => $v->value, 'source_contact_id' => $v->source_contact_id];
        }
        $contactArray['custom_fields'] = $customs;
        $contact = $contactArray;
        if (request()->ajax()) {
            return response()->json(['contact' => $contact]);
        }
        return view('contacts.show', compact('contact'));
    }

    public function edit($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->load('customFieldValues.definition');
        $customDefinitions = CustomFieldDefinition::all();
        $contactArray = $contact->toArray();
        $customsMap = [];
        foreach ($contact->customFieldValues as $v) {
            $name = $v->definition->name ?? $v->custom_field_definition_id;
            if (!isset($customsMap[$name])) {
                $customsMap[$name] = $v->value;
            }
            if (!isset($customsMap[$v->custom_field_definition_id])) {
                $customsMap[$v->custom_field_definition_id] = $v->value;
            }
        }
        $contactArray['custom_fields'] = $customsMap;
        $contact = $contactArray;   
        return view('contacts.edit', compact('contact', 'customDefinitions'));
    }

    // Alias used by routes
    public function editContact($id)
    {
        return $this->edit($id);
    }

    public function updateContact(Request $request)
    {
        $validator = Validator::make($request->all(), (new UpdateContactRequest())->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['success' => false, 'message' => implode('</br>', $errors)]);
        }

        $contact = Contact::findOrFail($request->input('id'));

        $data = $validator->validated();

        if ($request->hasFile('profile_image')) {
            if ($contact->profile_image) {
                Storage::disk('public')->delete($contact->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('contacts/profile_images', 'public');
        }
        if ($request->hasFile('additional_file')) {
            if ($contact->additional_file) {
                Storage::disk('public')->delete($contact->additional_file);
            }
            $data['additional_file'] = $request->file('additional_file')->store('contacts/files', 'public');
        }

        try {
            $contact->update($data);

            if ($request->filled('custom_fields')) {
                foreach ($request->custom_fields as $definitionId => $value) {
                    $cfv = ContactCustomFieldValue::firstOrNew([
                        'contact_id' => $contact->id,
                        'custom_field_definition_id' => $definitionId,
                    ]);
                    if ($value === null || $value === '') {
                        if ($cfv->exists) $cfv->delete();
                        continue;
                    }
                    $cfv->value = is_array($value) ? json_encode($value) : $value;
                    $cfv->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Contact updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update contact: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Merge a secondary contact into a master contact.
     */
    public function merge(Request $request)
    {
        $data = $request->validate([
            'master_id' => 'required|integer|exists:contacts,id',
            'secondary_id' => 'required|integer|exists:contacts,id',
            'append_values' => 'nullable|boolean'
        ]);

        $masterId = $data['master_id'];
        $secondaryId = $data['secondary_id'];
        $append = isset($data['append_values']) ? (bool)$data['append_values'] : true;

        if ($masterId == $secondaryId) {
            return response()->json(['success' => false, 'message' => 'Cannot merge a contact into itself.']);
        }

        $master = Contact::findOrFail($masterId);
        $secondary = Contact::findOrFail($secondaryId);

        // Helper to ensure alt custom field definitions exist
        $ensureDef = function($name, $type = 'text') {
            $def = CustomFieldDefinition::where('name', $name)->first();
            if (!$def) {
                $def = CustomFieldDefinition::create(['name' => $name, 'field_type' => $type, 'options' => null, 'is_required' => false]);
            }
            return $def;
        };

        // Emails: if master empty -> set master; else append as alternate
        if (!empty($secondary->email)) {
            if (empty($master->email)) {
                $master->email = $secondary->email;
            } elseif ($master->email !== $secondary->email) {
                $def = $ensureDef('Alternate Email', 'text');
                ContactCustomFieldValue::create([
                    'contact_id' => $master->id,
                    'custom_field_definition_id' => $def->id,
                    'value' => $secondary->email,
                    'source_contact_id' => $secondary->id,
                ]);
            }
        }

        // Phones
        if (!empty($secondary->phone)) {
            if (empty($master->phone)) {
                $master->phone = $secondary->phone;
            } elseif ($master->phone !== $secondary->phone) {
                $def = $ensureDef('Alternate Phone', 'text');
                ContactCustomFieldValue::create([
                    'contact_id' => $master->id,
                    'custom_field_definition_id' => $def->id,
                    'value' => $secondary->phone,
                    'source_contact_id' => $secondary->id,
                ]);
            }
        }

        // 3. Files merge
        if (!empty($secondary->profile_image)) {
            if (empty($master->profile_image)) {
                $master->profile_image = $secondary->profile_image;
            } else {
                $def = $ensureDef('Alternate File', 'text');
                ContactCustomFieldValue::create([
                    'contact_id' => $master->id,
                    'custom_field_definition_id' => $def->id,
                    'value' => $secondary->profile_image,
                    'source_contact_id' => $secondary->id,
                ]);
            }
        }
        if (!empty($secondary->additional_file)) {
            if (empty($master->additional_file)) {
                $master->additional_file = $secondary->additional_file;
            } else {
                $def = $ensureDef('Alternate File', 'text');
                ContactCustomFieldValue::create([
                    'contact_id' => $master->id,
                    'custom_field_definition_id' => $def->id,
                    'value' => $secondary->additional_file,
                    'source_contact_id' => $secondary->id,
                ]);
            }
        }

        // Custom fields merge
        $secValues = $secondary->customFieldValues()->get();
        foreach ($secValues as $sv) {
            $exists = $master->customFieldValues()->where('custom_field_definition_id', $sv->custom_field_definition_id)->first();
            if (!$exists) {
                // Reassign the value to master
                $sv->contact_id = $master->id;
                $sv->source_contact_id = $secondary->id;
                $sv->save();
            } elseif ($append && $exists->value !== $sv->value) {
                // Keep master value, but append secondary as a new value record with source_contact_id
                ContactCustomFieldValue::create([
                    'contact_id' => $master->id,
                    'custom_field_definition_id' => $sv->custom_field_definition_id,
                    'value' => $sv->value,
                    'source_contact_id' => $secondary->id,
                ]);
            }
        }

        // Mark secondary as merged (don't delete)
        $secondary->is_merged = true;
        $secondary->merged_to_id = $master->id;
        $secondary->save();

        // Save master after changes
        $master->save();

        return response()->json(['success' => true, 'message' => 'Merge completed successfully']);
    }

    public function destroy($id)
    {   
        try {
            $contact = Contact::findOrFail($id);
            if ($contact->profile_image) {
                Storage::disk('public')->delete($contact->profile_image);
            }
            if ($contact->additional_file) {
                Storage::disk('public')->delete($contact->additional_file);
            }
            $contact->delete();
            return redirect()->back()->with('success', 'Contact deleted successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete contact: ' . $e->getMessage());
        }
    }
}
