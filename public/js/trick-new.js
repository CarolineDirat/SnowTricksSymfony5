/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {

    //////////////////////////////////////////////////////////////////////////////////////////
    //                            ADD A FORMS PICTURE
    //////////////////////////////////////////////////////////////////////////////////////////

    let collectionHolderPictures = $('div.pictures');
    let addPictureButton = $('#add_picture_link');
    // count the current form inputs we have, use that as the new
    // index when inserting a new item
    collectionHolderPictures.data('index', collectionHolderPictures.find('.picture').length);

    let addPictureForm = function(collectionHolderPictures) {
        // Get the data-prototype explained earlier
        let prototype = collectionHolderPictures.data('prototype');
        // get the new index
        let index = collectionHolderPictures.data('index');

        let newForm = prototype;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolderPictures.data('index', index + 1);

        // Display the form in the page 
        collectionHolderPictures.append(newForm);

        // custom tags classes
        $('#trick_pictures_' + index).addClass('row picture mt-2 pt-2');
        $('#trick_pictures_' + index + ' div.form-group:first').wrap('<div class="col-12 col-md-10 col-lg-6"></div>');
        $('#trick_pictures_' + index + ' div.form-group:last').addClass('my-2 col-12');

        $('#trick_pictures_' + index).hide().slideDown('slow');
    };

    addPictureButton.on('click', function (e) {
        // add a new picture form
        addPictureForm(collectionHolderPictures);
        translateBrowse();
        displayFilename();
    });

    //////////////////////////////////////////////////////////////////////////////////////////
    //                            ADD A FORM VIDEO
    //////////////////////////////////////////////////////////////////////////////////////////

    let collectionHolderVideos = $('div.videos');
    let addVideoButton = $('#add_video_link');
    // count the current form inputs we have, use that as the new
    // index when inserting a new item
    collectionHolderVideos.data('index', collectionHolderVideos.find('.video').length);

    let addVideoForm = function(collectionHolderVideos) {
        // Get the data-prototype explained earlier
        let prototype = collectionHolderVideos.data('prototype');
        // get the new index
        let index = collectionHolderVideos.data('index');

        let newForm = prototype;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolderVideos.data('index', index + 1);

        // Display the form in the page
        collectionHolderVideos.append(newForm);

        // custom videos classes
        $('#trick_videos_' + index).addClass('form-row video pb-2 pt-1 mt-2');
        $('#trick_videos_' + index + ' fieldset').addClass('pl-3 col-sm-6 col-md-5 col-lg-4 col-xl-3 mb-0');
        $('#trick_videos_' + index + ' div.form-group').addClass('col-sm-6 col-md-7 col-lg-8 mt-3 col-xl-9 mt-sm-0 mb-0');
        $('#trick_videos_' + index + ' div.form-group label').after('<span class="modal-info-code ml-2 mb-1" data-toggle="modal" data-target="#modal-help-code-video"><i class="fas fa-question-circle"></i></span>');

        $('#trick_videos_' + index).hide().slideDown('slow');
    };

    addVideoButton.on('click', function (e) {
        // add a new video form
        addVideoForm(collectionHolderVideos);
    });
});
