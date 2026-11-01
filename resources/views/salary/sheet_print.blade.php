<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Employee Salary & Payment Ledger {{ $employee ? '- ' . $employee->firstname . ' ' . $employee->lastname : '' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #fff; 
            margin: 0; 
            padding: 15px; 
            color: #222; 
        }
        
        .print-container { 
            width: 100%; 
            max-width: 1100px; 
            margin: 0 auto; 
            background: #fff; 
        }

        .no-print { 
            text-align: right; 
            margin-bottom: 15px; 
            padding: 8px 12px;
            background: #f7f7f7;
            border-bottom: 1px solid #ddd;
        }
        .btn { 
            background: #00a65a; 
            color: #fff; 
            padding: 7px 16px; 
            border: none; 
            cursor: pointer; 
            font-size: 13px; 
            font-weight: bold; 
            border-radius: 4px; 
        }
        .btn-close { 
            background: #dd4b39; 
            margin-left: 8px; 
        }

        /* Top Title Banner */
        .ledger-title-banner {
            background: #1f385c;
            color: #ffffff;
            text-align: center;
            padding: 9px 15px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        /* Employee Information Card */
        .emp-info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 13px;
            line-height: 1.8;
            padding: 0 4px;
        }
        .emp-info-col {
            width: 48%;
        }
        .emp-info-row {
            display: flex;
            margin-bottom: 2px;
        }
        .emp-info-label {
            width: 170px;
            font-weight: 600;
            color: #333;
        }
        .emp-info-val {
            flex: 1;
            font-weight: 500;
            color: #111;
        }

        /* Sheet Table */
        .table-sheet {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-bottom: 25px;
        }
        .table-sheet th {
            background: #203764;
            color: #ffffff;
            border: 1px solid #102a45;
            padding: 7px 5px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }
        .table-sheet td {
            border: 1px solid #777;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .table-sheet tr:nth-child(even):not(.row-opening):not(.row-total) {
            background: #fafafa;
        }

        /* Specific row types */
        .row-opening {
            background: #fce4d6 !important;
            font-weight: 600;
        }
        .row-opening td {
            border: 1px solid #777 !important;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .amt {
            text-align: right;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11.5px;
            white-space: nowrap;
        }
        .amt-due {
            color: #111;
        }
        .amt-advance {
            color: #c00000;
            font-weight: bold;
        }

        .row-total {
            background: #e9eef4 !important;
            font-weight: bold;
            border-top: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
        }
        .row-total td {
            padding: 7px 6px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #000 !important;
        }

        /* Signatures */
        .signatures {
            width: 100%;
            margin-top: 55px;
            border-collapse: collapse;
        }
        .signatures td {
            text-align: center;
            vertical-align: bottom;
            width: 20%;
            border: none;
            padding: 0 10px;
        }
        .sign-line {
            border-top: 1px solid #000;
            display: inline-block;
            width: 85%;
            padding-top: 5px;
            font-size: 11.5px;
            font-weight: bold;
            color: #222;
        }

        @media print {
            @page {
                size: landscape;
                margin: 8mm;
            }
            body { 
                margin: 0; 
                padding: 0; 
                background: #fff;
            }
            .no-print { 
                display: none !important; 
            }
            .ledger-title-banner {
                background: #1f385c !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .table-sheet th {
                background: #203764 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 1px solid #000 !important;
            }
            .table-sheet td {
                border: 1px solid #555 !important;
            }
            .row-opening {
                background: #fce4d6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .row-total {
                background: #e9eef4 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border-top: 2px solid #000 !important;
                border-bottom: 2px solid #000 !important;
            }
            .amt-advance {
                color: #c00000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button onclick="window.print()" class="btn"><i class="fa fa-print"></i> Print Salary Sheet</button>
        <button onclick="window.close()" class="btn btn-close"><i class="fa fa-times"></i> Close Window</button>
    </div>

    <div class="print-container">
        @php
            $display_year = date('Y');
            if (!empty($pay_month) && preg_match('/^\d{4}/', $pay_month)) {
                $display_year = substr($pay_month, 0, 4);
            } elseif (!empty($to_date)) {
                $display_year = date('Y', strtotime($to_date));
            } elseif (!empty($from_date)) {
                $display_year = date('Y', strtotime($from_date));
            } elseif (count($salaries) > 0 && !empty($salaries->last()->date) && $salaries->last()->date != '0000-00-00') {
                $display_year = date('Y', strtotime($salaries->last()->date));
            }
        @endphp

        <!-- Title Banner -->
        <div class="ledger-title-banner">
            @if($employee)
                EMPLOYEE SALARY & PAYMENT LEDGER {{ $display_year }}
            @else
                EMPLOYEE SALARY STATEMENT {{ $display_year }}
            @endif
        </div>

        @if($employee)
        <!-- Employee Details Section -->
        <div class="emp-info-grid">
            <div class="emp-info-col">
                <div class="emp-info-row">
                    <div class="emp-info-label">Employee Name:</div>
                    <div class="emp-info-val">{{ $employee->firstname }} {{ $employee->lastname }}</div>
                </div>
                <div class="emp-info-row">
                    <div class="emp-info-label">Designation:</div>
                    <div class="emp-info-val">{{ $employee->position ?: 'N/A' }}</div>
                </div>
                <div class="emp-info-row">
                    <div class="emp-info-label">Base Salary:</div>
                    <div class="emp-info-val">৳ {{ number_format((float)$employee->salary, 2) }}</div>
                </div>
            </div>
            <div class="emp-info-col">
                <div class="emp-info-row">
                    <div class="emp-info-label">Employee ID / Card:</div>
                    <div class="emp-info-val">{{ $employee->employee_id ?: 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="emp-info-row">
                    <div class="emp-info-label">Contact:</div>
                    <div class="emp-info-val">{{ $employee->contact_info ?: 'N/A' }}</div>
                </div>
                <div class="emp-info-row">
                    <div class="emp-info-label">Currency:</div>
                    <div class="emp-info-val">BDT</div>
                </div>
            </div>
        </div>
        @endif

        <table class="table-sheet">
            <thead>
                <tr>
                    <th width="4%">SL</th>
                    <th width="10%">Payment Date</th>
                    @if(!$employee)
                        <th width="14%">Employee Name</th>
                    @endif
                    <th width="10%">Pay Month</th>
                    <th width="9%">Basic Salary</th>
                    <th width="8%">Allowances</th>
                    <th width="9%">Gross Salary</th>
                    <th width="8%">Deductions</th>
                    <th width="10%">Net Payable</th>
                    <th width="10%">Paid Amount</th>
                    <th width="11%">Due / (Advance)</th>
                    <th width="9%">Voucher No</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $t_basic = 0;
                    $t_allow = 0;
                    $t_gross = 0;
                    $t_deduct = 0;
                    $t_net = 0;
                    $t_paid = 0;
                    $t_due = 0;
                    $sl = 1;
                @endphp

                {{-- 1. Brought Forward Row (when filtering by date range and previous balance exists) --}}
                @if(isset($brought_forward) && $brought_forward != 0 && $employee)
                    @php
                        $bf_val = (float)$brought_forward;
                        $t_basic += $bf_val;
                        $t_gross += $bf_val;
                        $t_net += $bf_val;
                        $t_due += $bf_val;
                    @endphp
                    <tr class="row-opening">
                        <td class="text-center"></td>
                        <td class="text-center">-</td>
                        <td class="text-center"><b>BROUGHT FORWARD</b></td>
                        <td class="amt">৳ {{ number_format($bf_val, 2) }}</td>
                        <td class="amt">৳ 0.00</td>
                        <td class="amt">৳ {{ number_format($bf_val, 2) }}</td>
                        <td class="amt">৳ 0.00</td>
                        <td class="amt">৳ {{ number_format($bf_val, 2) }}</td>
                        <td class="amt">৳ 0.00</td>
                        <td class="amt">
                            @if($bf_val > 0)
                                ৳ {{ number_format($bf_val, 2) }}
                            @elseif($bf_val < 0)
                                <span class="amt-advance">(৳ {{ number_format(abs($bf_val), 2) }})</span>
                            @else
                                ৳ 0.00
                            @endif
                        </td>
                        <td class="text-center"></td>
                    </tr>
                @endif

                {{-- 2. Salary Records --}}
                @forelse($salaries as $idx => $s)
                    @php
                        $is_opening = (strtolower($s->pay_month) == 'opening' || str_contains(strtolower($s->note ?? ''), 'opening'));
                        
                        if ($is_opening && (float)$s->main_salary == 0 && ((float)$s->due_amount > 0 || (float)$s->net_payable > 0)) {
                            // Opening Due Record
                            $basic = (float)$s->due_amount > 0 ? (float)$s->due_amount : (float)$s->net_payable;
                            $allowances = 0.0;
                            $gross = $basic;
                            $deductions = 0.0;
                            $net = $gross;
                            $paid = (float)$s->amount;
                            $due = $net - $paid;

                            // Label for Opening
                            if (str_contains(strtolower($s->note ?? ''), '2025')) {
                                $month_label = '2025 DUE';
                            } else {
                                $prev_yr = (int)$display_year - 1;
                                $month_label = $prev_yr . ' DUE';
                            }
                            $row_class = 'row-opening';
                            $row_sl = '';
                            $date_display = '';
                        } else {
                            // Regular Monthly Salary Record
                            $basic = (float)$s->main_salary > 0 ? (float)$s->main_salary : ((float)($s->employee->salary ?? 0));
                            $allowances = (float)$s->mobile_bill + (float)$s->lunch_bill + (float)$s->other_allowances + (float)$s->bonus;
                            
                            // If adv_deduct was stored as negative adjustment (bonus/addition), add to allowances
                            if ((float)$s->adv_deduct < 0) {
                                $allowances += abs((float)$s->adv_deduct);
                                $deductions = (float)$s->fine + (float)$s->other_deduction;
                            } else {
                                $deductions = (float)$s->adv_deduct + (float)$s->fine + (float)$s->other_deduction;
                            }
                            
                            $gross = $basic + $allowances;
                            $net = $gross - $deductions;
                            $paid = (float)$s->amount;
                            $due = $net - $paid;

                            // Format Month Label
                            if (preg_match('/^\d{4}-\d{2}$/', $s->pay_month)) {
                                $month_label = strtoupper(date('F', strtotime($s->pay_month . '-01')));
                            } elseif (preg_match('/^[a-zA-Z]+$/', $s->pay_month)) {
                                $month_label = strtoupper($s->pay_month);
                            } else {
                                $month_label = strtoupper($s->pay_month);
                            }

                            $row_class = '';
                            $row_sl = $sl++;
                            $date_display = (!empty($s->date) && $s->date != '0000-00-00') ? date('d-M-Y', strtotime($s->date)) : '-';
                        }

                        $t_basic += $basic;
                        $t_allow += $allowances;
                        $t_gross += $gross;
                        $t_deduct += $deductions;
                        $t_net += $net;
                        $t_paid += $paid;
                        $t_due += $due;
                    @endphp
                    <tr class="{{ $row_class }}">
                        <td class="text-center">{{ $row_sl }}</td>
                        <td class="text-center">{{ $date_display }}</td>
                        @if(!$employee)
                            <td><b>{{ $s->employee_name }}</b></td>
                        @endif
                        <td class="text-center"><b>{{ $month_label }}</b></td>
                        <td class="amt">৳ {{ number_format($basic, 2) }}</td>
                        <td class="amt">৳ {{ number_format($allowances, 2) }}</td>
                        <td class="amt">৳ {{ number_format($gross, 2) }}</td>
                        <td class="amt">৳ {{ number_format($deductions, 2) }}</td>
                        <td class="amt">৳ {{ number_format($net, 2) }}</td>
                        <td class="amt">৳ {{ number_format($paid, 2) }}</td>
                        <td class="amt">
                            @if($due > 0)
                                ৳ {{ number_format($due, 2) }}
                            @elseif($due < 0)
                                <span class="amt-advance">(৳ {{ number_format(abs($due), 2) }})</span>
                            @else
                                ৳ 0.00
                            @endif
                        </td>
                        <td class="text-center">{{ $s->voucher_no ?: '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $employee ? 11 : 12 }}" class="text-center" style="padding: 25px; color: #777;">
                            No salary records found for this selection.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="row-total">
                    <td colspan="{{ $employee ? 3 : 4 }}" class="text-center">TOTAL</td>
                    <td class="amt">৳ {{ number_format($t_basic, 2) }}</td>
                    <td class="amt">৳ {{ number_format($t_allow, 2) }}</td>
                    <td class="amt">৳ {{ number_format($t_gross, 2) }}</td>
                    <td class="amt">৳ {{ number_format($t_deduct, 2) }}</td>
                    <td class="amt">৳ {{ number_format($t_net, 2) }}</td>
                    <td class="amt">৳ {{ number_format($t_paid, 2) }}</td>
                    <td class="amt">
                        @if($t_due > 0)
                            ৳ {{ number_format($t_due, 2) }}
                        @elseif($t_due < 0)
                            <span class="amt-advance">(৳ {{ number_format(abs($t_due), 2) }})</span>
                        @else
                            ৳ 0.00
                        @endif
                    </td>
                    <td class="text-center"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures Section -->
        <table class="signatures">
            <tr>
                <td><span class="sign-line">Received By</span></td>
                <td><span class="sign-line">Accounts Manager</span></td>
                <td><span class="sign-line">Checked By</span></td>
                <td><span class="sign-line">Audit By</span></td>
                <td><span class="sign-line">Approved By</span></td>
            </tr>
        </table>
    </div>
</body>
</html>
