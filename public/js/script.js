$(function () {
    $("#load-more").on("click", function () {
        $("div.d-none").slice(0, 5).each(function () {
            $(this).removeClass("d-none");
        });
        if ($("div.d-none").length === 0) {
            $("#load-more").css("display", "none");
        }
    });
});
