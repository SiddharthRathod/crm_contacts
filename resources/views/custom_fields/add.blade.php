<x-app-layout>
    @section('title', 'Add Custom Field')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Add Custom Field') }}</div>
                    <div class="card-body">
                        <form action="javascript:void(0)" method="post" id="customFieldForm">
                            @csrf
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-end">Name<span class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control">    
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-end">Field Type</label>
                                <div class="col-md-6">
                                    <select name="field_type" class="form-select">
                                        <option value="text">Text</option>
                                        <option value="date">Date</option>
                                        <option value="number">Number</option>
                                        <option value="select">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-end">Options (for select, comma-separated)</label>
                                <div class="col-md-6">
                                    <input type="text" name="options" value="{{ old('options') }}" class="form-control" placeholder="e.g. Option1,Option2,Option3">                                    
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-end">Is Required?</label>
                                <div class="col-md-6">
                                    <input type="checkbox" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="row mb-12">
                                <div class="col-md-6 offset-md-4">
                                    <button class="btn btn-primary" type="submit">{{ __('Add Custom Field') }}</button>
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
                $("#customFieldForm").validate({
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
                        let url = "{{ route('custom-fields.store') }}";
                        let param = new FormData(form);
                        let response = ajaxCall(url,param);                        
                        if(response.success){
                            $("#customFieldForm")[0].reset();
                            Swal.fire({icon:'success',title:'Custom Field',text:response.message});
                        } else {
                            let errMsg = response.message || 'Failed to add custom field';
                            Swal.fire({icon:'error', title:'Custom Field', html:errMsg});
                        }
                    },
                });                
            });
        </script>
    @endpush
</x-app-layout>
