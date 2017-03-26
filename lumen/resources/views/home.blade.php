<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/css/bootstrap.min.css">
    <style>
        /*.centered {*/
        /*position: fixed;*/
        /*top: 50%;*/
        /*left: 50%;*/
        /*transform: translate(-50%, -50%);*/
        /*}*/
    </style>
</head>
<body>

<div style="width:300px;margin: 300px auto;">
    <form role="form" method="POST" action="api/auth">
        <input id="email" type="email" class="form-control" name="email">
        <input id="password" type="password" class="form-control" name="password">
        {{--<button type="submit" class="btn btn-primary">Login</button>--}}
    </form>

    <div>
        <button id="loginbutton" type="button" class="btn btn-primary">Login</button>
    </div>

    <div>
        <button id="usersbutton" type="button" class="btn btn-primary">Users</button>
    </div>

    <div>
        <button id="userbutton" type="button" class="btn btn-primary">User</button>
    </div>

    <div id="result"></div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/js/bootstrap.min.js"></script>

<script>
    "use strict";

    $(document).ready(function () {
        var amt = 0, token, endpoint = 'http://cca-lumen.app',

            $loginbutton = $('#loginbutton').click(function (e) {
                $.ajax({
                    method: "POST",
                    url: endpoint + "/api/auth",
                    xhrFields: {withCredentials: false},
                    data: {
                        email: $('#email').val(),
                        password: $('#password').val()
                    }
                }).fail(function (event, jqxhr, settings, thrownError) {
                    $('#result').html('err: ' + settings);
                }).done(function (msg) {
                    console.log(msg);
                    $('#result').html(msg.data.token);
                    token = msg.data.token;

                });
            }),

            $usersbutton = $('#usersbutton').click(function (e) {
                $.ajax({
                    url: endpoint + '/api/users',
//                    url: 'http://api.erictotten.info/api/users',
                    data: {token: token},
                    //headers: {"Authorization": "Bearer " + token},
                    xhrFields: {
                        withCredentials: false
                    }
                }).fail(function (event, jqxhr, settings, thrownError) {
//                    console.log(event);
//                    console.log(jqxhr);
//                    console.log(settings);
//                    console.log(thrownError);
                    $('#result').html('err: ' + settings);
                }).done(function (data) {
                    $('#result').append(data[0].account_name);
                    console.log(data);
                });
            }),

            $userbutton = $('#userbutton').click(function (e) {
                $.ajax({
                    url: endpoint + '/api/users/4',
                    data: {token: token},
                    //headers: {"Authorization": "Bearer " + token},
                    xhrFields: {
                        withCredentials: false
                    }
                }).fail(function (event, jqxhr, settings, thrownError) {
//                    console.log(event);
//                    console.log(jqxhr);
//                    console.log(settings);
//                    console.log(thrownError);
                    $('#result').html('err: ' + settings);
                }).done(function (data) {
                    console.log(data);
//                $('#result').html(JSON.parse(data));

                });
            });


    });

    // setInterval( function() {
    //   amt -= 1;
    //   $mybutton.css(
    //     {
    //     transform:  "translateY(" + amt + "px)" } )
    //     }, 20 );


    //   })

    // $mybutton.animate(
    //   step: function(now,fx) {
    //     $(this).css('-webkit-transform','rotate('+now+'deg)');
    //     }

    //   });


</script>
</body>
</html>