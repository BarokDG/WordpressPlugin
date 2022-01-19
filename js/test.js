jQuery(".test-btn").on("click", function (e) {
  e.preventDefault();
  jQuery.ajax({
    method: "POST",
    url: ipAjaxVar.ajaxurl,
    data: {
      action: "test_function",
    },
    success: () => alert("success"),
    error: (error) => console.log(error),
  });
});
