<!DOCTYPE html>
<html>
    @include('includes.header')
    <link rel="stylesheet" href="../css/animatewow.css">
    <link rel="stylesheet" href="../css/hover-min.css">
    <body>
        <div class="main_container_challenge">
            <div class="bg_inner"></div>
            <div class="container-fluid">
                <div class="container maincontent">
                    <div class="challenge_content_first">
                        <div class="innercontent">
                            <h1 class="challengetitle wow jackInTheBox" data-wow-duration="2s" data-wow-delay="1s"> Are you ready for the Challenge? </h1>   
                            <div class="row">
                                <div class="btnpanel wow bounceIn" data-wow-duration="1s" data-wow-delay="3s">
                                   <button type="" id="yesbtn" class="compbtn hvr-grow-shadow">YES</button>
                                   <button type="" id="nobtn" class="compbtn hvr-grow-shadow">NO</button> 
                                </div>
                            </div> 
                        </div>              
                    </div>
                </div>
            </div>  
        </div>
        @yield('content')
        <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
        <script src="../js/wow.min.js"></script>
        <script>
            new WOW().init();
        </script>
         <script>
            $('#yesbtn').on('click',function(){
                
                window.location='/welcomeform';
                
                
                
            });
        </script>
        @include('includes.footer')
    </body>
</html>
