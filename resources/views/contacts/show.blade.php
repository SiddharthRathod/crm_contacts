<x-app-layout>
    @section('title',"Show Contact")
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Contact') }}</div>

                    <div class="card-body">                        
                        
                            <h5>Contact #{{ $contact['id'] }}</h5>
                            @if(!empty($contact['is_merged']))
                                <div class="alert alert-secondary">This contact was merged into contact #{{ $contact['merged_to_id'] }} and marked as merged.</div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-md-3 font-weight-bold">Name</div>
                                <div class="col-md-9">{{ $contact['name'] }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3 font-weight-bold">Email</div>
                                <div class="col-md-9">{{ $contact['email'] }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3 font-weight-bold">Phone</div>
                                <div class="col-md-9">{{ $contact['phone'] }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3 font-weight-bold">Gender</div>
                                <div class="col-md-9">{{ ucfirst($contact['gender'] ?? 'N/A') }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3 font-weight-bold">Profile Image</div>
                                <div class="col-md-9">
                                    @if ($contact['profile_image'])
                                        <img src="{{ url('storage/'.$contact['profile_image']) }}" alt="Profile Image" width="120" class="img-thumbnail" />
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 font-weight-bold">Additional File</div>
                                <div class="col-md-9">
                                    @if ($contact['additional_file'])
                                        <a href="{{ url('storage/'.$contact['additional_file']) }}" target="_blank">Download File</a>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>

                            <!-- Dynamic Custom Fields -->
                            @php
                                $customFields = $contact['custom_fields'] ?? [];
                            @endphp
                            @if(count($customFields))
                                <h5 class="mb-3">Custom Fields</h5>
                                @foreach($customFields as $key => $list)
                                    @foreach($list as $item)
                                    <div class="row mb-3">
                                        <div class="col-md-3 font-weight-bold">{{ $key }}</div>
                                        <div class="col-md-9">
                                            {{ $item['value'] }}
                                            @if(!empty($item['source_contact_id']))
                                                <span class="text-muted small">(from contact #{{ $item['source_contact_id'] }})</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                @endforeach
                            @endif
                            
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <a href="{{ route('contacts') }}"><button class="btn btn-secondary" type="button">Back</button></a> 
                                </div>
                            </div>
                                                
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
