/*globals $, jQuery */
(function () {
    "use strict";
    var last_pwd = '',
        score,
        word,
        color_ind,
        userdata = [],
        word_map = [
            ["", "Too short"],
            ["e31111", "Very weak"],
            ["#c81818", "Weak"],
            ["#ffac1d", "Alright"],
            ["#91D32D", "Good"],
            ["#27b30f", "Great"]
        ],
        color_map = [
            "#e8e8e8",
            "#e8e8e8",
            "#c81818",
            "#ffac1d",
            "#91D32D",
            "#27b30f"
        ],
        password_test = function () {
            if( !$('#password').length || !$('.password_strength').length ){
                return false;
            }
            
            var pwd = $('#password').val();
            if (pwd === last_pwd) {
                return;
            }

            last_pwd = pwd;
            if ($('#username').val() !== '') {
                userdata.push($('#username').val());
            }
            if ($('#email').val() !== '') {
                userdata.push($('#email').val());
            }

            if (pwd.length >= 6) {
                score = zxcvbn(pwd);
                score = score.score + 1;
            } else {
                score = 0
            }
            
            word = word_map[score];
            color_ind = score;

            $('.password_strength').css('color', word[0]);
            $('.password_strength_desc').html(pwd.length ? word[1] : '&nbsp;').css('color', word[0]);
            $('.password_strength').css('backgroundColor', color_map[color_ind]);
            if (score <= 0) {
                $('.password_strength').css('width', "0%");
            } else {
                $('.password_strength').css('width', ((score * 25) - 25) + "%");
            }
        };

    setInterval(password_test, 350);
}(jQuery));