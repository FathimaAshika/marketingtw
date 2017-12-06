//var public_url = 'http://localhost/marketing/public/';
//$('.btnNew').on('click', function () {
//    //alert('hcgcxg');
//    
//    var email =$('#email').val();
//    var fullname = $('#fullname').val();
//    var mobile = $('#mobile').val();
//    
//    var data ={
//        email : email ,
//        fullname : fullname ,
//        mobile : mobile,
//    };
//    
//      $.ajax({
//        url: public_url + 'user/add/details',
//        type: 'POST',
//        data : data,
//        success: function(data){
//             if(data.status == '200'){
//                      window.location = 'http://localhost:8002/startcompetition?token='+data.userId;  
//                }
//                else if(data.status == '401'){
// 			    window.location = 'http://tutorwizard.lk/';
//                        }
//		else{
//                      window.location = 'https://tutorwizard.org/';
//        }},
//        error : function(data){
//            console.log(data);
//        }
//    });
////            .done(function (data) {
////                if(data.status == '200'){
////                      window.location = 'http://localhost:8002/startcompetition?token='+data.userId;  
////                }
////                else if(data.status == '401'){
//// 			    window.location = 'http://tutorwizard.lk/';
////                        }
////		else{
////                      window.location = 'https://tutorwizard.org/';
////                }
////           
////           //   alert(data.status);
////             //  console.log(data);
////            })
////            .fail(function (data) {
////
////                window.location = 'http://tutorwizard.lk/';
////                 // alert(data.status);
////            })
////                    .always(function(){
////                        console.log('always');
////                    });
//    
//
//});
//
//
//    