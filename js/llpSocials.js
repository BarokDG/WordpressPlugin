jQuery("#submit").click(function (event) {
  jQuery(".filter-url").each(function () {
    if (this.value) {
      this.value =
        this.id !== "codepen"
          ? `https://www.${this.id}.com/${this.value}`
          : `https://www.${this.id}.io/${this.value}`;
    } else {
      this.dataset.check = "";
    }
  });
});
