<x-app-layout>
    @section('title', 'Update Custom Field')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Update Custom Field') }}</div>
                    <div class="card-body">
                        <form action="javascript:void(0);" method="POST" id="customFieldUpdateForm">
                            @csrf
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-end">Name<span class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input type="text" name="name" value="{{ old('name', $customFieldDefinition->name) }}" class="form-control @error('name') is-invalid @enderror">
                                    @error('name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-end">Field Type</label>
                                <div class="col-md-6">
                                    <select name="field_type" class="form-select @error('field_type') is-invalid @enderror">
                                        <option value="text" {{ $customFieldDefinition->field_type == 'text' ? 'selected': '' }}>Text</option>
                                        <option value="date" {{ $customFieldDefinition->field_type == 'date' ? 'selected': '' }}>Date</option>
                                        <option value="number" {{ $customFieldDefinition->field_type == 'number' ? 'selected': '' }}>Number</option>
                                        <option value="select" {{ $customFieldDefinition->field_type == 'select' ? 'selected': '' }}>Select</option>
                                    </select>
                                    @error('field_type')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-end">Options (for select, comma-separated)</label>
                                <div class="col-md-6">
                                    <input type="text" name="options" value="{{ old('options', is_array($customFieldDefinition->options) ? implode(',', $customFieldDefinition->options) : $customFieldDefinition->options) }}" class="form-control @error('options') is-invalid @enderror">
                                    @error('options')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-end">Required</label>
                                <div class="col-md-6">
                                    <input type="checkbox" name="is_required" value="1" {{ $customFieldDefinition->is_required ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <input type="hidden" name="id" value="{{ $customFieldDefinition->id }}">
                                    <button class="btn btn-primary" type="submit">{{ __('Update Custom Field') }}</button>
                                    <a href="{{ route('custom-fields') }}"><button class="btn btn-secondary" type="button">Back</button></a> 
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('script')
        <script>
            $(document).ready(function () {
                $("#customFieldUpdateForm").validate({
                    errorElement:'span',
                    errorClass:'error',
                    errorPlacement: function (error, element) {
                        error.insertAfter(element);
                    },
                    rules: {
                        name:"required",
                        field_type:"required",
                    },
                    messages: {
                        name:"The name field is required.",
                        field_type:"The field type is required.",
                    },
                    submitHandler: function(form) {
                        let url = "{{ route('custom-fields-update') }}";
                        let param = new FormData(form);
                        let response = ajaxCall(url,param);                        
                        if(response.success){
                            Swal.fire({icon:'success',title:'Custom Field',text:response.message});
                        } else {
                            let errMsg = response.message || 'Failed to update custom field';
                            Swal.fire({icon:'error', title:'Custom Field', html:errMsg});
                        }
                    },
                });                
            });
        </script>
    @endpush
</x-app-layout>
