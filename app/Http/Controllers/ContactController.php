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

        return view('contacts.index', compact('contacts', 'customDefinitions'));
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
        $customs = $contact->customFieldValues->mapWithKeys(function ($v) {
            $name = $v->definition->name ?? $v->custom_field_definition_id;
            return [$name => $v->value];
        });
        $contactArray['custom_fields'] = $customs;
        $contact = $contactArray;   
        return view('contacts.show', compact('contact'));
    }

    public function edit($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->load('customFieldValues.definition');
        $customDefinitions = CustomFieldDefinition::all();
        $contactArray = $contact->toArray();
        $customs = $contact->customFieldValues->mapWithKeys(function ($v) {
            $name = $v->definition->name ?? $v->custom_field_definition_id;
            return [$name => $v->value];
        });
        $contactArray['custom_fields'] = $customs;
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
