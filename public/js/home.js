/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {

    // **************************************************************************************************************
    //
    //                                             Display tricks             
    //
    // **************************************************************************************************************
    $('#tricks-down a').click(function(e) {
        e.preventDefault;
        $('#tricks').removeClass('d-none');
        $('#load-more-tricks').removeClass('d-none');
        $('#tricks-up').removeClass('d-none');
        $('html,body').animate({scrollTop: $('#tricks').offset().top}, 'slow');
    });

    $('#tricks-up a').click(function(e) {
        e.preventDefault;
        $('html,body').animate({scrollTop: $("#tricks").offset().top}, 'slow');
    });

    // **************************************************************************************************************
    //
    //                                             LOAD MORE TRICKS BUTTON             
    //
    // **************************************************************************************************************

    let loadTricks = $('#load-more-tricks');

    var displayTrick = function(trick) {

        let firstPicture = trick.firstPicture ? trick.firstPicture.filename : (trick.pictures ? trick.pictures[0].filename : 'default.jpg');

        let containerCardTrick = $(document.createElement('div'));
        containerCardTrick.addClass('col-sm-6 col-md-4 col-lg-3 my-2');
        containerCardTrick.append('<div class="card tricks"></div>').hide();

        let imageTrickContainer = $(document.createElement('div'));
        imageTrickContainer.addClass('div-card-img d-flex align-items-center justify-content-center');
        imageTrickContainer.append('<img src="/../uploads/images/200/'+ firstPicture +'" class="card-img-top" alt="Picture to illustrate '+ trick.name +' snowboard trick.">');

        let cardBodyTrick = $(document.createElement('div'));
        cardBodyTrick.addClass('card-body p-0 text-center');
        cardBodyTrick.append('<h2 class="card-title"></h2>');
        
        $('#tricks').append(containerCardTrick);
        $('.card.tricks:last').append(imageTrickContainer);
        $('.card.tricks:last').append(cardBodyTrick);
        $('h2.card-title:last').append('<a href="/trick/'+ trick.slug + '/'+ trick.uuid +'" class="btn btn-outline-primary text-nowrap">'+ trick.name.toUpperCase() + '</a>');

        containerCardTrick.slideDown(1000);

    };

    /** more tricks are loads by AJAX request when a user click on "load-more" button  */
    loadTricks.click(function(e) {
        e.preventDefault();
        let url = loadTricks.attr('href') + '/' + $('div.card.tricks').length;
        $.getJSON(url).done(function(data) {
            // if no more comments
            if (data.length === 0) {
                // then delete "load-more" button on trick page
                loadTricks.slideUp('slow', 'linear');
                $('#tricks').animate({'margin-bottom': '100px'}, 'slow', 'linear');                    
            } else {
                // else add comments on trick page
                $.each(data, function (index, trick) {
                    displayTrick(trick);
                });
            }
        });
    });
});
