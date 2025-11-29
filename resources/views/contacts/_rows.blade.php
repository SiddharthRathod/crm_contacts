<table border="1" width="100%" cellpadding="5" cellspacing="0" class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Gender</th>
            <th>Profile</th>
            <th>Files</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($contacts as $contact)
        <tr data-contact='@json(array_merge($contact->toArray(), ['custom_fields' => $contact->custom_fields]))'>
            <td>{{ $contact->id }}</td>
            <td>{{ $contact->name }}</td>
            <td>{{ $contact->email }}</td>
            <td>{{ $contact->phone }}</td>
            <td>{{ $contact->gender }}</td>
            <td>@if($contact->profile_image)<img src="{{ asset('storage/'.$contact->profile_image) }}" width="50" />@endif</td>
            <td>@if($contact->additional_file)<a href="{{ asset('storage/'.$contact->additional_file) }}" target="_blank">File</a>@endif</td>
            <td>
                <a href="{{ route('contact.show', $contact['id']) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>                                                
                <a href="{{ route('contact.edit', $contact['id']) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                <form action="{{ route('contact.destroy', $contact['id']) }}" method="post" style="display:inline-block" onsubmit="return confirm_delete(event);">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger" type="submit"><i class="fas fa-trash"></i></button>
                </form>
                <button class="merge-contact btn-merge btn btn-sm btn-primary" data-id="{{ $contact->id }}" data-name="{{ $contact->name }}" @if($contact->is_merged) disabled @endif><i class="fas fa-sync"></i></button>
                @if($contact->is_merged)
                    <span class="badge bg-secondary">Merged to #{{ $contact->merged_to_id }}</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="pagination">
    {{ $contacts->links() }}
</div>
