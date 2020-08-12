/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {

//////////////////////////////////////////////////////////////////////////////////////////
//                            PICTURES
//////////////////////////////////////////////////////////////////////////////////////////

    ///////////////// display chosen file name in it's input tag /////////////////////////
    var displayFilename = function() {
        $('.custom-file-input').change(function() {
            var $label = $(this).next();
            $.each($(this.files), function(index, file) {
                $label.text(file.name);
            });
        });
    };
    displayFilename();

    ////////////////////////////// ADD A FORM PICTURE ///////////////////////////////////////
    let collectionHolder = $('div.pictures');
    let addPictureButton = $('#add_picture_link');
    let pictureForm = $('div.picture');
    // count the current form inputs we have, use that as the new
    // index when inserting a new item
    collectionHolder.data('index', collectionHolder.find('.picture').length);

    var addPictureForm = function(collectionHolder, pictureForm) {
        // Get the data-prototype explained earlier
        var prototype = collectionHolder.data('prototype');
        // get the new index
        var index = collectionHolder.data('index');

        var newForm = prototype;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        // var $newFormLi = $('<li></li>').append(newForm);
        // $newLinkLi.before($newFormLi);
        collectionHolder.append(newForm);

        // custom tags
        index = collectionHolder.data('index');
        $('#trick_pictures_' + (index - 1)).addClass('row picture mt-2');
        $('#trick_pictures_' + (index - 1) + ' div.form-group:first').wrap('<div class="col-12 col-md-10 col-lg-6"></div>');
        $('#trick_pictures_' + (index - 1) + ' div.form-group:last').addClass('mt-2 mb-4 col-12');
    };

    addPictureButton.on('click', function(e) {
        // add a new picture form
        addPictureForm(collectionHolder, pictureForm);
        displayFilename();
    });
});
