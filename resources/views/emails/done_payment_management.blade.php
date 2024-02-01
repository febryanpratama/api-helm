@extends('emails.layout')

@section('content')
<!-- Body / Grey -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td bgcolor="#fafafa">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <th class="column" width="50" style="font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;"></th>
                    <th class="column" style="font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="p30-15">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td class="h3 white pb20" style="font-family:'roboto'; font-size:15px; line-height:32px; text-align:left; color:black; padding-bottom:20px;">
                                                Hi Management,
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text white pb20" style="font-family:roboto; font-size:14px; line-height:26px; text-align:left; color:black; padding-bottom:20px;">
                                                {{ $user->name }} telah melakukan pembayaran dengan rincian sebagai berikut :
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="pb20" style="padding-bottom:20px;">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td class="pb10" style="padding-bottom:10px;">
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td valign="top" class="img" width="20" style="font-size:0pt; line-height:0pt; text-align:left;">
                                                                    </td>
                                                                    <td valign="top" class="text2 white" style="font-family:'roboto'; font-size:14px; line-height:26px; text-align:left; color:black;">Pesanan :
                                                                    </td>
                                                                    <td valign="top" class="text2 white" style="font-family:'roboto'; font-size:16px; line-height:26px; text-align:left; color:black;">
                                                                        @foreach($courseTransaction->checkoutDetail as $val)
                                                                        <div><b>{{ $loop->iteration }}. {{ $val->course_name }}</b></div>
                                                                        @endforeach
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td valign="top" class="img" width="20" style="font-size:0pt; line-height:0pt; text-align:left;">
                                                                    </td>
                                                                    <td valign="top" class="text2 white" style="font-family:'roboto'; font-size:14px; line-height:26px; text-align:left; color:black;">Total :
                                                                    </td>
                                                                    <td valign="top" class="text2 white" style="font-family:'roboto'; font-size:16px; line-height:26px; text-align:left; color:black;">
                                                                        <b>{{ rupiah($courseTransaction->total_payment) }}</b>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td valign="top" class="img" width="20" style="font-size:0pt; line-height:0pt; text-align:left;">
                                                                    </td>
                                                                    <td valign="top" class="text2 white" style="font-family:'roboto'; font-size:14px; line-height:26px; text-align:left; color:black;">Metode Pembayaran :
                                                                    </td>
                                                                    <td valign="top" class="text2 white" style="font-family:'roboto'; font-size:16px; line-height:26px; text-align:left; color:black;">
                                                                        <b>
                                                                            {{ $courseTransaction->payment_type }} ({{ $courseTransaction->bank_name }})
                                                                        </b>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td valign="top" class="img" width="20" style="font-size:0pt; line-height:0pt; text-align:left;">
                                                                    </td>
                                                                    <td valign="top" class="text2 white" style="font-family:'roboto'; font-size:14px; line-height:26px; text-align:left; color:black;">Waktu Pembayaran :
                                                                    </td>
                                                                    <td valign="top" class="text2 white" style="font-family:'roboto'; font-size:16px; line-height:26px; text-align:left; color:black;">
                                                                        <b>{{ date('d F Y H:i:s') }}</b>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </th>
                    <th class="column" width="50" style="font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;"></th>
                </tr>
            </table>
        </td>
    </tr>
</table>
<!-- END Body / Grey -->
@stop