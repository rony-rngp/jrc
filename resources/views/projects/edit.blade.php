@extends('layouts.master')

@section('title', 'Edit Project - JRC ERP')
@section('page_title', 'Edit Project Details')

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <a href="{{ route('projects.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('projects.update', $project->id) }}">
                @csrf
                @method('PUT')
                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Client / Company *</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="company_name" required>
                                @foreach($clients as $client)
                                    <option value="{{ $client->company_name }}" {{ $project->company_name == $client->company_name ? 'selected' : '' }}>{{ $client->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Project Name *</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="project_name" value="{{ $project->project_name }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Estimated Value</label>
                        <div class="col-sm-9">
                            <input type="number" step="any" class="form-control" name="estimated_value" value="{{ $project->estimated_value }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Address</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="address">{{ $project->address }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Entry Date</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="date" value="{{ $project->date }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Project Status</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="project_status">
                                <option value="Runing" {{ in_array($project->project_status, ['Runing', 'Running']) ? 'selected' : '' }}>Runing</option>
                                <option value="Close" {{ $project->project_status == 'Close' ? 'selected' : '' }}>Close</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Note</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="note">{{ $project->note }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
