/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {

    //////////////////////////////////////////////////////////////////////////////////////////
    //                   FILE BROWSE : customizing the strings with HTML
    //////////////////////////////////////////////////////////////////////////////////////////
    
    let translateBrowse = function() {
        $('label.custom-file-label').attr('data-browse', 'Parcourir mes fichiers');
    };
    translateBrowse();

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

});
