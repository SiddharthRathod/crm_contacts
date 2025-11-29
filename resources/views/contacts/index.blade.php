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
                                    <div class="mb-3">
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                            <input id="filter_name" class="form-control" placeholder="Filter by name" value="{{ request('name') }}" />
                                        </div>
                                            <div class="col-md-3">
                                            <input id="filter_email" class="form-control" placeholder="Filter by email" value="{{ request('email') }}" />
                                        </div>
                                            <div class="col-md-2">
                                            <select id="filter_gender" class="form-select">
                                                    <option value="">All genders</option>
                                                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <button id="filter_btn" class="btn btn-primary">Search</button>
                                                <button id="reset_btn" class="btn btn-secondary">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <div id="contacts_table">
                                            @include('contacts._rows')
                                        </div>
                                    </div>
                                    <!-- Merge Modal-->
                                    <div class="modal fade" id="mergeModal" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Merge Contacts</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="mergeForm">
                                                            <input type="hidden" name="secondary_id" id="secondary_id" />
                                                            <div class="mb-3">
                                                                    <label for="master_id" class="form-label">Select Master Contact</label>
                                                                    <select id="master_id" name="master_id" class="form-select">
                                                                            <option value="">-- choose master contact --</option>
                                                                            @foreach($unMergedContacts as $c)
                                                                                <option value="{{ $c->id }}" @if($c->is_merged) disabled @endif>#{{ $c->id }} - {{ $c->name }} ({{ $c->email }})</option>
                                                                            @endforeach
                                                                    </select>
                                                            </div>
                                                            <div class="form-check mb-3">
                                                                <input class="form-check-input" type="checkbox" value="1" id="append_values" checked name="append_values">
                                                                <label class="form-check-label" for="append_values">Append differing values as additional fields (don’t overwrite master)</label>
                                                            </div>
                                                    </form>
                                                    <div id="merge_preview"></div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="button" class="btn btn-danger" id="confirm_merge">Confirm Merge</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
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
            // Merge feature
            document.addEventListener('DOMContentLoaded', function() {
                // Handler for merge buttons
                document.querySelectorAll('.btn-merge').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const secondaryId = this.dataset.id;
                        document.getElementById('secondary_id').value = secondaryId;
                        // Reset master selection and preview
                        const masterSelect = document.getElementById('master_id');
                        // re-enable all options then disable the selected secondary
                        masterSelect.querySelectorAll('option').forEach(o=>o.disabled=false);
                        const opt = masterSelect.querySelector('option[value="'+secondaryId+'"]');
                        if (opt) opt.disabled = true;
                        masterSelect.value = '';
                        document.getElementById('merge_preview').innerHTML = '<p>Select a master contact to preview the merge.</p>';
                        var mergeModal = new bootstrap.Modal(document.getElementById('mergeModal'));
                        mergeModal.show();
                    });
                });

                // Preview when a master is selected
                const masterSelectEl = document.getElementById('master_id');
                if (masterSelectEl) {
                    masterSelectEl.addEventListener('change', async function() {
                        const masterId = this.value;
                        const secondaryId = document.getElementById('secondary_id').value;
                        if (!masterId || !secondaryId) return;
                        // Fetch via the show Ajax endpoint
                        const res1 = await fetch('/contact/' + masterId + '/show', { headers: {'X-Requested-With':'XMLHttpRequest'} });
                        const res2 = await fetch('/contact/' + secondaryId + '/show', { headers: {'X-Requested-With':'XMLHttpRequest'} });
                        const data1 = await res1.json();
                        const data2 = await res2.json();
                        const master = data1.contact;
                        const secondary = data2.contact;
                        let html = `<h6>Master: ${master.name} (ID #${master.id})</h6>`;
                        html += `<h6>Secondary: ${secondary.name} (ID #${secondary.id})</h6>`;
                        html += '<div><strong>Differences:</strong></div>';
                        const fields = ['email','phone','gender'];
                        fields.forEach(f => {
                            const mval = master[f] || '';
                            const sval = secondary[f] || '';
                            if (mval == sval) return;
                            html += `<div>${f}: <br/> Master: ${mval || '<em>empty</em>'} <br/> Secondary: ${sval || '<em>empty</em>'}</div><hr/>`;
                        });
                        html += '<div><strong>Custom Fields present in secondary that master lacks or differ:</strong><ul>';
                        const mCustom = master.custom_fields || {};
                        const sCustom = secondary.custom_fields || {};
                        for (const k in sCustom) {
                            if (!mCustom[k]) html += `<li>${k}: ${sCustom[k]}</li>`;
                            else if (mCustom[k] != sCustom[k]) html += `<li>${k}: Master=${mCustom[k]} | Secondary=${sCustom[k]}</li>`;
                        }
                        html += '</ul></div>';
                        document.getElementById('merge_preview').innerHTML = html;
                    });
                }

                // Confirm merge action
                document.getElementById('confirm_merge').addEventListener('click', async function() {
                    const fd = new FormData(document.getElementById('mergeForm'));
                    const url = '{{ route('contact.merge') }}';
                    const res = await fetch(url, { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}, body: fd });
                    const data = await res.json();
                    if (data.success) {
                        // Reload the page to reflect merge
                        location.reload();
                    } else {
                        alert(data.message || 'Merge failed');
                    }
                });
            });

                // Filtering: AJAX search and pagination
                async function searchContacts(page = 1) {
                    const name = document.getElementById('filter_name').value;
                    const email = document.getElementById('filter_email').value;
                    const gender = document.getElementById('filter_gender').value;
                    const params = new URLSearchParams({name, email, gender, page});
                    const res = await fetch('/contacts?' + params.toString(), {headers: {'X-Requested-With':'XMLHttpRequest'}});
                    if (res.ok) {
                        const html = await res.text();
                        document.getElementById('contacts_table').innerHTML = html;
                        hookPaginationLinks();
                        hookMergeButtons();
                    }
                }

                // Hook the filter controls
                document.getElementById('filter_btn').addEventListener('click', function(e){
                    e.preventDefault();
                    searchContacts();
                });
                document.getElementById('reset_btn').addEventListener('click', function(e){
                    e.preventDefault();
                    document.getElementById('filter_name').value = '';
                    document.getElementById('filter_email').value = '';
                    document.getElementById('filter_gender').value = '';
                    searchContacts();
                });

                // Trigger search on Enter key in fields
                ['filter_name','filter_email'].forEach(id=>{
                    const el = document.getElementById(id);
                    if (el) el.addEventListener('keypress', function(e){ if (e.key === 'Enter') { e.preventDefault(); searchContacts(); } });
                });

                // Pagination links in AJAX loaded content
                function hookPaginationLinks() {
                    document.querySelectorAll('#contacts_table .pagination a').forEach(link => {
                        link.addEventListener('click', function(e){
                            e.preventDefault();
                            const url = new URL(this.href);
                            const page = url.searchParams.get('page') || 1;
                            searchContacts(page);
                        });
                    });
                }

                // Hook merge buttons for AJAX loaded content
                function hookMergeButtons(){
                    document.querySelectorAll('#contacts_table .btn-merge').forEach(function(btn) {
                        if (btn.dataset.hooked) return; // only hook once
                        btn.dataset.hooked = true;
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            const secondaryId = this.dataset.id;
                            document.getElementById('secondary_id').value = secondaryId;
                            const masterSelect = document.getElementById('master_id');
                            masterSelect.querySelectorAll('option').forEach(o=>o.disabled=false);
                            const opt = masterSelect.querySelector('option[value="'+secondaryId+'"]');
                            if (opt) opt.disabled = true;
                            masterSelect.value = '';
                            document.getElementById('merge_preview').innerHTML = '<p>Select a master contact to preview the merge.</p>';
                            var mergeModal = new bootstrap.Modal(document.getElementById('mergeModal'));
                            mergeModal.show();
                        });
                    });
                }

                // Initially hook pagination and merge buttons
                hookPaginationLinks();
                hookMergeButtons();
        </script>
    @endpush
</x-app-layout>
