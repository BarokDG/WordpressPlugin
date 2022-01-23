var scntDiv = jQuery("#llp-links-list");
var i = jQuery("#llp-links-list div").size() + 1;
var store = jQuery("[name='llp_added_links']");

jQuery("#addLink").click(function (event) {
  event.preventDefault();

  jQuery(
    `<div id="link${i}">
      <label for="link${i}">Link ${i}</label>
      <input type="text" id="link_${i}_title" size="20" name="link_${i}_title" value="" placeholder="Link title" />
      <input type="url" id="link_${i}_url" size="20" name="link_${i}_url" value="" placeholder="https://" />
      <button class="remLink">Remove</button>
    </div>`
  )
    .appendTo(scntDiv)
    .on("click", ".remLink", function (event) {
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
  var links = jQuery("#llp-links-list div input");

  var keys = "";
  var values = "";

  links.each(function (index) {
    if (index % 2) {
      values += `${links[index].value},`;
    } else {
      keys += `${links[index].value},`;
    }
  });

  console.log(links);

  var result = `${keys.slice(0, -1)}=>${values.slice(0, -1)}`;

  alert(result);

  store.val(result);
});
