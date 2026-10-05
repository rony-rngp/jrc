<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Profile - {{ $employee->firstname }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; -moz-box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; margin: 0; padding: 0; color: #333; }
        
        .page-container { width: 210mm; min-height: 297mm; margin: 20px auto; background: #fff; padding: 15mm 20mm; position: relative; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .logo-container { margin: -15mm -20mm 10mm -20mm !important; border-radius: 0 !important; }
        
        .header-line { border-bottom: 2px solid #3c8dbc; margin-bottom: 25px; text-align: center; padding-bottom: 5px; }
        .profile-title { display: inline-block; background: #333; color: #fff; padding: 4px 25px; font-size: 16px; font-weight: bold; text-transform: uppercase; border-radius: 3px; }
        
        .profile-header { display: flex; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 25px; }
        .photo-box img { width: 130px; height: 130px; border-radius: 8px; border: 3px solid #3c8dbc; object-fit: cover; }
        .name-box { flex: 1; padding-left: 30px; }
        .name-box h1 { margin: 0; font-size: 28px; color: #2c3e50; text-transform: uppercase; }
        .name-box p { margin: 5px 0 0 0; font-size: 18px; color: #3c8dbc; font-weight: 600; }
        .emp-id-tag { display: inline-block; background: #f0f7ff; color: #3c8dbc; padding: 3px 12px; border-radius: 15px; font-size: 13px; margin-top: 10px; border: 1px solid #d0e3ff; font-weight: bold; }
        
        .section-header { font-size: 14px; font-weight: bold; color: #333; text-transform: uppercase; margin: 25px 0 15px 0; border-left: 5px solid #3c8dbc; padding-left: 12px; background: #f8f9fa; line-height: 30px; }
        
        .data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 40px; }
        .data-row { border-bottom: 1px solid #f1f1f1; padding: 8px 0; display: flex; justify-content: space-between; }
        .label-text { color: #777; font-size: 13px; }
        .value-text { color: #333; font-size: 14px; font-weight: 600; text-align: left; }
        
        .salary-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        .salary-table td { padding: 10px; border: 1px solid #ddd; }
        .bg-light { background: #f9f9f9; width: 45%; color: #666; }
        .gross-row { background: #3c8dbc !important; color: #fff !important; font-weight: bold; }
        
        .footer { position: absolute; bottom: 15mm; width: 170mm; text-align: center; border-top: 1px solid #eee; padding-top: 15px; font-size: 11px; color: #999; }
        
        .no-print { text-align: center; padding: 20px; background: #333; }
        .btn { background: #00a65a; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; font-size: 15px; }
        
        @media print { 
            .no-print { display: none !important; } 
            .page-container { margin: 0; box-shadow: none; border: none; } 
            @page { size: A4; margin: 0; } 
            body { background: #fff; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button onclick="window.print()" class="btn"><i class="fa fa-print"></i> PRINT PROFILE</button>
        <button onclick="window.close()" class="btn" style="background:#d9534f; margin-left:10px;">CLOSE</button>
    </div>
    
    <div class="page-container">
        <div class="header-logo">
            @include('layouts.voucher_logo')
        </div>
        <div class="header-line">
            <div class="profile-title">Employee Information Sheet</div>
        </div>
        
        @php
            $photo = (!empty($employee->photo)) ? asset('images/' . $employee->photo) : asset('images/profile.jpg');
            $basic = (float)$employee->salary;
            $mobile = (float)($employee->mobile_bill ?? 0);
            $lunch = (float)($employee->lunch_bill ?? 0);
            $other = (float)($employee->other_allowances ?? 0);
            $gross = $basic + $mobile + $lunch + $other;
        @endphp

        <div class="profile-header">
            <div class="photo-box"><img src="{{ $photo }}" alt="Photo" onerror="this.src='{{ asset('images/profile.jpg') }}'"></div>
            <div class="name-box">
                <h1>{{ $employee->firstname }} {{ $employee->lastname }}</h1>
                <p>{{ $employee->position }}</p>
                <span class="emp-id-tag">EMP CODE: {{ $employee->employee_id }}</span>
            </div>
        </div>

        <div class="section-header">Personal Information</div>
        <div class="data-grid">
            <div class="data-row"><span class="label-text">Contact Number</span><span class="value-text">{{ $employee->contact_info }}</span></div>
            <div class="data-row"><span class="label-text">NID / Passport</span><span class="value-text">{{ !empty($employee->emp_nid) ? $employee->emp_nid : 'N/A' }}</span></div>
            <div class="data-row"><span class="label-text">Date of Birth</span><span class="value-text">{{ (!empty($employee->birthdate) && $employee->birthdate != '0000-00-00') ? date('d M, Y', strtotime($employee->birthdate)) : 'N/A' }}</span></div>
            <div class="data-row"><span class="label-text">Gender</span><span class="value-text">{{ $employee->gender }}</span></div>
        </div>
        <div class="data-row" style="margin-top:10px;">
            <span class="label-text">Residential Address:</span>
            <span class="value-text" style="width: 75%;">{{ $employee->address }}</span>
        </div>

        <div class="section-header">Employment Details</div>
        <div class="data-grid">
            <div class="data-row"><span class="label-text">Employment Type</span><span class="value-text">{{ $employee->empType }}</span></div>
            <div class="data-row"><span class="label-text">Joining Date</span><span class="value-text">{{ (!empty($employee->created_on) && $employee->created_on != '0000-00-00') ? date('d M, Y', strtotime($employee->created_on)) : 'N/A' }}</span></div>
        </div>

        <div class="section-header">Salary Structure (Monthly)</div>
        <table class="salary-table">
            <tr>
                <td class="bg-light">Basic Salary</td>
                <td style="text-align:right;">{{ number_format($basic, 2) }}</td>
            </tr>
            <tr>
                <td class="bg-light">Mobile Bill</td>
                <td style="text-align:right;">{{ number_format($mobile, 2) }}</td>
            </tr>
            <tr>
                <td class="bg-light">Lunch Bill</td>
                <td style="text-align:right;">{{ number_format($lunch, 2) }}</td>
            </tr>
            <tr>
                <td class="bg-light">Other Allowances</td>
                <td style="text-align:right;">{{ number_format($other, 2) }}</td>
            </tr>
            <tr class="gross-row">
                <td>TOTAL GROSS PAYABLE</td>
                <td style="text-align:right;">{{ number_format($gross, 2) }}</td>
            </tr>
        </table>

        <div class="footer">
            <p>Computer Generated Profile - Verified by JR CORPORATION Management System</p>
            <p>Printed on: {{ date('d M, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
