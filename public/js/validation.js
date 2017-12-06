$('#username').on('click', function () {
    $('#error').hide();
});
$('#username').on('change', function () {
    $('#error').html('');
    $('#error').show();
    $username = $('#username').val();
    $.ajax({
        url: 'checkUsername/' + $username,
        'type': 'get',
        success: function (data) {
            if (data == 'exist') {
                $('#error').append('<h4>Username exists</h4>');
            }
        },
        error: function (data) {
            console.log(data);
        }
    });
});
function checkValidDate() {
    $('#date_error').html('');
    var d = {
        dd: $('#dd').val(),
        mm: $('#mm').val(),
        yy: $('#yy').val()
    };
    if (d.dd != null && d.mm != null && d.yy != null) {
        var dat = new Date(0);
        dat.setDate(parseInt(d.dd));
        dat.setFullYear(d.yy);
        dat.setMonth(d.mm - 1);
        console.log(JSON.stringify(d));
        console.log(dat);
        if (dat.getDate() != (d.dd)) {

            $('#date_error').append('<h4>Not a valid date </h4>');
        } else {

        }
    } else {
        return false;
    }
}
// +960 301 0058
