<!DOCTYPE html>
<html>
    @include('includes.header')
    <link rel="stylesheet" href="../css/animatewow.css">
    <link rel="stylesheet" href="../css/hover-min.css">
    <body>
        <div class="main_container_challenge">
            <div class="bg_inner"></div>
            <div class="container-fluid">
                <div class="container">
                    <div class="challenge_content">
                        <div class="inner_content">
                            <div class="row wow bounceIn" data-wow-duration="1s" data-wow-delay="1s">
                                <h2 class="enterdetails"> Enter Competitor Details Below</h2>
                                <div class="col-md-12">

                                    <form  id="challengform">

                                        <div class="form-group cform">
                                            <input type="text" class="form-control forminput" id="fullname"  name="fullname" placeholder="Full Name">
                                        </div>

                                        <div class="form-group cform">
                                            <input type="number" class="form-control no-spin forminput" id="mobile" name="mobile" placeholder="Mobile Number">
                                        </div>

                                        <div class="form-group cform">
                                            <input type="email" class="form-control forminput" id="email" name="email" placeholder="Email Address">                                 
                                        </div>


                                    </form>
                                    <div class="form-group">
                                        <input type="button" value="Submit" id="challengebtn"  class="btn btnNew btnSubmit hvr-glow">
                                    </div>
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
        <script src="../js/welcome.js"></script>
  <!--        <script>
              new WOW().init();
          </script>-->

        @include('includes.footer')
    </body>
</html>
