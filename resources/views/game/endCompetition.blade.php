<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<body>
    @include ('includes.header')
<div class="bg_container"></div>
<div class="container-fluid">
<script type="text/javascript">
    
    history.pushState({ page: 1 }, "Title 1", "#no-back");
window.onhashchange = function (event) {
  window.location.hash = "no-back";
};

</script>
    <div class="container">
        <div class="col-sm-8 col-sm-offset-2">
            <h1> Competition </h1>
            <div class="thanx_content">
                <h2> Thank You</h2>
            </div>
            <div class="center_content">
                <div class="logo"> <i class="icon-tw logo_tw"></i> </div>
                <div class="aling_para">
                    </br> <p> Thank you for signing up to Tutor Wizard and entering our competition. We wish
                        you best of luck and will be announcing the winners on the 6th of March at 5pm.</p> </br>
                    <p> The last step to qualify for your chance to win is like our facebook page
                        where you can be kept up to date about new competitions and special offers.</p></div>
               
                
                 <div class="fb_logo"> <img src="{{asset('images/fb_logo.png')}}" class="image_fb"> </div>
                <div class="aling_para" ><p> You will be able to enter a new dimencion for smart learning with your username
                        and password on the 8th of March.</p></div>
            </div>

            <div class="row">
                <button type="button" class="btnClose btn_position" align="center" >Close</button>
            </div>

        </div>

    </div>
</div>
@include ('includes.footer')
  <script src="{{asset('js/startcompetition.js')}}" type="text/javascript"></script>   




</body>
</html>
