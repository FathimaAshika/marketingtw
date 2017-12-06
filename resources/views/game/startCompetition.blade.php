<!DOCTYPE html>
<html lang="en">
@include ('includes.header')
<body>
<div class="bg_container"></div>
<div class="container-fluid">
    <div class="container ">
        <div class="col-sm-8 col-sm-offset-2">
            <h1 > Competition </h1>
            <div class="row center_content">
                <div class="row">
                    <div class="logo start_logo"><img style="width:100px; height:auto;" src="{{asset('images/logo.png')}}" alt=""></div>
                </div>
                
                <div class = "aling_para">
                    <p> Thank you for signing up to Tutor Wizard, the new way for smart learning. </p> <br>
                    <p>You have only one chance to spot all the differences. Once you press START you have<br>
                     a maximum of  2  minutes to find all 13 differences, so be ready.</p><br/>
                    <p> (Do not close or exit the browser before you have finished as this will log your results automatically. We suggest you DO NOT use a mobile phone, as it will be very difficult) </p><br>
                    <p> Good luck with the competition.</p>
                </div>

                <div class = "row checkBox termslink">
                    <input type="checkbox" name="condition" id="condition"> &nbsp; I have read, understood and agreed to the <a href="terms" target="_blank">term and conditions</a> of the Competition.
                    <div class="check-error"> </div>
                </div>
            </div>

            <div class="row ">
                <button type="button" class="btnStart btn_position" id="btnStart">START</button>
                <button type="button" class="btnStart btn_position" id="skip">CLOSE</button>
            </div>
        </div>
    </div>
</div>

 @include ('includes.footer')
 <script src="{{asset('js/startcompetition.js')}}" type="text/javascript"></script> 
</body>
</html>
