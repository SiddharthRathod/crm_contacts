<?php

namespace App\Http\Controllers;

use App\Models\CustomFieldDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomFieldDefinitionController extends Controller
{
    public function index()
    {
        $definitions = CustomFieldDefinition::orderBy('id', 'desc')->get();
        return view('custom_fields.index', compact('definitions'));
    }

    public function addCustomFields() {
        return view('custom_fields.add');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                        'name' => 'required|string|max:255',
                        'field_type' => 'required|string',
                        'options' => 'nullable|string',
                        'is_required' => 'nullable|boolean',
                    ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['success' => false, 'message' => implode('</br>', $errors)]);
        }

        $data = $validator->validated();
        if (!isset($data['is_required'])) $data['is_required'] = false;

        try {
            CustomFieldDefinition::create($data);
            return response()->json(['success' => true, 'message' => 'Custom field created successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create custom field: ' . $e->getMessage()], 500);
        }
        
    }

    public function edit($id)
    {
        $customFieldDefinition = CustomFieldDefinition::findOrFail($id);
        return view('custom_fields.edit', compact('customFieldDefinition'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
                        'id' => 'required|integer|exists:custom_field_definitions,id',
                        'name' => 'required|string|max:255',
                        'field_type' => 'required|string',
                        'options' => 'nullable|string',
                        'is_required' => 'nullable|boolean',
                    ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['success' => false, 'message' => implode('</br>', $errors)]);
        }

        $data = $validator->validated();
        if (!isset($data['is_required'])) $data['is_required'] = false;
        unset($data['id']);

        try {            
            $customFieldDefinition = CustomFieldDefinition::findOrFail($request->id);
            $customFieldDefinition->update($data);
            return response()->json(['success' => true, 'message' => 'Custom field updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update custom field: ' . $e->getMessage()], 500);
        }
        
    }

    public function destroy($id)
    {
        try {
            $customFieldDefinition = CustomFieldDefinition::findOrFail($id);
            $customFieldDefinition->delete();
            return redirect()->back()->with('success', 'Custom field deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete custom field: ' . $e->getMessage());
        }
    }
}
