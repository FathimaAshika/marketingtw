<!DOCTYPE html>
<html lang="en">
@include ('includes.header')
<body>
<div class="bg_container"></div>
<div class="container-fluid">
    <div class="container">
        <div class="col-md-10 col-sm-12 col-md-offset-1 col-sm-offset-0">
            <h1> Competition </h1>
            <div class="competition_content_static">
                <div class="info_content">
                    <div class="time"> 
                        Remaining <i id="time"></i> 
                        <i id="timer" class="hide"></i>
                        <div class="remaining_clicks" >Clicks - <i id="mouse-clicks"> 0 </i></div> 
                       
                    </div>
                    <div class="logo ">
                        <i class="icon-tw logo_tw"></i></div>
                        <div class="marks" >Corrects <i id="correct">0</i></div>
                </div>
                <div class="image_content">
                    <div class="col-md-6 col-sm-6 orginal_content" >
                        <img src="{{asset('images/original.png')}}" class="image_competition original" usemap="#original" >
                        <map name="original" id="original"></map>
                    </div>
                    <div class="col-md-6 col-sm-6 change_content">
                        <img src="{{asset('images/changed.png')}}" class="image_competition changed" usemap="#duplicate" id="changed">
                        <map name="duplicate" id="duplicate"></map>
                    </div>
                    <div class="clearfix"></div>
                    <div class="error"></div>

                </div>
            </div>
        </div>
    </div>
</div>



    @include ('includes.footer')
   <script src="{{asset('js/table.js')}}" type="text/javascript"></script>   



</body>
</html>
