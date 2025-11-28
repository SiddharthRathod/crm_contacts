<x-app-layout>
    @section('title',"Edit Contact")
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Edit Contact') }}</div>

                    <div class="card-body">                        
                        <form method="POST" action="javascript:void(0)" enctype="multipart/form-data" id="contactEditForm">
                            @csrf
                            <input type="hidden" name="id" value="{{ $contact['id'] ?? '' }}" />
                            <!-- Standard Contact fields -->
                            <div class="row mb-3">
                                <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}<span class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $contact['name'] ?? '') }}"  autocomplete="name" autofocus >
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email') }}<span class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $contact['email'] ?? '') }}">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="phone" class="col-md-4 col-form-label text-md-end">{{ __('Phone') }}<span class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $contact['phone'] ?? '') }}">
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label class="col-md-4 col-form-label text-md-end">{{ __('Gender') }}<span class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" {{ (old('gender',$contact['gender'] ?? '') == 'male') ? 'checked':'' }} aria-describedby="gender-error">
                                        <label class="form-check-label" for="gender_male">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female" {{ (old('gender',$contact['gender'] ?? '') == 'female') ? 'checked':'' }}>
                                        <label class="form-check-label" for="gender_female">Female</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_other" value="other" {{ (old('gender',$contact['gender'] ?? '') == 'other') ? 'checked':'' }}>
                                        <label class="form-check-label" for="gender_other">Other</label>
                                    </div>
                                    <span id="gender-error" class="error">@error('gender') {{ $message }} @enderror</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="profile_image" class="col-md-4 col-form-label text-md-end">{{ __('Profile Image') }}</label>
                                <div class="col-md-6">
                                    @if (!empty($contact['profile_image']))
                                        <div style="margin-bottom:8px">
                                            <img src="{{ asset('storage/'.$contact['profile_image']) }}" width="80" class="img-thumbnail" />
                                        </div>
                                    @endif
                                    <input id="profile_image" type="file" class="form-control @error('profile_image') is-invalid @enderror" name="profile_image" accept="image/*">
                                    @error('profile_image')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="additional_file" class="col-md-4 col-form-label text-md-end">{{ __('Additional File') }}</label>
                                <div class="col-md-6">
                                    @if (!empty($contact['additional_file']))
                                        <div style="margin-bottom:8px">
                                            <a href="{{ asset('storage/'.$contact['additional_file']) }}" target="_blank">Current File</a>
                                        </div>
                                    @endif
                                    <input id="additional_file" type="file" class="form-control @error('additional_file') is-invalid @enderror" name="additional_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                                    @error('additional_file')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Dynamic Custom Fields -->
                            @php
                                $customDefinitions = $customDefinitions ?? \App\Models\CustomFieldDefinition::all();
                                $existingCustom = $contact['custom_fields'] ?? [];
                            @endphp
                            @if($customDefinitions->count())
                                <hr />
                                <h5 class="mb-3">Custom Fields</h5>
                                @foreach($customDefinitions as $def)
                                    @php
                                        // controller returns custom fields keyed by name (or id), so check both
                                        $valueKey = $def->name;
                                        $val = $existingCustom[$valueKey] ?? ($existingCustom[$def->id] ?? old('custom_fields.'.$def->id));
                                    @endphp
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-form-label text-md-end">{{ $def->name }}@if($def->is_required)<span class="text-danger">*</span>@endif</label>
                                        <div class="col-md-6">
                                            @if($def->field_type === 'select')
                                                @php $options = is_array($def->options) ? $def->options : explode(',', $def->options ?? ''); @endphp
                                                <select name="custom_fields[{{ $def->id }}]" class="form-select @error('custom_fields.'. $def->id) is-invalid @enderror" @if($def->is_required) required @endif>
                                                    <option value="">-- select --</option>
                                                    @foreach($options as $opt)
                                                        <option value="{{ trim($opt) }}" {{ ($val == trim($opt)) ? 'selected' : '' }}>{{ trim($opt) }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($def->field_type === 'date')
                                                <input type="date" name="custom_fields[{{ $def->id }}]" value="{{ $val }}" class="form-control @error('custom_fields.'. $def->id) is-invalid @enderror" @if($def->is_required) required @endif>
                                            @else
                                                <input type="text" name="custom_fields[{{ $def->id }}]" value="{{ $val }}" class="form-control @error('custom_fields.'. $def->id) is-invalid @enderror" @if($def->is_required) required @endif>
                                            @endif
                                            @error('custom_fields.'. $def->id)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">{{ __('Update Contact') }}</button>
                                    <a href="{{ route('contacts') }}"><button type="button" class="btn btn-secondary">Back</button></a>
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
                $("#contactEditForm").validate({
                    errorElement:'span',
                    errorClass:'error',
                    errorPlacement: function (error, element) {
                        if (element.attr('name') === 'gender') {
                            error.insertAfter($('#gender_other').closest('.form-check-inline'));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    highlight: function(element, errorClass) {
                        $(element).addClass(errorClass);
                    },
                    unhighlight: function(element, errorClass) {
                        $(element).removeClass(errorClass);
                    },
                    rules: {
                        name:"required",
                        email: {
                            required: true,
                            email: true,
                            remote: {
                                url: "{{ route('check-contact-email') }}",
                                type: "post",
                                data: {
                                    '_token': '{{ csrf_token() }}',
                                    'contact_id': function() { return $("input[name='id']").val(); }
                                }
                            },
                        },
                        phone:"required",
                        gender:"required",
                    },
                    messages: {
                        name:"The full name field is required.",
                        email: {
                            required: "The email field is required.",
                            email: "Please enter a valid email address",
                            remote: "This email is already registered, Please try with another email",
                        },
                        phone:"The phone field is required.",
                        gender:"The gender field is required.",                        
                    },
                    submitHandler: function(form) {
                        let url = "{{ route('update.contact') }}"; // existing route in app
                        let param = new FormData(form);
                        param.set('id', $(form).find("input[name='id']").val());
                        let response = ajaxCall(url,param);
                        if(response.success){
                            Swal.fire({icon:'success',title:'Contact',text:response.message});
                        } else {
                            let errMsg = response.message || 'Failed to update contact';
                            Swal.fire({icon:'error', title:'Contact', html:errMsg});
                        }
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
