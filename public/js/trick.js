/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {

    // **************************************************************************************************************
    //
    //                                             LOAD MORE COMMENTS BUTTON         
    //
    // **************************************************************************************************************

    let loadComments = $('#load-more-comments');

    /** Create a Javascript Date from the string return in JSON data : 2020-08-08T18:10:55+00:00 */
    var createDateJS = function(dateString) {
        let dateJS = new Date(dateString); /** Sat Aug=7 08 2020 20:10:55 GMT+0200 (heure d’été d’Europe centrale) */
        /** month = [0,11] */
        let month = dateJS.getMonth();
        dateJS.setMonth(month+1);
        /** hours */
        let hour = dateJS.getHours();
        dateJS.setHours(hour-2);

        return dateJS; /** Sat [Aug=8] 08 2020 18:10:55 GMT+0200 (heure d’été d’Europe centrale) */
    };

    /** function witch add zero on front of the month digit and the day digit, when it's lower than ten */
    var createDateToDisplay = function(dateJS) {
        let day = dateJS.getDate();
        let month = dateJS.getMonth();
        let date = (day < 10 ? ('0' + day) : day) + '/' + (month < 10 ? ('0' + month) : month) + '/' + dateJS.getFullYear();

        return date;
    };

    /** function witch build the HTML to display an additional comment. */
    var displayComment = function(comment) {
        
        let filename = comment.user.profile ? comment.user.profile : 'default.jpg';
        
        let dateJS = createDateJS(comment.createdAt);
        let date = createDateToDisplay(dateJS);
        let time = dateJS.getHours() + ':' + dateJS.getMinutes() + ':' + dateJS.getSeconds();

        $('#comments').append('<div class="card comments col-12 col-sm-12"></div>');
        $('div.card.comments:last').append('<div class="row no-gutters"></div>').hide();
        
        let profileElement = document.createElement('div');
        $(profileElement).addClass('col-2 col-lg-1 pt-3');
        $(profileElement).append(`<img src="/../uploads/images/profile/${filename}" class="card-img" alt="Image de profil de ${comment.user.username}">`);

        let contentElement = document.createElement('div');
        $(contentElement).addClass('col-10 col-lg-11');
        $(contentElement).append('<div class="card-body"></div>');

        $('.no-gutters:last').append(profileElement, contentElement);
        
        $('.card-body:last').append(
            '<h5 class="card-title font-weight-bold">' + comment.user.username + '</h5>',
            '<p class="card-text">' + comment.content + '</p>',
            '<p class="card-text"><small class="text-muted">Posté le ' + date + ' à ' + time + '</small></p>'
        );

        $('div.card.comments:last').slideDown(1000);
    };
    
    /** more comments are loads by AJAX request when a user click on "load-more" button  */
    loadComments.click(function(e) {
        e.preventDefault();
        let url = loadComments.attr('href') + '/' + $('div.card.comments').length;
        $.getJSON(url).done(function(data) {
            // if no more comments
            if (data.length === 0) {
                // then delete "load-more" button on trick page
                loadComments.slideUp('slow', 'linear');
                $('#comments').animate({'margin-bottom': '100px'}, 'slow', 'linear');  
            } else {
                // else add comments on trick page
                $.each(data, function (index, comment) {
                    displayComment(comment);
                });
            }
        });
    });
});
