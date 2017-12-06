$('#challengebtn').on('click', function () {

    validateForm();

});


function validateForm() {

    var fullname = $('#fullname').val();
    var mobile = $('#mobile').val();
    var email = $('#email').val();
    var data = {

        fullname: fullname,
        mobile: mobile,
        email: email
    };
    if (mobile == '' || mobile.length < 9 || fullname == '' || email == '') {

        if (mobile == '' || mobile.length < 9) {
            $('#mobile').addClass('errormsg');
        }
        if (fullname == '') {
            $('#fullname').addClass('errormsg');
        }
        if (email == '') {
            $('#email').addClass('errormsg');
        }

    } else {

        $.ajax({
            type: 'POST',
            url: '/user/add/details',
            dataType: "json",
            data: data,

            success: function (status) {
                if (status.status == '200') {
                    window.location = '/start/competiton?uid='+status.data;
                } else {
                    window.location = '/welcomeform';
                }
            },
            error: function () {
                window.location = '/welcomeform';
            }

        });
    }

}