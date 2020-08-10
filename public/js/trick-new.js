/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {

/////////////////////////////////////////////////////////////////////////////////////////
//                            PICTURES
////////////////////////////////////////////////////////////////////////////////////////

    $('#trick_pictures_0_file').change(function() {
        $('#display-upload').html('');
        $.each($(this.files), function(index, file) {
            let filename = $(document.createElement('span'));
            filename.addClass('pr-3 text-nowrap');
            filename.text(file.name);
            $('#display-upload').append(filename);
        });
        $(".custom-file-label").text('Modifier la sélection de fichier');
    });


});
