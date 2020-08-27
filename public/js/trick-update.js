/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {

    // disables the enter key which must not submit the form
    $("form").bind("keypress", function (e) {
        if (e.keyCode == 13) {
            return false;
        }
    });

    // delete unless fields from form_end when the trick doesn't have pictures or video
    $('fieldset.form-group').has('div#trick_pictures').remove();
    $('fieldset.form-group').has('div#trick_videos').remove();

    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  UPDATE NAME
    //////////////////////////////////////////////////////////////////////////////////////////

    // input field must keep the trick name
    $('#updateNameModal').on('hidden.bs.modal', function (e) {
        $('#updateNameModal input').val($('#trick-update h2 span').text());
    });
    
    let updateTrickName = $("#update-trick-name");

    let displayNewName = function(data) {
        $('#updateNameModal input').val(data.newName);
        $('#trick-update h2 span').text((data.newName).toUpperCase());
    };

    updateTrickName.click(function(e) {
        e.preventDefault();
        $('#updateNameModal').modal('hide');    
        $.ajax({
            url: $(this).attr('href'), 
            method: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                '_token': $(this).data('token'),
                'newName' : $('#updateNameModal input').val(),
            }),
            statusCode: {
                500: function() {
                    alert('Attention ! Ce nom de trick n\'est pas valide. Peut-être est-il déjà utilisé ?');
                },
                403: function(data) {
                    alert(data.message);
                }
              }
        }).done(function(data) {
            console.log(data);
            displayNewName(data);
            $('#updateNameModal input').val(data.newName);
        });
    });
    
    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  UPDATE FIRST IMAGE
    //////////////////////////////////////////////////////////////////////////////////////////
    
    let updateFirstImage = $('#update-first-image');

    $('.input-radio-first-picture').click(function() {
        let $this = $(this);
        updateFirstImage.data('value', $this.attr('value'));
    });

    let displayNewFirstPicture = function(data){
        $('#trick-update picture source:first-child').attr('srcset', '/../uploads/images/540/' + data.filename);
        $('#trick-update picture source:nth-child(2)').attr('srcset', '/../uploads/images/720/' + data.filename);
        $('#trick-update picture img').attr('src', '/../uploads/images/960/' + data.filename);
        $('#trick-update picture img').attr('alt', data.alt + 'photo pour illustrer le trick ' + data.trickName);
    };

    updateFirstImage.click(function(e) {
        e.preventDefault();
        $('#updateFirstImageModal').modal('hide');    
        $.ajax({
            url: $(this).attr('href'), 
            method: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                '_token': $(this).data('token'),
                'pictureId' : $(this).data('value'),
            }),
        }).done(function(data) {
            displayNewFirstPicture(data);
        }).fail(function(data){
            alert(data.message);
        });
    });

    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  DELETE FIRST IMAGE
    //////////////////////////////////////////////////////////////////////////////////////////

    let deleteFirstImage = $('#delete-first-image');

    deleteFirstImage.click(function(e) {
        e.preventDefault();    
        $.ajax({
            url: $(this).attr('href'), 
            method: 'DELETE',
            dataType: 'json',
            data: JSON.stringify({
                '_token': $(this).data('token'),
            })
        }).done(function(data) {
            displayNewFirstPicture(data);
        }).fail(function(data){
            alert(data.responseJSON.message);
        });
    });

    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  UPDATE VIDEO
    //////////////////////////////////////////////////////////////////////////////////////////
    
    $('#modal-help-code-video div.modal-footer button').click(function(e){
        $(this).removeData('dismiss');
        e.preventDefault();
    });

    let updateVideoLinks = $('.update-video-link');

    // data for AJAX request are on data attributes of the link witch send AJAX request
    // when input values change, these data attribute must change too
    $('.update-service-video input').change(function(){
        let $this = $(this);
        $this
            .closest('.modal-body')
            .next('.modal-footer')
            .children('a.update-video-link')
            .data('service', $this.closest('.update-service-video').find('input[type=radio]:checked').val())
        ;
    });
    $('input.update-code-video').change(function(){
        let $this = $(this);
        $this
            .closest('.modal-body')
            .next('.modal-footer')
            .children('a.update-video-link')
            .data('code', $this.val())
        ;
    });

    // function to update display of the video with new data
    let updateVideo = function(data) {
        let address = 'www.youtube.com/embed/';
        if ('vimeo' === data.service.toLowerCase()) {
            address = 'player.vimeo.com/video/';
        }
        if ('dailymotion' === data.service.toLowerCase()) {
            address = 'www.dailymotion.com/embed/video/';
        }
        $('#video-display-' + data.videoId + ' iframe').attr('src', 'https://' + address + data.code);
        $('#video-display-mobile-' + data.videoId + ' iframe').attr('src', 'https://' + address + data.code);
    };

    // AJAX request
    updateVideoLinks.click(function(e) {
        e.preventDefault();
        $('#updateVideoModal-' + $(this).data('videoid')).modal('hide');
        $.ajax({
            url: $(this).attr('href'), 
            method: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                '_token': $(this).data('token'),
                'videoId': $(this).data('videoid'),
                'service': $(this).data('service'),
                'code': $(this).data('code'),
            }),
            statusCode: {
                409: function(error) {
                    console.log(error);
                    alert(error.responseJSON.message);
                },
                403: function(error) {
                    console.log(error);
                    alert(error.responseJSON.message);
                },
                500: function(error) {
                    console.log(error);
                    alert(error.responseText);
                },
            }
        }).done(function(data){
            console.log(data);
            updateVideo(data);
        });
    });

    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  DELETE VIDEO 
    //////////////////////////////////////////////////////////////////////////////////////////

    let deleteVideoLinks = $('.delete-video-link');

    let deleteVideo = function(data) {
        // screen > 720px
        $('#video-display-' + data.videoId).remove();
        // screen < 720px
        $('#video-display-mobile-' + data.videoId).remove();
        // delete video form
        $('#updateVideoModal-' + data.videoId).remove();
    };

    deleteVideoLinks.click(function(e) {
        e.preventDefault();
        $('#deleteVideoModal-' + $(this).data('videoid')).modal('hide');   
        $.ajax({
            url: $(this).attr('href'), 
            method: 'DELETE',
            dataType: 'json',
            data: JSON.stringify({
                '_token': $(this).data('token'),
                'videoId': $(this).data('videoid')
            })
        }).done(function(data) {
            console.log(data);
            deleteVideo(data);
        }).fail(function(data){
            console.log(data);
            alert(data.responseJSON.message);
        });
    });

    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  ADD VIDEO FORM IN THE RIGHT PLACE ;)
    //////////////////////////////////////////////////////////////////////////////////////////

    let collectionHolderVideosUpdatePage = $('.update-trick-videos');
    let addVideoButtonUpdatePage = $('.add_video_link button');

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolderVideosUpdatePage.data('index', $('.update-video-modal').length);

    // function to add a new video form
    let addVideoFormUpdatePage = function(collectionHolder, screen) {
        // Get the data-prototype
        let prototype = collectionHolder.data('prototype');
        // get the new index
        let index = collectionHolder.data('index');

        let newForm = prototype;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolder.data('index', index + 1);

        // Display the form in the page
        $('.add_video_link.'+ screen + '-screen').before(newForm);

        // custom videos classes
        index = collectionHolder.data('index');
        $('#trick_videos_' + (index - 1)).addClass('form-row video pb-2 pt-1 my-2 text-left');
        $('#trick_videos_' + (index - 1) + ' fieldset').addClass('pl-3 col-12 col-sm-12 col-lg-6 mb-0');
        $('#trick_videos_' + (index - 1) + ' div.form-group').addClass('col-12 col-sm-12 col-lg-6 mt-3 mb-0');
        $('#trick_videos_' + (index - 1) + ' div.form-group label').after('<span class="modal-info-code ml-2 mb-1" data-toggle="modal" data-target="#modal-help-code-video"><i class="fas fa-question-circle"></i></span>');

        $('#trick_videos_' + (index - 1)).hide().slideDown('slow');
    };
    
    addVideoButtonUpdatePage.on('click', function(e) {
        // add a new video form
        addVideoFormUpdatePage(collectionHolderVideosUpdatePage, $(this).data('screen'));
    });

    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  UPDATE PICTURE
    //////////////////////////////////////////////////////////////////////////////////////////

    // FILE BROWSE : customizing the strings with HTML
    var translateBrowse = function() {
        $('label.custom-file-label').attr('data-browse', 'Parcourir mes fichiers');
    };
    translateBrowse();

    // functions to display new picture
    let updatePicture = function(data, id) {
        $(id)
            .attr('src', '/../uploads/images/200/' + data.filename)
            .attr('alt', data.alt + ' photo pour illustrer le trick de snowboard ' + data.trick)
        ;
    };
    let displayPictures = function(data) {
        updatePicture(data, '#picture-display-' + data.pictureId);
        updatePicture(data, '#picture-display-mobile-' + data.pictureId);
        updatePicture(data, '#updatePictureModal-' + data.pictureId + ' img');
        updatePicture(data, '#deletePictureModal-' + data.pictureId + ' img');
    };
    
    // AJAX REQUEST to update a picture
    let updatePictureLinks = $('.update-picture-link');

    updatePictureLinks.click(function(e) {
        e.preventDefault();
        let pictureId =  $(this).data('pictureid');
        $('#updatePictureModal-' + pictureId).modal('hide');
        let formData = new FormData($('#update-picture-form-' + pictureId)[0]);
        formData.append('nameForm', $('#update-picture-form-' + pictureId).attr('name'));       

        $.ajax({
            url: $(this).attr('href'), 
            method: 'POST',
            enctype: 'multipart/form-data',
            data: formData,
            dataType: 'json',
            processData: false,
		    contentType: false,
            cache: false,
            statusCode: {
                409: function(error) {
                    console.log(error);
                    alert(error.responseJSON.message);
                },
                403: function(error) {
                    console.log(error);
                    alert(error.responseJSON.message);
                },
                500: function(error) {
                    console.log(error);
                    alert(error.responseJSON.title);
                }
            }
        }).done(function(data){
            console.log(data);
            displayPictures(data);
        });
    });

    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  DELETE PICTURE 
    //////////////////////////////////////////////////////////////////////////////////////////

    let deletePictureLinks = $('.delete-picture-link');

    let deletePicture = function(data) {
        // screen > 720px
        $('#picture-display-' + data.pictureId).closest('div').remove();
        // screen < 720px
        $('#picture-display-mobile-' + data.pictureId).closest('div').remove();
        // in first image form
        $('#first-image-radio-' + data.pictureId).remove();
    };

    deletePictureLinks.click(function(e) {
        e.preventDefault();
        $('#deletePictureModal-' + $(this).data('pictureid')).modal('hide');   
        $.ajax({
            url: $(this).attr('href'), 
            method: 'DELETE',
            dataType: 'json',
            data: JSON.stringify({
                '_token': $(this).data('token'),
                'pictureId': $(this).data('pictureid')
            })
        }).done(function(data) {
            console.log(data);
            deletePicture(data);
        }).fail(function(data){
            console.log(data);
            alert(data.responseJSON.message);
        });
    });

     //////////////////////////////////////////////////////////////////////////////////////////
    //                   DISPLAY CHOSEN FILE NAME IN IT'S INPUT TAG
    //////////////////////////////////////////////////////////////////////////////////////////

    let displayFilename = function () {
        $('.custom-file-input').change(function () {
            let $label = $(this).next();
            $.each($(this.files), function (index, file) {
                $label.text(file.name);
            });
        });
    };
    displayFilename();

    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  ADD PICTURE FORM IN THE RIGHT PLACE ;)
    //////////////////////////////////////////////////////////////////////////////////////////

    let collectionHolderPicturesUpdatePage = $('.update-trick-pictures');
    let addPictureButtonUpdatePage = $('.add-picture-link button');

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolderPicturesUpdatePage.data('index', $('.update-picture-modal').length);

    // function to add a new video form
    let addPictureFormUpdatePage = function(collectionHolder, screen) {
        // Get the data-prototype
        let prototype = collectionHolder.data('prototype');
        // get the new index
        let index = collectionHolder.data('index');

        let newForm = prototype;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolder.data('index', index + 1);

        // Display the form in the page
        $('.add-picture-link.'+ screen + '-screen').before(newForm);

        // custom picture classes
        $('#trick_pictures_' + index).addClass('row picture my-2 pt-2');
        $('#trick_pictures_' + index + ' div.form-group:first').wrap('<div class="col-12"></div>');
        $('#trick_pictures_' + index + ' div.form-group:last').addClass('my-2 col-12');

        $('#trick_pictures_' + index).hide().slideDown('slow');
    };
    
    addPictureButtonUpdatePage.on('click', function(e) {
        // add a new picture form
        addPictureFormUpdatePage(collectionHolderPicturesUpdatePage, $(this).data('screen'));
        translateBrowse();
        displayFilename();
    });
});
