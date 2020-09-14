/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {
    // **************************************************************************************************************
    //
    //                                             DELETE A TRICK FROM TRASH ICON             
    //
    // **************************************************************************************************************
    
    const deleteTrick = function(e, deleteLink) {
        e.preventDefault();
        $('#modalDelete div.modal-body').html('Êtes-vous sûr.e de vouloir supprimer le trick <span class="font-weight-bold">' + deleteLink.data('trick') + '</span> ?');
        $('#confirmDeleteTrickModal').one('click', function(e){
            $('#modalDelete').modal('hide');    
            $.ajax({
                url: deleteLink.attr('href'), 
                method: 'DELETE',
                dataType: 'json',
                data: JSON.stringify({'_token': $('#tricks').data('token')}),
            }).done(function(data) {
                $('a[href="'+ $(this)[0]['url'] +'"]').parent().parent().parent().parent().fadeOut('1000');
                $('#modalResponseFromDelete  p.modal-title').text(data.message);
                $('#modalResponseFromDelete').modal('show');
            }).fail(function() {
                $('#modalResponseFromDelete  p.modal-title').text("Oups ! La suppression n'a pas pu se faire.");
                $('#modalResponseFromDelete').modal('show');
            });
        });
    };

    const deleteLinks = $('[data-delete]');
    deleteLinks.click(function(e){
        deleteTrick(e, $(this));
    });

    $('#cancelDeleteTrickModal').click(function(e) {
        $('#confirmDeleteTrickModal').off('click');
    });    

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
        $('.add-trick-btn').removeClass('d-none');
        $('html,body').animate({scrollTop: $('#tricks').offset().top}, 'slow');
    });

    $('#tricks-up a').click(function(e) {
        e.preventDefault;
        $('html,body').animate({scrollTop: $('#tricks').offset().top}, 'fast');
    });

    // **************************************************************************************************************
    //
    //                                             LOAD MORE TRICKS BUTTON             
    //
    // **************************************************************************************************************

    const loadTricks = $('#load-more-tricks');

    const displayTrick = function(trick) {

        let firstPicture = trick.firstPicture ? trick.firstPicture.filename : (trick.pictures.length ? trick.pictures[0].filename : 'default.jpg');

        let containerCardTrick = $(document.createElement('div'));
        containerCardTrick.addClass('col-sm-6 col-md-4 col-lg-3 my-2');
        containerCardTrick.append('<div class="card tricks"></div>').hide();

        let imageTrickContainer = $(document.createElement('div'));
        imageTrickContainer.addClass('div-card-img d-flex align-items-center justify-content-center');
        imageTrickContainer.append('<img src="/../uploads/images/200/'+ firstPicture +'" class="card-img-top" alt="Picture to illustrate '+ trick.name +' snowboard trick.">');

        let cardBodyTrick = $(document.createElement('div'));
        cardBodyTrick.addClass('card-body p-0 text-center');
        cardBodyTrick.append('<h2 class="card-title d-flex flex-nowrap justify-content-center"></h2>');
        
        $('#tricks').append(containerCardTrick);
        $('.card.tricks:last').append(imageTrickContainer);
        $('.card.tricks:last').append(cardBodyTrick);
        $('h2.card-title:last').append('<a href="/trick/'+ trick.slug + '/'+ trick.uuid +'" class="btn btn-outline-primary btn-sm text-nowrap" data-toggle="tooltip" data-placement="bottom" title="Page de présentation du trick">'+ trick.name.toUpperCase() + '</a>');

        if ($('a.add-trick-btn').length) {
            $('h2.card-title:last').append('<a href="/modifier/trick/' + trick.slug + '/' + trick.uuid + '" class="btn btn-outline-primary btn-sm mr-1 ml-2" data-toggle="tooltip" data-placement="bottom" title="Modifier le trick"><i class="fas fa-pencil-alt"></i></a>');
            $('h2.card-title:last').append('<a href="/trick-suppression/' + trick.uuid  + '" class="btn btn-outline-primary btn-sm" data-delete data-trick="'+ trick.name +'" data-toggle="modal" data-target="#modalDelete" data-toggle="tooltip" data-placement="bottom" title="Supprimer le trick"><i class="fas fa-trash-alt"></i></a>');
        }

        containerCardTrick.slideDown(1000);
    };

    // more tricks are loads by AJAX request when a user click on "load-more" button
    loadTricks.click(function(e) {
        e.preventDefault();
        let url = loadTricks.attr('href') + '/' + $('div.card.tricks').length;
        $.getJSON(url).done(function(data) {
            // if no more comments
            if (data.length === 0) {
                // then delete "load-more" button on trick page
                loadTricks.hide('slow', 'linear');
            } else {
                // else add tricks on trick page
                $.each(data, function (index, trick) {
                    displayTrick(trick);
                    $('html,body').animate({scrollTop: $('#load-more-tricks').offset().top}, 'slow');
                    $('[data-delete]:last').click(function(e) {
                        deleteTrick(e, $(this));
                    });
                });
            }
        });
    });
});
