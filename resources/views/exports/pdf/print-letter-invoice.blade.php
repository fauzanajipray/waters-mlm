<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Surat Invoice</title>
    </head>
    <style>
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
            <div class="panel-left">
                KEPADA Yang terhormat,
                <table>
                    <tr>
                        <td style="width: 70px;">Nama</td>
                        <td>:&nbsp;</td>
                        <td>--</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Alamat</td>
                        <td>:&nbsp;</td>
                        <td>--</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Kota</td>
                        <td>:&nbsp;</td>
                        <td>--</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Telp/HP</td>
                        <td>:&nbsp;</td>
                        <td>--</td>
                    </tr>
                </table>
            </div>
            <div style="clear:both;"></div>
        </div>
        <div style="margin-top: 9px;">
            <div class="panel-left">
                <span><strong>FAKTUR PENJUALAN</strong> : <span>--</span><br/>
                <span>Tanggal Kirim : </span><span>--<span><br/>
                <span>Keterangan : </span><span>--</span><br/>
            </div>
            <div class="panel-left">
                <span>Alamat Kirim </span><span>:</span><span>---</span>
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
                <tr>
                    <td>---</td>
                    <td>---</td>
                    <td>---</td>
                    <td>---</td>
                    <td>---</td>
                    <td>---</td>
                    <td>---</td>
                    <td>---</td>
                </tr>
            </tbody>
        </table>
        <table style="width:100%; margin-top:12px;">
            <tr>
                <td>
                    <div>
                        Terbilang : ###
                    </div>
                </td>
                <td>
                    <div>
                        Total : ---
                    </div>
                </td>
            </tr>
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