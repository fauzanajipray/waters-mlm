<html>
    <head>
        <title>{{$title}}</title>
        <style>
            @page {
                margin: 50px 30px;
            }
        </style>
    </head>
    <body>
        <h3 style="text-align: center;font-weight:bold">ID CARD MEMBER</h3>
        <div style="width:450px;margin-left:auto;margin-right:auto;border:1px solid black;border-radius:2px">
            <div style="margin-top:12px;margin-bottom:12px">
                <div style="width: 100%;height:12px;background-color:#7b8472 "></div>
                <div style="width: 100%;height:24px;background-color:#53637b;text-align:center;color:white;font-weight:bold;padding-top:2px">CNA INDONESIA</div>
                <div style="width: 100%;height:12px;background-color:#ac8d84"></div>
                <table style="width:100%;margin-top:16px;margin-bottom:16px;padding-left:12px;padding-right:12px;">
                    <tr>
                        <td style="width:30%;padding-right:12px">
                            @php
                                  $base64Image = base64_encode(file_get_contents(public_path($imageUrl)));
                                  echo '<img src="data:image/jpg;base64,' . $base64Image . '" style="width:100%;height:auto;" />';
                            @endphp
                        </td>
                        <td style="width:70%;vertical-align:top;">
                            <div style="border-bottom: 1px solid black;border-bottom-style:dotted;"><b>{{\Str::limit($member->name, 30)}}</b></div>
                            <small style="font-size:10px;">Authorized Distributor</small>
                            <div style="border-bottom: 1px solid black;border-bottom-style:dotted;margin-top:8px"><b>{{$member['member_numb'] ?? '-'}}</b></div>
                            <small style="font-size:10px;">Unique Number</small>
                            <div style="margin-top:8px;text-align:right">KTP / SIM : <span style="border-bottom: 1px solid black;border-bottom-style:dotted;font-weight:bold">{{$member['id_card'] ?? '-'}}</span></div>
                            <table style="width:100%;margin-top:24px;">
                                <tr>
                                    <td style="width:70%;">
                                        <span style="border-bottom: 1px solid black;border-bottom-style:dotted;font-size:12px;font-weight:bold">{{\Str::limit('Gouw Andy Siswanto', 30)}}</span>
                                        <small style="font-size:10px;display:block">The Management</small>
                                    </td>
                                    <td style="width:30%;text-align:right">
                                        <span style="border-bottom: 1px solid black;border-bottom-style:dotted;font-size:12px;font-weight:bold">{{$member['expired'] ?? '-'}}</span>
                                        <small style="font-size:10px;display:block">Date of Expiry</small>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>