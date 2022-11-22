<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Form Registrasi Member</title>
    </head>
    <style>
        .body {
            font-family: Arial, Helvetica, sans-serif;
            border: 1px solid gray;
            width: 100%;
            height: 99%;
        }

        .panel-left {
            float:left;
            width:55%;
        }
        .starter-kit {
            background-color: red;
            padding: 10px;
            width: 140px;
            color: #fff;
        }
        #price {
            font-size: 20px;
        }
        small {
            font-size: 10px;
            position: relative;
            top: -10px;
        }
        #form {
            margin-top: 12px;
        }
        #form h2{
            margin-top: 12px;
            color: red;
            text-decoration: underline;
        }

        .line {
            margin-bottom: 12px;
        }

        .line .box {
            float:left;
        }
        .label {
            width: 135px;
        }
        .val {
            width: 63%;
            height: 20px;
            border-bottom: 1px solid black;
        }
    </style>
    <body>
       <div class="body">
            <div class="panel-left" style="padding-left: 23px; padding-top: 20px;">
                <img src="{{ public_path('/images/cna-indonesia.jpeg')}}" width="240px"><br>
                <div style="padding-left: 23px;">
                    Jl. Mayjend Sutoyo Siswomiharjo No. 942<br/>
                    Semarang, 50134 - Jawa Tengah
                </div>
                <div style="padding-left: 20px; margin-top: 12px;">
                    <table>
                        <tr>
                            <td>Telepon&nbsp;&nbsp;</td>
                            <td>:</td>
                            <td>(+62-24) 8414756</td>
                        </tr>
                        <tr>
                            <td>Facimile&nbsp;&nbsp;</td>
                            <td>:</td>
                            <td>(+62-24) 7462356</td>
                        </tr>
                        <tr>
                            <td>Website&nbsp;&nbsp;</td>
                            <td>:</td>
                            <td>www.waters.co.id</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="panel-left" style="padding-left: 100px; padding-top: 40px;">
                <img src="{{ public_path() . '/images/llogo-waters.jpg'}}" width="120"/> 
                <div class="starter-kit">
                    <center>
                        <span>STARTER KIT</span>
                        <hr>
                        <small>Rp.</small>
                        <span id="price">100.000</span>
                    </center>
                </div>
            </div>
            <div style="clear:both;"></div>
            <div id="form">
                <center>
                    <h2>FORM PENDAFTARAN MEMBER WATERS</h2>
                </center>
                    <div id="find-body" style="padding-left: 86px;">
                        <div class="line">
                            <div class="box label">1. Nama</div>
                            <div class="box bot">:</div>
                            <div class="box val"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">
                            <div class="box label">2. Jenis Kelamin</div>
                            <div class="box bot">:</div>
                            <div class="box val"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">
                            <div class="box label">3. Tanggal Lahir</div>
                            <div class="box bot">:</div>
                            <div class="box val"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">
                            <div class="box label">4. Mobile Phone</div>
                            <div class="box bot">:</div>
                            <div class="box val"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">
                            <div class="box label">5. Email</div>
                            <div class="box bot">:</div>
                            <div class="box val"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">
                            <div class="box label">6. Alamat</div>
                            <div class="box bot">:</div>
                            <div class="box val"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">

                            <div class="box val" style="width: 85.8%;"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">
                            <div class="box val" style="width: 85.8%;"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">
                            <div class="box val" style="width: 85.8%;"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">
                            <div class="box label">7. Upline</div>
                            <div class="box bot">:</div>
                            <div class="box val"></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div class="line">
                            <div class="box label">8. Unigue Number</div>
                            <div class="box bot">:</div>
                            <div class="box val"></div>
                            <div style="clear: both;"></div>
                        </div>
                    </div>
            </div>
            <div class="signature" style="padding-left: 86px; padding-top: px;">
                <div class="panel-left">
                    <div>
                        <br/>
                        <br/>
                        <div style="border:0.5px solid black; width: 150px;"></div>
                        <div style="margin-left: 17px; margin-top: 5px;">Tanggal - Date</div>
                    </div>
                </div>
                <div class="panel-left">
                    <div>
                        <br/>
                        <br/>
                        <div style="border:0.5px solid black; width: 210px;"></div>
                        <div style="margin-left: 17px; margin-top: 5px;">Tanda Tangan - Signature</div>
                    </div>
                </div>
            </div>
       </div>
    </body>
</html>