/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {
    
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
        //$('#modal-help-code-video').modal('hide');
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
            })
        }).done(function(data){
            updateVideo(data);
        }).fail(function(data){
            alert(data.responseJSON.message);
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

    let collectionHolderVideosUpdatePage = $('.trick-update-videos');
    let addVideoButtonUpdatePage = $('.add_video_link button');

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolderVideosUpdatePage.data('index', collectionHolderVideosUpdatePage.find('input').length);

    // function to add a new video form
    var addVideoFormUpdatePage = function(collectionHolder, screen) {
        // Get the data-prototype
        var prototype = collectionHolder.data('prototype');
        // get the new index
        var index = collectionHolder.data('index');

        var newForm = prototype;

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
});
