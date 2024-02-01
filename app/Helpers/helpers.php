<?php
// Needs
use App\Course;
use App\BiddingProject;

function authCheck()
{
    return auth()->user();
}

function companyLogo()
{
    if (auth()->user() && auth()->user()->company && auth()->user()->company->Logo != null) {
        return auth()->user()->company->Logo;
    }

    if (request()->segment(1) != '') {
        // Initialize
        $type           = current(explode('-', request()->segment(1)));
        $remove_type    = str_replace($type.'-', '', request()->segment(1));
        $company_name   = str_replace('-', ' ', $remove_type);
        $company        = \App\Company::where('Name', 'like', '%'. $company_name . '%')->first();
        
        if ($company && $company->Logo) {
            return $company->Logo;
        }
    }

    return asset('img/ruang-ajar-logo.png');
}


function guestCompanyColor()
{

    if (!auth()->check()) {
        if (request()->segment(1) != '') {

            $type =  current(explode('-', request()->segment(1)));
            $remove_type = str_replace($type.'-', '', request()->segment(1));
            $company_name = str_replace('-', ' ', $remove_type);
            $company = \App\Company::where('Name', 'like', '%'. $company_name . '%')->where('type', $type)->first();
            if ($company && $company->Color) {
                return $company->Color;
            }
        }
        return '#62DDBD';
    }
}

function coursePeriode($periode)
{
    switch ($periode) {
        case '1':
            $value = 'Minggu';
            break;
        case '2':
            $value = 'Bulan';
            break;
        case '3':
            $value = 'Tahun';
        break;

        default:
            $value = 'lifetime';
            break;
    }

    return $value;
}

function expiredDate($periodeType, $periode)
{
    switch ($periodeType) {
        case '1':
            // Initialize
            $value = date('Y-m-d 23:59:59', strtotime('+'.($periode * 7).' day'));
            break;
        case '2':
            // Initialize
            $value = date('Y-m-d 23:59:59', strtotime('+'.($periode).' month'));
            break;
        case '3':
            // Initialize
            $value = date('Y-m-d 23:59:59', strtotime('+'.($periode).' year'));
        break;

        default:
            $value = 'lifetime';
            break;
    }

    return $value;
}

function rupiah($rp)
{
    $val = "Rp." . number_format($rp, 0, "", ".") . "";
    
    return $val;
}

function statusTransaction($status)
{
    switch ($status) {
        case '0':
            // Initialize
            $value = 'Menunggu Transfer';
            break;
        case '1':
            // Initialize
            $value = 'Dibayar';
            break;
        case '2': 
            // Initialize
            $value = 'Expired';
            break;
        default:
            $value = 'Menunggu Transfer';
            break;
    }

    return $value;
}

function fileTypes($fileType)
{
    switch ($fileType) {
        case '1':
            // Initialize
            $value = 'Dokumen';
            break;
        case '2':
            // Initialize
            $value = 'Video';
            break;
        case '3':
            // Initialize
            $value = 'image';
            break;
        default:
            $value = 'Video';
            break;
    }

    return $value;
}

function paymentType($payment)
{
    switch ($payment) {
        case '1':
            // Initialize
            $value = 'Bank Transfer';
            break;
        case '2':
            // Initialize
            $value = 'E-wallet';
            break;
        case '3':
            // Initialize
            $value = 'Saldo';
            break;
        case '4':
            // Initialize
            $value = 'Saldo dan Bank Transfer';
            break;
        case '5':
            // Initialize
            $value = 'Saldo dan E-wallet';
            break;
        case '6':
            // Initialize
            $value = 'Tunai';
            break;
        case '7':
            // Initialize
            $value = 'Debit';
            break;
        case '8':
            // Initialize
            $value = 'Credit';
            break;
        case '9':
            // Initialize
            $value = 'Uang Muka';
            break;
        case '10':
            // Initialize
            $value = 'Uang Termin';
            break;
        default:
            $value = $payment;
            break;
    }

    return $value;
}

function courseCategory($category)
{
    switch ($category) {
        case '0':
            // Initialize
            $value = 'Produk';
            break;
        case '1':
            // Initialize
            $value = 'Jasa';
            break;
        default:
            $value = 'Produk';
            break;
    }

    return $value;
}

function statusApproveInstitution($status)
{
    switch ($status) {
        case '0':
            // Initialize
            $value = 'Waiting Approve';
            break;
        case '1':
            // Initialize
            $value = 'Approved';
            break;
        default:
            $value = 'Rejected';
            break;
    }

    return $value;
}

function terminInterval($interval)
{
    /*
        Notes :
        1 = Annual, 2 = Monthly, 3 = Once Four Months, 4 = Weekly
        1 = Annual, 2 = Monthly, 3 = 1,5 bulanan, 4 = 2 bulanan, 5 = 3 bulanan, 6 = Once Four Months, 7 = 5 bulanan, 8 = 6 bulanan, 9 = Weekly
     */
    
    switch ($interval) {
        case '1':
            // Initialize
            $value = 1;
            break;
        case '2':
            // Initialize
            $value = 12;
            break;
        case '3':
            // Initialize
            $value = 4;
            break;
        case '4':
            // Initialize
            $value = 7;
            break;
        default:
            $value = 12;
            break;
    }

    return $value;
}

function finalTerminOld($id)
{
    // Initialize
    $course = Course::where('id', $id)->first();

    if (!$course) {
        return response()->json([
            'status'    => 'error',
            'message'   => 'Produk tidak ditemukan.'
        ]);
    }

    // Initialize
    $termin  = $course->courseTermin;
    $data    = [];
    $nowDate = strtotime(date('Y-m-d H:i:s'));

    if (!$termin) {
        return response()->json([
            'status'    => 'error',
            'message'   => 'Produk tidak memiliki termin.'
        ]);
    }

    // Formula
    $totalVal   = 0;
    $total      = $course->price_num;

    // Check Discount
    if ($course->discount > 0) {
        // Initialize
        $priceAfterDisc = discountFormula($course->discount, $course->price_num);
        $total          = $priceAfterDisc;
    }

    /*
        Notes :
        1 = Annual, 2 = Monthly, 3 = 1,5 bulanan, 4 = 2 bulanan, 5 = 3 bulanan, 6 = Once Four Months, 7 = 5 bulanan, 8 = 6 bulanan, 9 = Weekly
     */

     if ($termin->interval == 1) { // Annual
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            $key = $indexInterval-1;
            $dueDate = date("d-m-Y", strtotime("+$indexInterval year", $nowDate));
            $completion_percentage = null;
            $completion_percentage_detail = null;
            if (isset($termin->completion_percentage[$key])) {
                $completion_percentage = $termin->completion_percentage[$key];
            }
            if (isset($termin->completion_percentage_detail[$key])) {
                $completion_percentage_detail = $termin->completion_percentage_detail[$key];
            }

            if ($indexInterval == 0 && $termin->down_payment) {
                $totalVal = ($termin->down_payment/100) * $termin->installment_amount;;
            } else {
                $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;;
            }

            $row['course_id']   = $course->id; 
            $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
            $row['value']       = rupiah($totalVal);
            $row['value_num']   = round($totalVal);
            $row['interest']    = $termin->interest;
            $row['due_date']    = $dueDate;
            $row['termin_percentage'] = ($indexInterval == 0) ?  $termin->down_payment . '%' : $termin->value[$key] . '%';
            $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
            $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;

            $data[] = $row;
        }
    } else if ($termin->interval == 2) { // Monthly
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            $key = $indexInterval-1;
            $dueDate = date("d-m-Y", strtotime("+$indexInterval month", $nowDate));
            $completion_percentage = null;
            $completion_percentage_detail = null;
            if (isset($termin->completion_percentage[$key])) {
                $completion_percentage = $termin->completion_percentage[$key];
            }
            if (isset($termin->completion_percentage_detail[$key])) {
                $completion_percentage_detail = $termin->completion_percentage_detail[$key];
            }

            if ($indexInterval == 0 && $termin->down_payment) {
                $totalVal = ($termin->down_payment/100) * $termin->installment_amount;;
            } else {
                $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;;
            }

            $row['course_id']   = $course->id; 
            $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
            $row['value']       = rupiah($totalVal);
            $row['value_num']   = round($totalVal);
            $row['interest']    = $termin->interest;
            $row['due_date']    = $dueDate;
            $row['termin_percentage'] = ($indexInterval == 0) ?  $termin->down_payment . '%' : $termin->value[$key] . '%';
            $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
            $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;

            $data[] = $row;
        }
    } else if ($termin->interval == 3) { // 1 half month
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            $key = $indexInterval-1;
            $day = 45 * $indexInterval;
            $dueDate = date("d-m-Y", strtotime("+$day day", $nowDate));
            $completion_percentage = null;
            $completion_percentage_detail = null;
            if (isset($termin->completion_percentage[$key])) {
                $completion_percentage = $termin->completion_percentage[$key];
            }
            if (isset($termin->completion_percentage_detail[$key])) {
                $completion_percentage_detail = $termin->completion_percentage_detail[$key];
            }

            if ($indexInterval == 0 && $termin->down_payment) {
                $totalVal = ($termin->down_payment/100) * $termin->installment_amount;;
            } else {
                $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;;
            }

            $row['course_id']   = $course->id; 
            $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
            $row['value']       = rupiah($totalVal);
            $row['value_num']   = round($totalVal);
            $row['interest']    = $termin->interest;
            $row['due_date']    = $dueDate;
            $row['termin_percentage'] = ($indexInterval == 0) ?  $termin->down_payment . '%' : $termin->value[$key] . '%';
            $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
            $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;

            $data[] = $row;
        }
    } else if ($termin->interval == 4) { // 2 Monthly
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            $key = $indexInterval-1;
            $month = 2 * $indexInterval;
            $dueDate = date("d-m-Y", strtotime("+$month month", $nowDate));
            $completion_percentage = null;
            $completion_percentage_detail = null;
            if (isset($termin->completion_percentage[$key])) {
                $completion_percentage = $termin->completion_percentage[$key];
            }
            if (isset($termin->completion_percentage_detail[$key])) {
                $completion_percentage_detail = $termin->completion_percentage_detail[$key];
            }

            if ($indexInterval == 0 && $termin->down_payment) {
                $totalVal = ($termin->down_payment/100) * $termin->installment_amount;;
            } else {
                $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;;
            }

            $row['course_id']   = $course->id; 
            $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
            $row['value']       = rupiah($totalVal);
            $row['value_num']   = round($totalVal);
            $row['interest']    = $termin->interest;
            $row['due_date']    = $dueDate;
            $row['termin_percentage'] = ($indexInterval == 0) ?  $termin->down_payment . '%' : $termin->value[$key] . '%';
            $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
            $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;

            $data[] = $row;
        }
    } else if ($termin->interval == 5) { // 3 Monthly
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            $key = $indexInterval-1;
            $month = 3 * $indexInterval;
            $dueDate = date("d-m-Y", strtotime("+$month month", $nowDate));
            $completion_percentage = null;
            $completion_percentage_detail = null;
            if (isset($termin->completion_percentage[$key])) {
                $completion_percentage = $termin->completion_percentage[$key];
            }
            if (isset($termin->completion_percentage_detail[$key])) {
                $completion_percentage_detail = $termin->completion_percentage_detail[$key];
            }

            if ($indexInterval == 0 && $termin->down_payment) {
                $totalVal = ($termin->down_payment/100) * $termin->installment_amount;;
            } else {
                $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;;
            }

            $row['course_id']   = $course->id; 
            $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
            $row['value']       = rupiah($totalVal);
            $row['value_num']   = round($totalVal);
            $row['interest']    = $termin->interest;
            $row['due_date']    = $dueDate;
            $row['termin_percentage'] = ($indexInterval == 0) ?  $termin->down_payment . '%' : $termin->value[$key] . '%';
            $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
            $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;

            $data[] = $row;
        }
    } else if ($termin->interval == 6) { // 4 Monthly
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            $key = $indexInterval-1;
            $month = 4 * $indexInterval;
            $dueDate = date("d-m-Y", strtotime("+$month month", $nowDate));
            $completion_percentage = null;
            $completion_percentage_detail = null;
            if (isset($termin->completion_percentage[$key])) {
                $completion_percentage = $termin->completion_percentage[$key];
            }
            if (isset($termin->completion_percentage_detail[$key])) {
                $completion_percentage_detail = $termin->completion_percentage_detail[$key];
            }

            if ($indexInterval == 0 && $termin->down_payment) {
                $totalVal = ($termin->down_payment/100) * $termin->installment_amount;;
            } else {
                $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;;
            }

            $row['course_id']   = $course->id; 
            $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
            $row['value']       = rupiah($totalVal);
            $row['value_num']   = round($totalVal);
            $row['interest']    = $termin->interest;
            $row['due_date']    = $dueDate;
            $row['termin_percentage'] = ($indexInterval == 0) ?  $termin->down_payment . '%' : $termin->value[$key] . '%';
            $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
            $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;

            $data[] = $row;
        }
    } else if ($termin->interval == 7) { // 5 Monthly
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            $key = $indexInterval-1;
            $month = 5 * $indexInterval;
            $dueDate = date("d-m-Y", strtotime("+$month month", $nowDate));
            $completion_percentage = null;
            $completion_percentage_detail = null;
            if (isset($termin->completion_percentage[$key])) {
                $completion_percentage = $termin->completion_percentage[$key];
            }
            if (isset($termin->completion_percentage_detail[$key])) {
                $completion_percentage_detail = $termin->completion_percentage_detail[$key];
            }

            if ($indexInterval == 0 && $termin->down_payment) {
                $totalVal = ($termin->down_payment/100) * $termin->installment_amount;;
            } else {
                $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;;
            }

            $row['course_id']   = $course->id; 
            $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
            $row['value']       = rupiah($totalVal);
            $row['value_num']   = round($totalVal);
            $row['interest']    = $termin->interest;
            $row['due_date']    = $dueDate;
            $row['termin_percentage'] = ($indexInterval == 0) ?  $termin->down_payment . '%' : $termin->value[$key] . '%';
            $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
            $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;

            $data[] = $row;
        }
    } else if ($termin->interval == 8) { // 6 Monthly
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            $key = $indexInterval-1;
            $month = 6 * $indexInterval;
            $dueDate = date("d-m-Y", strtotime("+$month month", $nowDate));
            $completion_percentage = null;
            $completion_percentage_detail = null;
            if (isset($termin->completion_percentage[$key])) {
                $completion_percentage = $termin->completion_percentage[$key];
            }
            if (isset($termin->completion_percentage_detail[$key])) {
                $completion_percentage_detail = $termin->completion_percentage_detail[$key];
            }

            if ($indexInterval == 0 && $termin->down_payment) {
                $totalVal = ($termin->down_payment/100) * $termin->installment_amount;;
            } else {
                $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;;
            }

            $row['course_id']   = $course->id; 
            $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
            $row['value']       = rupiah($totalVal);
            $row['value_num']   = round($totalVal);
            $row['interest']    = $termin->interest;
            $row['due_date']    = $dueDate;
            $row['termin_percentage'] = ($indexInterval == 0) ?  $termin->down_payment . '%' : $termin->value[$key] . '%';
            $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
            $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;

            $data[] = $row;
        }
    } else if ($termin->interval == 9) { // Weekly
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            $key = $indexInterval-1;
            $day = 7 * $indexInterval;
            $dueDate = date("d-m-Y", strtotime("+$day day", $nowDate));
            $completion_percentage = null;
            $completion_percentage_detail = null;
            if (isset($termin->completion_percentage[$key])) {
                $completion_percentage = $termin->completion_percentage[$key];
            }
            if (isset($termin->completion_percentage_detail[$key])) {
                $completion_percentage_detail = $termin->completion_percentage_detail[$key];
            }

            if ($indexInterval == 0 && $termin->down_payment) {
                $totalVal = ($termin->down_payment/100) * $termin->installment_amount;;
            } else {
                $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;;
            }

            $row['course_id']   = $course->id; 
            $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
            $row['value']       = rupiah($totalVal);
            $row['value_num']   = round($totalVal);
            $row['interest']    = $termin->interest;
            $row['due_date']    = $dueDate;
            $row['termin_percentage'] = ($indexInterval == 0) ?  $termin->down_payment . '%' : $termin->value[$key] . '%';
            $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
            $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;

            $data[] = $row;
        }
    }

    return $data;
}

function finalTermin($id, $qty = 1, $is_hidden = false)
{
    // Initialize
    $course = Course::where('id', $id)->first();

    if (!$course) {
        return response()->json([
            'status'    => 'error',
            'message'   => 'Produk tidak ditemukan.'
        ]);
    }

    // Initialize
    $termin  = $course->courseTermin;
    $data    = [];
    $nowDate = strtotime(date('Y-m-d H:i:s'));

    if (!$termin) {
        return response()->json([
            'status'    => 'error',
            'message'   => 'Produk tidak memiliki termin.'
        ]);
    }

    // Formula
    $totalVal   = 0;
    $total      = $course->price_num;

    // Check Discount
    if ($course->discount > 0) {
        // Initialize
        $priceAfterDisc = discountFormula($course->discount, $course->price_num);
        $total          = $priceAfterDisc;
    }

    // dd($termin);

    if ($termin) {
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            if ($indexInterval == 0) { // DP

                $check_format = changeFormatDueDateName($termin->dp_duedate_name);

                $number_due_date = $termin->dp_duedate_number;
                if ($check_format && $check_format == 'week') {
                    $check_format = 'day';
                    $number_due_date = $termin->dp_duedate_number * 7;
                }
                
                $dueDate = null;
                if ($termin->dp_duedate_name && $termin->dp_duedate_number) {
                    $dueDate = date("d-m-Y", strtotime("+$number_due_date $check_format", $nowDate));
                }
                $dp_due_date = $dueDate;

                if ($termin->is_percentage == 1) { // with percenteage
                    # code...
                    $totalVal = ($termin->down_payment/100) * $termin->installment_amount;
                    $totalVal = $totalVal * $qty;
                } else { // with nominal
                    $totalVal = $termin->down_payment;
                    $totalVal = $totalVal * $qty;
                }

                if (!$is_hidden) {
                    $row['course_id']   = $course->id; 
                    $row['description'] = 'Uang Muka';
                    $row['value']       = rupiah($totalVal);
                    $row['value_num']   = round($totalVal);
                    $row['interest']    = $termin->interest;
                    $row['due_date']    = $dueDate;
                    $row['due_date_description']    = $termin->dp_duedate_number ? $termin->dp_duedate_number . ' ' . $termin->dp_duedate_name . ' setelah transaksi' : null;
                    $row['termin_percentage'] = ($termin->is_percentage == 1) ? $termin->down_payment . '%' : rupiah($termin->down_payment * $qty);
                    $row['completion_percentage'] = null;
                    $row['completion_percentage_detail'] = null;
                    $row['duedate_number'] = $termin->dp_duedate_number;
                    $row['duedate_name'] = $termin->dp_duedate_name;
                    $row['is_percentage'] = $termin->is_percentage;
                    $row['is_hidden'] = $termin->is_hidden;
                } elseif ($is_hidden) {
                    $row['course_id']   = $course->id; 
                    $row['description'] = 'Uang Muka';
                    $row['value']       = null;
                    $row['value_num']   = null;
                    $row['interest']    = $termin->interest;
                    $row['due_date']    = $dueDate;
                    $row['due_date_description']    = null;
                    $row['termin_percentage'] = null;
                    $row['completion_percentage'] = null;
                    $row['completion_percentage_detail'] = null;
                    $row['duedate_number'] = null;
                    $row['duedate_name'] = null;
                    $row['is_percentage'] = $termin->is_percentage;
                    $row['is_hidden'] = $termin->is_hidden;
                }
            } else {
                // init
                if ($indexInterval == 1) { // setting for termin 1
                    $dp_check_format = changeFormatDueDateName($termin->dp_duedate_name);

                    $dp_number_due_date = $termin->dp_duedate_number;
                    if ($dp_check_format && $dp_check_format == 'week') {
                        $dp_check_format = 'day';
                        $dp_number_due_date = $termin->dp_duedate_number * 7;
                    }

                    $dp_due_date = date("d-m-Y", strtotime("+$dp_number_due_date $dp_check_format", $nowDate));

                    $dp_due_date = strtotime($dp_due_date);
                }

                $key = $indexInterval-1;
                if (!isset($termin->termin_duedate_name[$key])) {
                    $dueDate = null;
                }
                if (isset($termin->termin_duedate_name[$key])) {
                    $check_format = changeFormatDueDateName($termin->termin_duedate_name[$key]);
    
                    $number_due_date = $termin->termin_duedate_number[$key];
                    if ($check_format && $check_format == 'week') {
                        $check_format = 'day';
                        $number_due_date = $termin->termin_duedate_number[$key] * 7;
                    }
    
                    if ($indexInterval == 1) { // check get due_date first termin
                        $dueDate = date("d-m-Y", strtotime("+$number_due_date $check_format", $dp_due_date));
                    }
                    if ($indexInterval > 1) { // check get due_date for next termin
                        $prev_due_date = strtotime($dueDate);
                        $dueDate = date("d-m-Y", strtotime("+$number_due_date $check_format", $prev_due_date));
                    }
                }

                $due_date_description = null;

                if ($indexInterval == 1) {
                    if (isset($termin->termin_duedate_name[$key]) && isset($termin->termin_duedate_number[$key])) {
                        $due_date_description = $termin->termin_duedate_number[$key] . ' ' . $termin->termin_duedate_name[$key] . ' setelah uang muka';
                    }
                } else {
                    if (isset($termin->termin_duedate_name[$key]) && isset($termin->termin_duedate_number[$key])) {
                        $due_date_description = $termin->termin_duedate_number[$key] . ' ' . $termin->termin_duedate_name[$key] . ' setelah termin - ' . ($indexInterval-1);
                    }
                }

                $completion_percentage = null;
                $completion_percentage_detail = null;
                if (isset($termin->completion_percentage[$key])) {
                    $completion_percentage = $termin->completion_percentage[$key];
                }
                if (isset($termin->completion_percentage_detail[$key])) {
                    $completion_percentage_detail = $termin->completion_percentage_detail[$key];
                }

                if ($indexInterval == 0 && $termin->down_payment) {
                    if ($termin->is_percentage == 1) { // with percenteage
                        $totalVal = ($termin->down_payment/100) * $termin->installment_amount;
                        $totalVal = $totalVal * $qty;
                    } else { // with nominal
                        $totalVal = $termin->down_payment;
                        $totalVal = $totalVal * $qty;
                    }
                } else {
                    if ($termin->is_percentage == 1) { // with percenteage
                        $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;
                        $totalVal = $totalVal * $qty;
                    } else { // with nominal
                        $totalVal = $termin->value[$key];
                        $totalVal = $totalVal * $qty;
                    }
                }

                if (!$is_hidden) {
                    $row['course_id']   = $course->id; 
                    $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
                    $row['value']       = rupiah($totalVal);
                    $row['value_num']   = round($totalVal);
                    $row['interest']    = $termin->interest;
                    $row['due_date']    = $dueDate;
                    $row['due_date_description']    = $due_date_description;
                    $row['termin_percentage'] = ($termin->is_percentage == 1) ? $termin->value[$key] . '%' : rupiah($termin->value[$key] * $qty);
                    $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
                    $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;
                    $row['duedate_number'] = isset($termin->termin_duedate_number[$key]) ? $termin->termin_duedate_number[$key] : null;
                    $row['duedate_name'] = isset($termin->termin_duedate_name[$key]) ? $termin->termin_duedate_name[$key] : null;
                    $row['is_percentage'] = $termin->is_percentage;
                    $row['is_hidden'] = $termin->is_hidden;
                } elseif ($is_hidden) {
                    $row['course_id']   = $course->id; 
                    $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
                    $row['value']       = null;
                    $row['value_num']   = null;
                    $row['interest']    = $termin->interest;
                    $row['due_date']    = $dueDate;
                    $row['due_date_description']    = null;
                    $row['termin_percentage'] = null;
                    $row['completion_percentage'] = null;
                    $row['completion_percentage_detail'] = null;
                    $row['duedate_number'] = null;
                    $row['duedate_name'] = null;
                    $row['is_percentage'] = $termin->is_percentage;
                    $row['is_hidden'] = $termin->is_hidden;
                }
            }

            $data[] = $row;
        }
    }

    return $data;
}

function changeFormatDueDateName($var)
{
    $data = null;
    if ($var == 'hari') {
        $data = 'day';
    }

    if ($var == 'minggu') {
        $data = 'week';
    }

    if ($var == 'bulan') {
        $data = 'month';
    }

    if ($var == 'tahun') {
        $data = 'year';
    }

    return $data;
}

function discountFormula ($discount, $price) {
    // Initialize
    $formula = ($discount/100) * $price;

    return ($price - $formula);
}

function balanceTypeIcon($type)
{
    switch ($type) {
        case 0:
            $value = 0;
            break;
        case 1:
            $value = 1;
            break;
        case 2:
            $value = 1;
        break;
        case 3:
            $value = 1;
        break;
        case 4:
            $value = 0;
        break;

        default:
            $value = 1;
            break;
    }

    return $value;
}

// Marketplace
function statusTransactionV2($status, $type = '')
{
    switch ($status) {
        case 0:
            $value = 'Menunggu Konfirmasi Seller';
            break;
        case 1:
            $value = 'Pesanan Diproses';
            break;
        case 2:
            $value = 'Pesanan Ditolak';
        break;
        case 3:
            if ($type == 1) {
                $value = 'Jasa Dikerjakan';
            } else {
                $value = 'Barang Dikirim';
            }
        break;
        case 4:
            if ($type == 1) {
                $value = 'Jasa Selesai';
            } else {
                $value = 'Barang Diterima';
            }
        break;
        case 5:
            $value = 'Pesanan Dibatalkan';
        break;
        case 6:
            $value = 'Selesai';
        break;

        default:
            $value = 'Menunggu Konfirmasi Seller';
            break;
    }

    return $value;
}

function statusPayment($status)
{
    switch ($status) {
        case 0:
            $value = 'Menunggu Pembayaran';
            break;
        case 1:
            $value = 'Dibayar';
            break;
        case 2:
            $value = 'Transaksi kadaluarsa';
        break;
        case 3:
            $value = 'Selesai';
        break;
        case 4:
            $value = 'Dibatalkan Pembeli';
        break;
        case 5:
            $value = 'Dibatalkan Sistem';
        break;

        default:
            $value = 'Belum dibayar';
            break;
    }

    return $value;
}

function categoryTransaction($category)
{
    switch ($category) {
        case 0:
            $value = 'Belanja';
            break;
        case 1:
            $value = 'Top Up';
            break;
        case 2:
            $value = 'Pembayaran Termin';
            break;
        case 3:
            $value = 'Pembelian Kas Barang';
            break;

        default:
            $value = 'Belanja';
            break;
    }

    return $value;
}

function companyStatus($status)
{
    switch ($status) {
        case 1:
            $value = 'Platinum';
            break;
        case 2:
            $value = 'Premium';
            break;

        default:
            $value = 'Premium';
            break;
    }

    return $value;
}

function statusBAST($status)
{
    switch ($status) {
        case 0:
            $value = 'Menunggu Persetujuan Buyer';
            break;
        case 1:
            $value = 'Pengajuan Diterima';
            break;
        case 2:
            $value = 'Pengajuan Ditolak';
            break;

        default:
            $value = 'Menunggu Persetujuan Buyer';
            break;
    }

    return $value;
}

function biddingStatus($status)
{
    switch ($status) {
        case 0:
            $value = 'Menunggu Persetujuan';
            break;
        case 1:
            $value = 'Penawaran Diterima';
            break;
        case 2:
            $value = 'Penawaran Ditolak';
            break;
        case 3:
            $value = 'Penawaran Dibatalkan';
            break;

        default:
            $value = 'Menunggu Persetujuan';
            break;
    }

    return $value;
}

function terminBidding($id, $qty = 1, $is_hidden = false)
{
    // Initialize
    $biddingProject = BiddingProject::where('id', $id)->first();

    // Initialize
    $termin     = $biddingProject->termin;
    $data       = [];
    $nowDate    = strtotime(date('Y-m-d H:i:s'));
    $totalVal   = 0;

    if ($termin) {
        for ($indexInterval = 0; $indexInterval <= $termin->number_of_payment; $indexInterval++) { 
            // Initialize
            if ($indexInterval == 0) { // DP
                $check_format = changeFormatDueDateName($termin->dp_duedate_name);

                $number_due_date = $termin->dp_duedate_number;
                if ($check_format && $check_format == 'week') {
                    $check_format = 'day';
                    $number_due_date = $termin->dp_duedate_number * 7;
                }
                
                $dueDate = null;
                if ($termin->dp_duedate_name && $termin->dp_duedate_number) {
                    $dueDate = date("d-m-Y", strtotime("+$number_due_date $check_format", $nowDate));
                }
                $dp_due_date = $dueDate;

                if ($termin->is_percentage == 1) { // with percenteage
                    $totalVal = ($termin->down_payment/100) * $termin->installment_amount;
                    $totalVal = $totalVal * $qty;
                } else { // with nominal
                    $totalVal = $termin->down_payment;
                    $totalVal = $totalVal * $qty;
                }

                if (!$is_hidden) {
                    // $row['course_id']   = $course->id; 
                    $row['description'] = 'Uang Muka';
                    $row['value']       = rupiah($totalVal);
                    $row['value_num']   = round($totalVal);
                    $row['interest']    = $termin->interest;
                    $row['due_date']    = $dueDate;
                    $row['due_date_description']    = $termin->dp_duedate_number ? $termin->dp_duedate_number . ' ' . $termin->dp_duedate_name . ' setelah transaksi' : null;
                    $row['termin_percentage'] = ($termin->is_percentage == 1) ? $termin->down_payment . '%' : rupiah($termin->down_payment * $qty);
                    $row['completion_percentage'] = null;
                    $row['completion_percentage_detail'] = null;
                    $row['duedate_number'] = $termin->dp_duedate_number;
                    $row['duedate_name'] = $termin->dp_duedate_name;
                    $row['is_percentage'] = $termin->is_percentage;
                    $row['is_hidden'] = $termin->is_hidden;
                } elseif ($is_hidden) {
                    // $row['course_id']   = $course->id; 
                    $row['description'] = 'Uang Muka';
                    $row['value']       = null;
                    $row['value_num']   = null;
                    $row['interest']    = $termin->interest;
                    $row['due_date']    = $dueDate;
                    $row['due_date_description']    = null;
                    $row['termin_percentage'] = null;
                    $row['completion_percentage'] = null;
                    $row['completion_percentage_detail'] = null;
                    $row['duedate_number'] = null;
                    $row['duedate_name'] = null;
                    $row['is_percentage'] = $termin->is_percentage;
                    $row['is_hidden'] = $termin->is_hidden;
                }
            } else {
                // init
                if ($indexInterval == 1) { // setting for termin 1
                    $dp_check_format = changeFormatDueDateName($termin->dp_duedate_name);

                    $dp_number_due_date = $termin->dp_duedate_number;
                    if ($dp_check_format && $dp_check_format == 'week') {
                        $dp_check_format = 'day';
                        $dp_number_due_date = $termin->dp_duedate_number * 7;
                    }

                    $dp_due_date = date("d-m-Y", strtotime("+$dp_number_due_date $dp_check_format", $nowDate));

                    $dp_due_date = strtotime($dp_due_date);
                }

                $key = $indexInterval-1;
                $dueDate = null;
                if (isset($termin->termin_duedate_name[$key])) {
                    $check_format = changeFormatDueDateName($termin->termin_duedate_name[$key]);
    
                    $number_due_date = $termin->termin_duedate_number[$key];
                    if ($check_format && $check_format == 'week') {
                        $check_format = 'day';
                        $number_due_date = $termin->termin_duedate_number[$key] * 7;
                    }
    
                    if ($indexInterval == 1) { // check get due_date first termin
                        $dueDate = date("d-m-Y", strtotime("+$number_due_date $check_format", $dp_due_date));
                    }
                    if ($indexInterval > 1) { // check get due_date for next termin
                        $prev_due_date = strtotime($dueDate);
                        $dueDate = date("d-m-Y", strtotime("+$number_due_date $check_format", $prev_due_date));
                    }
                }

                $due_date_description = null;

                if ($indexInterval == 1) {
                    if (isset($termin->termin_duedate_name[$key]) && isset($termin->termin_duedate_number[$key])) {
                        $due_date_description = $termin->termin_duedate_number[$key] . ' ' . $termin->termin_duedate_name[$key] . ' setelah uang muka';
                    }
                } else {
                    if (isset($termin->termin_duedate_name[$key]) && isset($termin->termin_duedate_number[$key])) {
                        $due_date_description = $termin->termin_duedate_number[$key] . ' ' . $termin->termin_duedate_name[$key] . ' setelah termin - ' . ($indexInterval-1);
                    }
                }

                $completion_percentage = null;
                $completion_percentage_detail = null;
                if (isset($termin->completion_percentage[$key])) {
                    $completion_percentage = $termin->completion_percentage[$key];
                }
                if (isset($termin->completion_percentage_detail[$key])) {
                    $completion_percentage_detail = $termin->completion_percentage_detail[$key];
                }

                if ($indexInterval == 0 && $termin->down_payment) {
                    if ($termin->is_percentage == 1) { // with percenteage
                        $totalVal = ($termin->down_payment/100) * $termin->installment_amount;
                        $totalVal = $totalVal * $qty;
                    } else { // with nominal
                        $totalVal = $termin->down_payment;
                        $totalVal = $totalVal * $qty;
                    }
                } else {
                    if ($termin->is_percentage == 1) { // with percenteage
                        $totalVal = ($termin->value[$key]/100) * $termin->installment_amount;
                        $totalVal = $totalVal * $qty;
                    } else { // with nominal
                        $totalVal = $termin->value[$key];
                        $totalVal = $totalVal * $qty;
                    }
                }

                if (!$is_hidden) {
                    // $row['course_id']   = $course->id; 
                    $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
                    $row['value']       = rupiah($totalVal);
                    $row['value_num']   = round($totalVal);
                    $row['interest']    = $termin->interest;
                    $row['due_date']    = $dueDate;
                    $row['due_date_description']    = $due_date_description;
                    $row['termin_percentage'] = ($termin->is_percentage == 1) ? $termin->value[$key] . '%' : rupiah($termin->value[$key] * $qty);
                    $row['completion_percentage'] = $completion_percentage ? $completion_percentage . '%' : null;
                    $row['completion_percentage_detail'] = $completion_percentage_detail ? $completion_percentage_detail : null;
                    $row['duedate_number'] = isset($termin->termin_duedate_number[$key]) ? $termin->termin_duedate_number[$key] : null;
                    $row['duedate_name'] = isset($termin->termin_duedate_name[$key]) ? $termin->termin_duedate_name[$key] : null;
                    $row['is_percentage'] = $termin->is_percentage;
                    $row['is_hidden'] = $termin->is_hidden;
                } elseif ($is_hidden) {
                    // $row['course_id']   = $course->id; 
                    $row['description'] = ($indexInterval == 0) && $termin->down_payment ? 'Down Payment' : 'Termin - '.$indexInterval;
                    $row['value']       = null;
                    $row['value_num']   = null;
                    $row['interest']    = $termin->interest;
                    $row['due_date']    = $dueDate;
                    $row['due_date_description']    = null;
                    $row['termin_percentage'] = null;
                    $row['completion_percentage'] = null;
                    $row['completion_percentage_detail'] = null;
                    $row['duedate_number'] = null;
                    $row['duedate_name'] = null;
                    $row['is_percentage'] = $termin->is_percentage;
                    $row['is_hidden'] = $termin->is_hidden;
                }
            }

            $data[] = $row;
        }
    }

    return $data;
}

function paymentTypeInventory($status)
{
    switch ($status) {
        case '0':
            // Initialize
            $value = 'Tunai';
            break;
        case '1':
            // Initialize
            $value = 'Uang Muka';
            break;
        case '2':
            // Initialize
            $value = 'Uang Termin';
            break;
        default:
            $value = 'Tunai';
            break;
    }

    return $value;
}

function invoiceType($type)
{
    switch ($type) {
        case '0':
            // Initialize
            $value = 'Barang';
            break;
        case '1':
            // Initialize
            $value = 'Jasa';
            break;
        default:
            $value = 'Barang';
            break;
    }

    return $value;
}