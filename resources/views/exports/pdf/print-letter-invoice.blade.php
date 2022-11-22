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
                font-size: 9px
            }
            .td-style{
                padding:0px;
            }
            #transactions {
                font-family: Arial, Helvetica, sans-serif;
                border-collapse: collapse;
                width: 100%;
                margin-top: 12px;
                font-size: 10pt;
            }
            #transactions td, #transactions thead td {
                border: 1px solid black;
                padding: 8px;
            }
            #transactions thead td {
                padding-top: 12px;
                padding-bottom: 12px;
                text-align: left;
                background-color: #ffff00;
                color: black;
            }
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12pt;
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
                font-family: 'Times New Roman';
            }
            #description-official {
                font-size: 14px;
            }
            #keuangan {
                font-size: 10pt;
                margin-top: 40px;
            }
            .panel-left2 {
                float:left;
                width:45%;
            }
            .catatan {
                border: 1px solid black;
                padding: 4px;
            }
            table td, table td * {
                vertical-align: top;
            }
    </style>
    <body>
        <div class="panel-description">
            <div class="panel-left" style="width:60% !important; margin-right: 12px;">
                <div id="description-official">
                    <h3>CV. CNA INDONESIA</h3>
                    <span>JL. MAYJEND SUTOYO 942 SEMARANG 50134 - JAWA TENGAH</span>
                    <span>TELEPON : (+62-24) 8414756 - FACSIMILE (+62-24) 7462356</span>
                    <span>WEBSITE: www.waters.co.id - email : andygouw@waters.co.id</span>
                    <br/><br/><br/>
                    <span>NPWP : 60.351.086.8-509.000</span>
                </div>
            </div>
            <div class="panel-left2">
                KEPADA Yang terhormat,
                <table>
                    <tr>
                        <td style="width: 70px;">Nama</td>
                        <td>:&nbsp;</td>
                        <td>{{ $transaction->customer->name }}</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Alamat</td>
                        <td>:&nbsp;</td>
                        <td>{{ $transaction->customer->address }}</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Kota</td>
                        <td>:&nbsp;</td>
                        <td>{{ $transaction->customer->city }}</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Telp/HP</td>
                        <td>:&nbsp;</td>
                        <td>{{ $transaction->customer->phone }}</td>
                    </tr>
                </table>
            </div>
            <div style="clear:both;"></div>
        </div>
        <div style="margin-top: 12px;">
            <div class="panel-left">
                <span><strong>FAKTUR PENJUALAN</strong> : <span>{{ $transaction->code }}</span><br/>
                <span>Tanggal Kirim : </span><span>{{ $transaction->transaction_date }}<span><br/>
                <span>Keterangan : </span><span style="border-bottom: 1px solid black; width: 100%;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br/>
            </div>
            <div class="panel-left">
                <span>Alamat Kirim </span><span>:&nbsp;</span><span>{{ $transaction->shipping_address ?? '-' }}</span>
            </div>
            <div style="clear:both;"></div>
        </div>
        <table id="transactions">
            <thead>
                <tr>
                    <td><center>JUMLAH</center></td>
                    <td><center>NAMA BARANG</center></td>
                    <td><center>KAPASITAS</center></td>
                    <td><center>MODEL</center></td>
                    <td><center>HARGA NETTO</center></td>
                    <td><center>UNIQUE NUMBER</center></td>
                    <td><center>AUTHORIZER<br/>DISTRIBUTOR</center></td>
                    <td><center>KETERANGAN</center></td>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->transactionProducts as $item)
                <tr>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ strtoupper($item->name) }}</td>
                    <td>{{ strtoupper($item->capacity) }}</td>
                    <td>{{ strtoupper($item->model) }}</td>
                    <td>Rp.{{ number_format($item->price, 2, ',', '.') }}</td>
                    <td>{{ strtoupper($transaction->member->member_numb) }}</td>
                    <td>{{ strtoupper($transaction->member->name) }}</td>
                    <td></td>
                </tr>
                @endforeach
                <tr style="border: none !important;">
                    <td style="border: none !important;" colspan="3">
                        <div>
                            <div style="margin-top: 12px;">
                                <span>Terbilang : </span><span style="border-bottom: 1px solid black; width: 100%;">{{ $transaction->terbilang }}</span>
                            </div>
                        </div>
                    </td>
                    <td style="border: none !important;" colspan="5">
                        <div style="margin-top: 12px;">
                            TOTAL : Rp. {{ number_format($transaction->total_price, 2, ',', '.') }}
                        </div>
                    </td>
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