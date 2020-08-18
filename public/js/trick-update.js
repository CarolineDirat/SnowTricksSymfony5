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
            method: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                '_token': $(this).data('token'),
            })
        }).done(function(data) {
            displayNewFirstPicture(data);
        }).fail(function(data){
            console.log(data);
            alert(data.responseJSON.message);
        });
    });

    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  UPDATE VIDEO FOR SCREEN > 720px
    //////////////////////////////////////////////////////////////////////////////////////////

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
            console.log(data);
            updateVideo(data);
        }).fail(function(data){
            alert(data.responseJSON.message);
        });
    });
});
