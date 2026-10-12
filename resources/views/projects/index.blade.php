@extends('layouts.master')

@section('title', 'JR || Corporation - Project List')
@section('page_title', 'Project List')

@section('breadcrumb')
<li>Project</li>
<li class="active">Project List</li>
@endsection

@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header with-border">
          <a href="{{ route('projects.index') }}" class="btn btn-success"><i class="fa fa-refresh"></i> Refresh </a>
          <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Add New Project</a>
      </div>
      <div class="box-body table-responsive">
        <table id="example1" class="table table-bordered">
          <thead>
            <th>ID</th>
            <th>Company Name</th>
            <th>Project Name</th>
            <th>Address</th>
            <th>Date</th>
            <th>Note</th>
            <th>Estimated Value</th>
            <th>Status</th>
            <th width="200">Tools</th>
          </thead>
          <tbody>
            @foreach($projects as $project)
              <tr>        
                <td>{{ $project->id }}</td>
                <td>{{ $project->company_name }}</td>
                <td>{{ $project->project_name }}</td>
                <td>{{ $project->address }}</td>
                <td>{{ $project->date }}</td>
                <td>{{ $project->note }}</td>
                <td>{{ number_format((float)$project->estimated_value, 2) }}</td>
                <td>
                  <span class="label {{ $project->project_status == 'Complete' ? 'label-success' : 'label-primary' }}">
                    {{ $project->project_status }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('projects.show', $project->id) }}" class="btn btn-info btn-sm btn-flat"><i class="fa fa-eye"></i> View</a>
                  <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                  <form action="{{ route('projects.destroy', $project->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this project?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Add Project</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('projects.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="company_name" class="col-sm-3 control-label">Company Name</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="company_name" id="company_name" required>
                                <option value="" selected>- Select Client Name -</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->company_name }}">{{ $client->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>              
                    <div class="form-group">
                        <label for="project_name" class="col-sm-3 control-label">Project Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="project_name" name="project_name" onkeyup="checkProjectName()" required>
                            <span id="name_status" style="margin-top: 5px; display: block;"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="estimated_value" class="col-sm-3 control-label">Estimated Value</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="estimated_value" name="estimated_value" placeholder="Enter Amount" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary btn-flat" id="save_btn" name="add"><i class="fa fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkProjectName() {
    var name = $('#project_name').val().trim();
    var status = $('#name_status');
    var saveBtn = $('#save_btn');

    if(name.length > 2) {
        status.html('<i class="fa fa-spinner fa-spin"></i> Checking...');
        
        $.ajax({
            url: "{{ route('projects.check-name') }}",
            method: "POST",
            data: { 
                _token: "{{ csrf_token() }}",
                project_name: name 
            },
            success: function(response) {
                var res = '';
                if (typeof response === 'string') {
                    res = response.trim();
                } else if (response && response.status) {
                    res = String(response.status).trim();
                }

                if(res === "exists") {
                    status.html('<span style="color: red;"><i class="fa fa-warning"></i> This project name already exists.</span>');
                    saveBtn.prop('disabled', true);
                } else {
                    status.html('<span style="color: green;"><i class="fa fa-check"></i> The project name can be used.</span>');
                    saveBtn.prop('disabled', false);
                }
            },
            error: function() {
                status.html('<span style="color: orange;"><i class="fa fa-warning"></i> Error checking name!</span>');
                saveBtn.prop('disabled', false);
            }
        });
    } else {
        status.html("");
        saveBtn.prop('disabled', false);
    }
}
</script>
@endpush
