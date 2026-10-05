@extends('layouts.master')

@section('title', 'JR || Corporation - Employee List')
@section('page_title', 'Employee List')

@section('breadcrumb')
<li>Employee Desk</li>
<li class="active">Employee List</li>
@endsection

@section('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Add New Employee</a>
    </div>
    <div class="box-body table-responsive">
        <table id="example1" class="table table-bordered table-striped">
            <thead style="background-color: #3c8dbc; color: white;">
                <tr>
                    <th width="5%">Photo</th>
                    <th width="15%">Emp ID</th>
                    <th width="20%">Name</th>
                    <th width="15%">Designation</th>
                    <th width="15%">Gross Salary</th>
                    <th width="30%">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $emp)
                    @php
                        $photo = !empty($emp->photo) ? asset('images/' . $emp->photo) : asset('images/profile.jpg');
                        $gross = (float)$emp->salary + (float)($emp->mobile_bill ?? 0) + (float)($emp->lunch_bill ?? 0) + (float)($emp->other_allowances ?? 0);
                    @endphp
                    <tr>
                        <td><img src="{{ $photo }}" width="40px" height="40px" style="border-radius:50%; object-fit:cover;"></td>
                        <td>{{ $emp->employee_id }}</td>
                        <td><b>{{ $emp->firstname }} {{ $emp->lastname }}</b></td>
                        <td>{{ $emp->position }}</td>
                        <td style="color:#00a65a; font-weight:bold;">৳ {{ number_format($gross, 2) }}</td>
                        <td>
                            <a href="{{ route('employees.show', $emp->id) }}" target="_blank" class="btn btn-info btn-sm btn-flat"><i class="fa fa-print"></i> Profile</a>
                            <a href="{{ route('employees.edit', $emp->id) }}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                            <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this employee?');">
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
@endsection
