var scntDiv = jQuery("#p_scents");
var i = jQuery("#p_scents div").size() + 1;
var store = jQuery("[name='llp_added_links']");

jQuery("#addScnt").click(function (event) {
  event.preventDefault();

  jQuery(
    `<div>
      <label for="p_scnts"></label>
      <input type="text" id="p_scnt" size="20" name="p_scnt_${i}" value="" placeholder="Input Value" />
      <button class="remScnt">Remove</button>
    </div>`
  )
    .appendTo(scntDiv)
    .on("click", ".remScnt", function (event) {
      event.preventDefault();

      if (i > 2) {
        jQuery(this).parent("div").remove();
        i--;
      }

      return false;
    });

  i++;

  return false;
});

jQuery("#save").click(function (event) {
  event.preventDefault();

  store.val(3);
});
