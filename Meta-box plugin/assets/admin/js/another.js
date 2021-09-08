var frame;
(function ($) {
      // books input datepicker
    $(document).ready(function () {
        $(".omb_dp").datepicker();
    });

    var image_url = $("#omb_image_url").val();
    if(image_url){
        $("#image_container").html(`<img src="${image_url}">`);
    }

    $("#upload_image").on("click", function () {
        if (frame) {
        frame.open();
        return false;
        }

        frame = wp.media({
        title: "Upload An Image",
        button: {
            text: "Select this Image",
        },
        multiple: false,
        });
        frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();
        console.log( attachment.id );
        $("#omb_image_id").val(attachment.id);
        $("#omb_image_url").val(attachment.sizes.thumbnail.url);
        $("#image_container").html(`<img src="${attachment.sizes.thumbnail.url}">`);
        });

        frame.open();
        return false;
    });
})(jQuery);
