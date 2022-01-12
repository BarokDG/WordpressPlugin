jQuery(".upload_image_button").click(function () {
  var send_attachment_bkp = wp.media.editor.send.attachment;
  var button = jQuery(this);
  wp.media.editor.send.attachment = function (props, attachment) {
    jQuery(button).parent().prev().attr("src", attachment.url);
    jQuery(button).prev().val(attachment.id);
    wp.media.editor.send.attachment = send_attachment_bkp;
  };
  wp.media.editor.open(button);
  return false;
});

jQuery(".remove_image_button").click(function (event) {
  event.preventDefault();
  var answer = confirm("Are you sure?");
  if (answer == true) {
    var src = jQuery(this).parent().prev().attr("data-src");
    jQuery(this).parent().prev().attr("src", src);
    jQuery(this).prev().prev().val("");
  }
  return false;
});
