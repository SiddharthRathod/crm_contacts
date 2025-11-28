<x-app-layout>
    @section('title', "Custom Fields")
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        {{ __('Custom Fields') }}
                        <a href="{{ route('custom-fields.add') }}" class="btn btn-sm btn-warning" style="float:right"><i class="fas fa-plus"></i>&nbsp;Add Field</a>
                    </div>
                    <div class="card-body">
                        @if($definitions->count())
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Options</th>
                                        <th>Required</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($definitions as $def)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $def->name }}</td>
                                        <td>{{ $def->field_type }}</td>
                                        <td>{{ is_array($def->options) ? implode(', ', $def->options) : $def->options }}</td>
                                        <td>{{ $def->is_required ? 'Yes' : 'No' }}</td>
                                        <td>
                                            <a href="{{ route('custom-fields.edit', $def->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('custom-fields.destroy', $def->id) }}" method="post" style="display:inline-block" onsubmit="return confirm_delete(event);">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>No custom fields defined.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('script')
        <script>
            function confirm_delete(event) {
                event.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Delete this custom field?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        event.target.submit();
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
