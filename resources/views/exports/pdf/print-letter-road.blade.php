<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Surat Jalan</title>
    </head>
    <style>
            * {
                font-size: 7.5pt
            }
            .td-style{
                padding:0px;
            }
            #transactions {
                font-family: Arial, Helvetica, sans-serif;
                border-collapse: collapse;
                width: 100%;
                margin-top: 12px;
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
            }
            
            .panel-left {
                float:left;
                width:55%;
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
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
            <tr>
                <td style="width: 55%; padding: 0px;"><h3 style="margin:0px; padding-left:3px;">SURAT JALAN</h3></td>
                <td>Semarang, {{ now()->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td style="width: 55%; border:1px;"></td>
                <td>KEPADA Yang terhormat,</td>
            </tr>
        </table>
        <div class="panel-description">
            <div class="panel-left">
                <table>
                    <tr>
                        <td style="width: 160px;">No.Surat Jalan</td>
                        <td>:&nbsp;</td>
                        <td>{{ $data->letter_road_code }}</td>
                    </tr>
                    <tr>
                        <td style="width: 160px;">No.Invoice</td>
                        <td>:&nbsp;</td>
                        <td>{{ $data->code }}</td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style="width: 160px;">Tanggal Pengiriman</td>
                        <td>:&nbsp;</td>
                        <td>{{ $data->transaction_date }}</td>
                    </tr>
                    <tr>
                        <td style="width: 160px;">Almat. kirim</td>
                        <td>:&nbsp;</td>
                        <td>{{ $data->shipping_address ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="panel-left2">
                <table>
                    <tr>
                        <td style="width: 100px;">Nama</td>
                        <td>:&nbsp;</td>
                        <td>{{ $data->customer->name }}</td>
                    </tr>
                    <tr>
                        <td style="width: 100px;">Alamat</td>
                        <td>:&nbsp;</td>
                        <td>{{ $data->customer->address }}</td>
                    </tr>
                    <tr>
                        <td style="width: 100px;">Kota</td>
                        <td>:&nbsp;</td>
                        <td>{{ $data->customer->city }}</td>
                    </tr>
                    <tr>
                        <td style="width: 100px;">Telp/HP</td>
                        <td>:&nbsp;</td>
                        <td>{{ $data->customer->phone }}</td>
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
                    <td><center>KAPASITAS</center></td>
                    <td><center>MODEL</center></td>
                    <td><center>KETERANGAN</center></td>
                </tr>
            </thead>
            <tbody>
                {{-- {{ dd($data->transactionProducts) }} --}}
                @foreach ($data->transactionProducts as $item)
                <tr>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ strtoupper($item->name) }}</td>
                    <td>{{ strtoupper($item->capacity) }}</td>
                    <td>{{ strtoupper($item->model) }}</td>
                    <td>{{ $item->product_notes }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <table style="width:100%; margin-top:30px;">
            <tr>
                <td>
                    <div>
                        <center><span>Tanda Terima</span></center>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <center>(.......................)</center>
                    </div>
                </td>
                <td>
                    <div>
                        <center><span>Driver</span></center>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <center>(.......................)</center>
                    </div>
                </td>
                <td>
                    <div>
                        <center><span>Gudang</span></center>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <center>(.......................)</center>
                    </div>
                </td>
                <td>
                    <div>
                        <center><span>Ekspedisi</span></center>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <center>(.......................)</center>
                    </div>
                </td>
                <td>
                    <div>
                        <center><span>Bagian Keuangan</span></center>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <center>(.......................)</center>
                    </div>
                </td>
            </tr>
        </table>
        <div class="panel-final" style="margin-top: 30px;">
            <div class="panel-left" style="width: 52% !important;">
                <div class="" style="margin-bottom:12px;">
                    <span>JASA TAMBAHAN : </span><span> -</span>
                </div>
                <div class="" style="margin-bottom:12px;">
                    <span>Ongkos Kirim : </span><span>.......................</span>
                </div>
                <div class="" style="margin-bottom:12px;">
                    <span>Terbilang : </span><span> <i>.......................</i></span>
                </div>
            </div>
            <div class="panel-left" style="width: 48% !important;">
                <div class="catatan" style="text-align: justify;">
                    <strong>CATATAN :</strong><br/>
                    <span>
                        Down Payment (uang muka) hangus jika transaksi dibatalkan. Barang yang sudah dibeli, tidak dapat ditukar/dikembalikan. Barang telah diperiksa oleh kedua belah pihak dan diterima dalam kondisi baik. Segala kerusakan yang terjadi setelah barang diterima menjadi tanggung jawab pembeli.</span>
                </div>
            </div>
        </div>
    </body>
</html>