<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Surat Invoice</title>
    </head>
    <style>
            * {
                font-size: 9.5pt
            }
            .td-style{
                padding:0px;
            }
            #transactions {
                border-collapse: collapse;
                width: 100%;
                margin-top: 8px;
                font-size: 10pt;
            }
            #transactions td, #transactions thead td {
                border: 1.6px solid black;
                padding: 4px;
            }
            #transactions thead td {
                padding-top: 12px;
                padding-bottom: 12px;
                text-align: left;
                /* font-weight: bold; */
                /* background-color: #ffff00; */
                color: black;
            }
            body {
                font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
                font-size: 12pt;
                font-weight: bold;
                word-spacing: normal;
                /* transform: scale(1, 1.12); */
            }
            .panel-left {
                float:left;
                width:55%;
            }
            .catatan {
                border: 1px solid black;
                padding: 4px;
            }
            h3 {
                padding:0px;
                margin:0px;
                /* font-family: 'Times New Roman'; */
            }
            #description-official {
                font-size: 14px;
            }
            #keuangan {
                font-size: 10pt;
                margin-top: 4px;
            }
            .panel-left2 {
                float:left;
                width:45%;
            }
            .catatan {
                border: 1.5px solid black;
                padding: 4px;
            }
            table td, table td * {
                vertical-align: top;
            }
            @page{
                margin: 10px 28px 10px 12px;
            }
    </style>
    <body>
        <div class="panel-description">
            <div class="panel-left" style="width:60% !important; margin-right: 12px;">
                <div id="description-official">
                    <h3>CV. CNA INDONESIA</h3>
                    <span>Jl. Mayjend Sutoyo Siswomiharjo No. 942, Semarang 50134</span>
                    <br />
                    <span>Telepon : (+62-24) 8414756 - WA (+62) 81326613526</span>
                    <br />
                    <span>Web : waters.co.id - Email : andygouw@waters.co.id</span>
                    <br />
                    <span>NPWP : 60.351.086.8-509.000</span>
                    <br /><br />
                    <span><strong>FAKTUR PENJUALAN</strong> : <span>{{ $transaction->code }}</span>
                    <br/>
                    <span>Tanggal Kirim : </span><span>{{ date("d M Y", strtotime($transaction->transaction_date)) }}</span>
                    <br/>
                    <span>Keterangan : </span><span>{{ $transaction->shipping_notes ?? '-' }}</span>
                    <br/>
                    <span>Alamat Kirim : </span>
                    <span>{{ $transaction->shipping_address ?? '-' }}</span>
                </div>
            </div>
            <div class="panel-left2">
                <table>
                    <tr>
                        <td colspan="3">KEPADA Yang terhormat,</td>
                    </tr>
                    <tr>
                        <td style="width: 40px;">Nama</td>
                        <td style="width: 10px; text-align: center;">:&nbsp;</td>
                        <td>{{ $transaction->customer->name }}</td>
                    </tr>
                    <tr>
                        <td style="width: 40px;">Alamat</td>
                        <td style="width: 10px; text-align: center;">:&nbsp;</td>
                        <td style="max-width: 230px;">{{ $transaction->customer->address }}</td>
                    </tr>
                    <tr>
                        <td style="width: 40px;">Kota</td>
                        <td style="width: 10px; text-align: center;">:&nbsp;</td>
                        <td>{{ $transaction->customer->city }}</td>
                    </tr>
                    <tr>
                        <td style="width: 40px;">Telp/HP</td>
                        <td style="width: 10px; text-align: center;">:&nbsp;</td>
                        <td>{{ $transaction->customer->phone }}</td>
                    </tr>
                </table>
            </div>
            <div style="clear:both;"></div>
        </div>
        
        <table id="transactions">
            <thead>
                <tr>
                    <td><center>JUMLAH</center></td>
                    <td><center>NAMA BARANG</center></td>
                    <td><center>MODEL</center></td>
                    <td><center>KAPASITAS</center></td>
                    <td><center>HARGA NETTO</center></td>
                    <td><center>UNIQUE NUMBER</center></td>
                    @if ($transaction->type == 'Demokit' || $transaction->type == 'Display' || $transaction->type == 'Bebas Putus')
                        <td><center>DISKON</center></td>
                    @endif
                    <td><center>AUTHORIZER<br/>DISTRIBUTOR</center></td>
                    <td><center>KETERANGAN</center></td>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->transactionProducts as $item)
                <tr>
                    <td>{{ $item->quantity }} Unit</td>
                    <td>{{ strtoupper($item->name) }}</td>
                    <td>{{ strtoupper($item->model) }}</td>
                    <td>{{ strtoupper($item->capacity) }}</td>
                    <td>Rp.{{ number_format($item->price, 2, ',', '.') }}</td>
                    <td>{{ strtoupper($transaction->member->member_numb) }}</td>
                    @if ($transaction->type == 'Demokit' || $transaction->type == 'Display')
                        <td>{{ $item->discount_percentage }} %</td>
                    @elseif($transaction->type == 'Bebas Putus')
                        @if ($item->discount_percentage > 0)
                            <td>{{ $item->discount_percentage }} %</td>
                        @else
                            <td>Rp. {{ number_format($item->discount_amount, 2, ',', '.') }}</td>
                        @endif
                    @endif
                    <td>{{ strtoupper($transaction->member->name) }}</td>
                    <td>{{ $item->product_notes ?? '-' }}</td>
                </tr>
                @endforeach
                <tr style="border: none !important;">
                    <td style="border: none !important;" colspan="@if($transaction->type == 'Demokit' || $transaction->type == 'Display' || $transaction->type == 'Bebas Putus') 9 @else 8 @endif">
                        <div>
                            <div style="margin-top: 4px;">
                                <span>Terbilang : </span><span style="border-bottom: 1px solid black; width: 100%;">{{ $transaction->terbilang }}</span>
                            </div>
                        </div>
                    </td>
                    {{-- <td style="border: none !important;" colspan="@if($transaction->type == 'Demokit' || $transaction->type == 'Display' || $transaction->type == 'Bebas Putus') 6 @else 5 @endif">
                        <div style="margin-top: 12px;">
                            TOTAL : Rp. {{ number_format($transaction->total_price, 2, ',', '.') }}
                        </div>
                    </td> --}}
                </tr>
            </tbody>
        </table>
        <table id="keuangan">
            <tr>
                <td style="width: 180px;">
                    <div>
                    <br/>

                    <br/>
                        <span>CNA INDONESIA</span><br>
                        BAGIAN KEUANGAN
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <br/>

                        (.................................)
                    </div>
                </td>
                <td>
                    <div class="catatan" style="text-align: justify;">
                        <strong>CATATAN :</strong><br/>
                        <span>
                        Saya sebagai pembeli menyetujui bahwa, jika saya membatalkan pembelian tersebut diatas, maka uang deposit tidak dapat di minta kembali, Bila pembeli belum bersedia menerima barang atau melunasinya pada tanggal pengiriman, maka kenaikan harga yang terjadi setelahnya akan menjadi tanggung jawab pembali. Barang yang sudah dibeli, tidak dapat ditukar/dikembalikan.
                        </span>
                    </div>
                </td>
                <td style="width: 48px;"></td>
                <td>
                    <div>
                    <br/>

                    <br/>
                        <center><span>Semarang,</span></center>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <br/>

                        <center>(.................................)</center>
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>