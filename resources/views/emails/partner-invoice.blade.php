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
                                                Hi {{ $user->name }},
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text white pb20" style="font-family:roboto; font-size:14px; line-height:26px; text-align:left; color:black; padding-bottom:20px;">
                                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsum qui, explicabo ratione ad magnam animi enim in atque voluptas dolores, consequuntur ullam soluta temporibus, nobis laborum et eos vitae? Vero.
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