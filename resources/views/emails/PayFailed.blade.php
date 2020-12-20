
<html>

<head>
    <meta name=viewport content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <title>healthcareOkay.com - Leading healthcare jobs board in the United states</title>
    <style>
        /*universal */
        
        img {
            margin: 0;
            padding: 0;
            max-width: 100%;
            display: block;
            border: none;
            outline: none;
        }
        
        @media (max-width:767px) {
            .mob_100 {
                width: 100% !important;
                padding-left: 10px !important;
                padding-right: 10px !important;
            }
            .partner_img,
            .partner_des {}
            .partner_des {
                width: 87%;
                margin-top: 11px;
                padding-left: 9px;
            }
            .mob_auto_img {
                margin: auto !important;
            }
            .mob_95 {
                width: 95% !important;
                padding-left: 10px !important;
                padding-right: 10px !important;
            }
            .mob_hide {
                display: none;
            }
            .desk_hide {
                display: block;
            }
            .our_part_img {
                margin-bottom: 8px;
            }
        }
        
        @media (min-width:768px) {
            .mob_hide {
                display: block;
            }
            .desk_hide {
                display: none;
            }
        }
        /*end universal */
    </style>
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="margin:0 auto;padding:0;font-family:Calibri;font-size:16px;">
    <div border="0" cellpadding="0" cellspacing="0" style="max-width:800px;width:100%;margin:0 auto;padding:0;overflow-x:hidden;">
        <!-- begin new header-->
        <div style="width:100%; text-align: center;">
            <a href="https://healthcareokay.com" target="_blank"> <img src="{{asset('customer_head.png')}}" alt="" style="margin: auto;"></a>
        </div>

        <div style="width:80%;margin:10px auto;" class="mob_100">
            <h3 style="font: normal normal bold 20px/24px Calibri; letter-spacing: 0px;color: #2f3ee8;opacity: 1; text-align: center;">Payment Failed</h3>
        </div>
        <!-- end new header-->
        <div style="width:80%;margin:18px auto; text-align: center;">
            
        </div>
        <div style="width:80%;margin:10px auto;text-align:center;">
          <p>Your payment of  ${{$data['amount']}} FAILED. Your job was successfully posted but will be invisible. Use the Edit link sent to your email to resubmit payment information. Make sure your card is Visa or Mastercard. Other cards are not supported</p>
          <p>
            Invoice Amt: ${{$data['amount']}}<br>
            Receipt No: {{$data['receipt']}}
          </p>
        </div>

        <!--new footer-->
        <div style="width:80%;margin:20px auto; text-align: center;">
            <div style="">
            </div>
        </div>

        <div style="width:100%;text-align: center;">
            <br><br>
            <h3 class="heading"><i>About healthcareokay.com</i></h3>
            <i><p style="color:#000;">Healthcare Okay is the most popular Healthcare jobs board in the United States.<br> We are trusted by millions of Healthcare professionals and tens of thousands of Healthcare companies.</p></i>
        </div>

        <div style=" width:80%;
            background:#908c8d 0% 0% no-repeat padding-box;
            border-bottom-right-radius:13px;
            border-bottom-left-radius:13px;
            padding:12px 8px 9px;
            height:79px;
            margin:-2px auto;" class="mob_95">
            <div style="width:100%;float:left;">
                <div style="text-align:center;margin-bottom:10px;margin-top:10px;">
                </div>
            </div>
            <div style="width:100%;text-align:center;margin-bottom:30px;letter-spacing:0px;color:#ffff;float:left;line-height:20px;">
                You are receiving this email because you are a valued customer on healthcareokay.com<br> HealthcareOkay | CA | United States
            </div>
        </div>
        <!--end new footer-->
    </div>
</body>

</html>
	     