/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {

    //////////////////////////////////////////////////////////////////////////////////////////
    //                   DISPLAY CHOSEN FILE NAME IN IT'S INPUT TAG
    //////////////////////////////////////////////////////////////////////////////////////////

    var displayFilename = function () {
        $('.custom-file-input').change(function () {
            var $label = $(this).next();
            $.each($(this.files), function (index, file) {
                $label.text(file.name);
            });
        });
    };
    displayFilename();

    //////////////////////////////////////////////////////////////////////////////////////////
    //                            ADD A FORMS PICTURE
    //////////////////////////////////////////////////////////////////////////////////////////

    let collectionHolderPictures = $('div.pictures');
    let addPictureButton = $('#add_picture_link');
    // count the current form inputs we have, use that as the new
    // index when inserting a new item
    collectionHolderPictures.data('index', collectionHolderPictures.find('.picture').length);

    var addPictureForm = function(collectionHolderPictures) {
        // Get the data-prototype explained earlier
        var prototype = collectionHolderPictures.data('prototype');
        // get the new index
        var index = collectionHolderPictures.data('index');

        var newForm = prototype;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolderPictures.data('index', index + 1);

        // Display the form in the page 
        collectionHolderPictures.append(newForm);

        // custom tags classes
        index = collectionHolderPictures.data('index');
        $('#trick_pictures_' + (index - 1)).addClass('row picture mt-2 pt-2');
        $('#trick_pictures_' + (index - 1) + ' div.form-group:first').wrap('<div class="col-12 col-md-10 col-lg-6"></div>');
        $('#trick_pictures_' + (index - 1) + ' div.form-group:last').addClass('my-2 col-12');
    };

    addPictureButton.on('click', function (e) {
        // add a new picture form
        addPictureForm(collectionHolderPictures);
        displayFilename();
    });

    //////////////////////////////////////////////////////////////////////////////////////////
    //                            ADD A FORMS VIDEO
    //////////////////////////////////////////////////////////////////////////////////////////

    let collectionHolderVideos = $('div.videos');
    let addVideoButton = $('#add_video_link');
    // count the current form inputs we have, use that as the new
    // index when inserting a new item
    collectionHolderVideos.data('index', collectionHolderVideos.find('.video').length);

    var addVideoForm = function(collectionHolderVideos) {
        // Get the data-prototype explained earlier
        var prototype = collectionHolderVideos.data('prototype');
        // get the new index
        var index = collectionHolderVideos.data('index');

        var newForm = prototype;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolderVideos.data('index', index + 1);

        // Display the form in the page
        collectionHolderVideos.append(newForm);

        // custom tags classes
        index = collectionHolderVideos.data('index');
        $('#trick_videos_' + (index - 1)).addClass('form-row video pb-2 pt-1 mt-2');
        $('#trick_videos_' + (index - 1) + ' fieldset').addClass('pl-3 col-sm-6 col-md-5 col-lg-4 col-xl-3 mb-0');
        $('#trick_videos_' + (index - 1) + ' div.form-group').addClass('col-sm-6 col-md-7 col-lg-8 mt-3 col-xl-9 mt-sm-0 mb-0');
        $('#trick_videos_' + (index - 1) + ' div.form-group label').after('<span class="modal-info-code ml-2 mb-1" data-toggle="modal" data-target="#modal-help-code-video"><i class="fas fa-question-circle"></i></span>')
    };

    addVideoButton.on('click', function (e) {
        // add a new video form
        addVideoForm(collectionHolderVideos);
    });
});
