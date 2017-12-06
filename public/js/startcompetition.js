var public_url = 'http://localhost/marketing/public/';
var public_url = 'http://localhost:8002/index.php/';

$('#btnStart').on('click', function () {
    var url = window.location.href;
    var myarr = url.split("=");
    var userid = myarr[1];

    localStorage.setItem('userid', userid);

    var checkedValue = $("#condition").is(":checked");
    if (checkedValue == false) {
        $('.check-error').html('Please accept the terms &amp; conditions');
    } else {
        //var token=localStorage.getItem('token');
        $('.check-error').html('');
        if ($(document).width() < 480) {

            bootbox.confirm({
                title: ' Incompatible device',
                message: 'Mobile devices do not support this, please use tablet or laptops',
                className: 'alertbox',
                buttons: {

                    cancel: {
                        label: 'close',
                        className: 'btn-danger hide'
                    }
                },
                callback: function (result) {
                    window.location = '/delete/user/'+localStorage.getItem('userid');
                    isMsgboxOpen = false;
                }
            });
        } else {
            bootbox.confirm({
                title: 'Start Competition',
                message: 'Are you sure you want to continue? ',
                className: 'alertbox',
                buttons: {
                    confirm: {
                        label: 'Yes',
                        className: 'btn-success'
                    },
                    cancel: {
                        label: 'No',
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: public_url + '/check/done/competition/' + userid,
                            type: 'GET',
                        })
                                .done(function (data) {
                                    if (data.status == '200') {
                                        console.log(data);

                                        window.location = public_url + 'do/competition';
                                        //alert(timerId);

                                    } else if (data.status == '401') {
                                        bootbox.confirm({
                                            title: 'Existing User ',
                                            message: 'Sorry you have already done the competition',
                                            className: 'alertbox',
                                            buttons: {
                                                confirm: {
                                                    label: 'Continue',
                                                    className: 'btn-success'
                                                },
                                                cancel: {
                                                    label: 'No',
                                                    className: 'btn-danger hide '
                                                }
                                            },
                                            callback: function () {
                                                localStorage.removeItem('test');
                                                // window.location = login_url;
                                            }});

                                    } else {
                                        console.log(data.message);
                                        // window.location = public_url;
                                    }
                                })
                                .fail(function (data) {
                                    console.log('failed');
                                    //window.location = public_url;
                                });
                    }
                }
            });
        }


    }
});



$('.btnClose').on('click', function () {

    window.location = public_url;

});

$('#skip').on('click', function () {
    localStorage.removeItem('userid');
  //  localStorage.removeItem();
    window.location = public_url;

});