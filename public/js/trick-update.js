/*eslint quotes: ["error", "single", { "avoidEscape": true }]*/

$(function () {
    
    //////////////////////////////////////////////////////////////////////////////////////////
    //                                  UPDATE FIRST IMAGE
    //////////////////////////////////////////////////////////////////////////////////////////
    
    let updateFirstImage = $('#update-first-image');

    $('#trick-update input[type="radio"]').click(function(e) {
        updateFirstImage.data('value', $(this).attr('value'));
    });

    let displayNewFirstPicture = function(data){
        $('#trick-update picture:first-child').attr('srcset', '/../uploads/images/540/' + data.filename);
        $('#trick-update picture:nth-child(2)').attr('srcset', '/../uploads/images/720/' + data.filename);
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

});
