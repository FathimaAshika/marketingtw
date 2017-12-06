
//var token='af2dbf95f78d7f996f97af55a044a6526194';
var max_clicks = 20;
var clicks = 0;
var correct_answers = 13;
var correct = 0;
var time = 120;
var time_taken = 0;
var maxClicksExceeded = false;
var isTimeout = false;
var isMsgboxOpen = false;
var a = 0;
var b = 0;
var tt = 0;
var countdown;

var public_url = 'http://localhost/marketing/public/';

var public_url = 'http://localhost:8002/index.php/';
var correct_precentage = correct / correct_answers * 100;

$('html .competition_content_static').ready(function ($) {
    $('#timer').html(time);
    sessionStorage.setItem('countdown',120);
    countdownTimer();
    window.setInterval(
            function () {
                tt = tt + 1;
            }, 1000);

});


function countdownTimer() {


    var i = document.getElementById('timer');
   // countdown = 120;
   countdown = parseInt(sessionStorage.getItem('countdown')) - 1

    if (isNaN(countdown)) {
        countdown = 120;
    }




    var mins = parseInt(countdown / 60);
    var seconds = countdown % 60;



    i.innerHTML = countdown;

    sessionStorage.setItem('countdown', countdown);



    if (parseInt(countdown / 60) < 9) {
        mins = "0" + parseInt(countdown / 60);
    }

    if (countdown % 60 <= 9) {
        seconds = "0" + countdown % 60;
    }

    document.getElementById('time').innerHTML = mins + ":" + seconds;



    if (parseInt(countdown) == 0 || parseInt(countdown) < 0 || maxClicksExceeded) {

        if (parseInt(countdown) < 0) {
            document.getElementById('time').innerHTML = "00:00";
        }

        isTimeout = true;
        finishedCompetition();
        countdown = 0;
        clearInterval(timerId);

    }
}

var timerId = setInterval(function () {
    countdownTimer();
}, 1000);

if ($(document).width() < 480) {

    // mobile 
    msgBox1('Mobile devices do not support this, please use tablet or laptops');
    //window.location='/';

} else if ($(document).width() < 1200 && $(document).width() > 780) {
    // tab 
    var cordinates = ['50,30,25', '224,45,20', '176,381,20',
        '216.5,127,20', '303.5,126,20', '109,162,20',
        '238,194,20', '344,213,30', '248,297,20', '323,251,20',
        '251,359,20', '312,303,20', '29,288,20'];
    for (var i = 0; i < cordinates.length; i++) {
        $('#original, #duplicate').append('<area shape="circle" coords="' + cordinates[i] + '" href="javascript:void(0)" onclick="correctAnswer(\'' + cordinates[i] + '\')" alt="Mercury">')
    }

} else {
// desktop 
    var cordinates = ['65,44,25',
        '278,53,30', '269,162,20',
        '377.5,154,20',
        '137.5,199,30',
        '294,238,20',
        '423,261,20',
        '307,366,20',
        '399,308,20',
        '380,370,20',
        '216,468,30',
        '308,446,30',
        '36.5,365,20'];
    for (var i = 0; i < cordinates.length; i++) {
        $('#original, #duplicate').append('<area shape="circle" coords="' + cordinates[i] + '" href="javascript:void(0)" onclick="correctAnswer(\'' + cordinates[i] + '\')" alt="Mercury">')
    }

}

$('#mouse-clicks').html(clicks + " / " + max_clicks);
$('#correct').html(correct + " / " + correct_answers);




$('.image_content').on('click', function (event) {
    event.preventDefault();


    if (clicks < max_clicks) {
        clicks++;
        $('#mouse-clicks').html(clicks + " / " + max_clicks);

    } else if (clicks == max_clicks) {
        maxClicksExceeded = true;
        //alert(time_taken);
        finishedCompetition();
    } else {

    }
    if (correct >= correct_answers) {
        time_taken++;
        finishedCompetition();
    }

});

$('.image_competition ').on('click', function (event) {
    event.preventDefault();
    //alert(event.pageX + ' , ' + event.pageY);
    /* Act on the event */
    var parentOffset = $(this).parent().offset();
    var left = event.pageX - parentOffset.left - 15;
    var top = event.pageY - parentOffset.top - 15;

    console.log(left, 'gggggggg', top);
    $('.change_content, .orginal_content').append('<div class="circle wrong animated zoomIn" style="left:' + left + 'px; top:' + top + 'px "></div>')

    setTimeout(function () {
        $('.change_content .circle.wrong, .orginal_content .circle.wrong').addClass('hide');
    }, 500);

});

$('.alertbox ').on('hidden.bs.modal', function () {
    isMsgboxOpen = true;
})


function correctAnswer(coordinates) {

    if (!maxClicksExceeded && !isTimeout) {

        coordinates = coordinates.split(",");
        var left = coordinates[0] - coordinates[2] + 15;
        var left_or = coordinates[0] - coordinates[2];
        var top = coordinates[1] - coordinates[2];
        var radius = coordinates[2] * 2;

        correct++;
        //  finishedCompetition();

        $('#correct').html(correct + " / " + correct_answers);

        $('.change_content').append('<div class="circle green animated zoomIn" style="left:' + left + 'px; top:' + top + 'px; width:' + radius + 'px; height:' + radius + 'px  "></div>')

        $('.orginal_content').append('<div class="circle green animated zoomIn" style="left:' + left_or + 'px; top:' + top + 'px; width:' + radius + 'px; height:' + radius + 'px  "></div>');

    }

}


function finishedCompetition() {

    var correct_precentage = correct / correct_answers * 100;

    console.log(correct_precentage);
    a = "0" + Math.floor(tt / 60);
    b = tt % 60;

    if (b <= 9) {
        b = "0" + b;
    }
    if (b < 9) {
        b = "0" + b;
    }
    time_taken = a + ":" + b;
    if (correct_precentage >= 75 && !isMsgboxOpen) {
        msgBox('You have completed the competition. You spotted ' + correct + ' differences using ' + clicks + ' clicks in a time of ' + time_taken + ' Once the winner has been announced we will reveal all the differences on out facebook page. Good luck!');

    } else if (correct_precentage >= 50 && !isMsgboxOpen) {
        msgBox('You have completed the competition. You spotted ' + correct + ' differences using ' + clicks + ' clicks in a time of ' + time_taken + ' Once the winner has been announced we will reveal all the differences on out facebook page. Good luck!');
    } else if (!isMsgboxOpen) {
        msgBox('You have completed the competition. You spotted ' + correct + ' differences using ' + clicks + ' clicks in a time of ' + time_taken + ' Once the winner has been announced we will reveal all the differences on out facebook page. Good luck!');
    }

    isMsgboxOpen = true;

}

function msgBox(msg) {
    var uid = 215;
    bootbox.confirm({
        title: 'Competition Completed',
        message: msg,
        className: 'alertbox',
        buttons: {
            confirm: {
                label: 'Continue',
                className: 'btn-success'
            },
            cancel: {
                label: 'No',
                className: 'btn-danger hide'
            }
        },
        callback: function (result) {
            var t = localStorage.getItem('test');
            //localStorage.setItem('logout', t);
            var correct_precentage = correct / correct_answers * 100;
            //var token = $('#token').val();
            var userid =localStorage.getItem('userid');
            var data = {
                userid: userid,
                correct_precentage: correct_precentage,
                time_taken: time_taken,
                clicks: clicks
            };
            $.ajax({
                'type': "post",
                'url': public_url + '/user/update/score/details',

                data: data,
                success: function (data) {
                    console.log(data);
                    if (data.status == '200') {
                        sessionStorage.removeItem('countdown');
                        
                        window.location = public_url+'end/competition';
                        
                    } else {
                        window.location = public_url;
                    }
                },
                error: function (data) {
                    console.log(data);
                }
            });
        }
    });
}
function msgBox1(msg) {

    bootbox.confirm({
        title: ' Incompatible device',
        message: msg,
        className: 'alertbox',
        buttons: {

            cancel: {
                label: 'close',
                className: 'btn-danger hide'
            }
        },
        callback: function (result) {
            window.location = public_url;
            isMsgboxOpen = false;
        }
    });
}





