/*globals $, jQuery */

(function () {
    "use strict";
    if (!$('#password').length) {
        return false;
    }
    var last_pwd = '',
        score,
        word,
        color_ind,
        userdata = [],
        word_map = [
            ["", "Very weak"],
            ["#c81818", "Weak"],
            ["#ffac1d", "Alright"],
            ["#91D32D", "Good"],
            ["#27b30f", "Great!"]
        ],
        color_map = [
            "#e8e8e8",
            "#c81818",
            "#ffac1d",
            "#91D32D",
            "#27b30f"
        ],
        password_test = function () {
            var pwd = $('#password').val();
            if (pwd === last_pwd) {
                return;
            }

            last_pwd = pwd;
            if ($('#username').val() !== '') {
                userdata.push($('#fname').val());
            }
            if ($('#email').val() !== '') {
                userdata.push($('#lname').val());
            }
            if (userdata.length < 1) {
                return;
            }

            score = zxcvbn(pwd);
            score = score.score;
            word = word_map[score];
            color_ind = score;

            $('.password_strength').css('color', word[0]);
            $('.password_strength_desc').html(pwd.length ? word[1] : '&nbsp;').css('color', word[0]);
            $('.password_strength').css('backgroundColor', color_map[color_ind]);
            if (score === 0) {
                $('.password_strength').css('width', "0%");
            } else {
                $('.password_strength').css('width', (score * 25) + "%");
            }
        };

    setInterval(password_test, 350);
}(jQuery));