<x-app-layout>
    @section('title',"Contacts")
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                            {{ __('Contacts') }}                            
                            <a href="{{ route('contact.add') }}" class="btn btn-sm btn-warning" style="float:right"><i class="fas fa-plus"></i>&nbsp;Add Contact</a>
                    </div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if(count($contacts) > 0) 
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">Actions</th>                                    
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contacts as $contact)
                                        <tr>
                                            <th scope="row">{{ $loop->iteration }}</th>
                                            <td>{{ $contact['name'] }}</td>
                                            <td>{{ $contact['email'] }}</td>
                                            <td>{{ $contact['phone'] }}</td>                                            
                                            <td>
                                                <a href="{{ route('contact.show', $contact['id']) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>                                                
                                                <a href="{{ route('contact.edit', $contact['id']) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                                <form action="{{ route('contact.destroy', $contact['id']) }}" method="post" style="display:inline-block" onsubmit="return confirm_delete(event);">
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
                            {{ __('No data found') }}    
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
                    text: 'Delete this contact?',
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
