const inputs = document.querySelectorAll(".llp-inner-input-container input");

inputs.forEach((input) => {
  if (input.value !== "") {
    input.parentElement.classList.add("filled");
  }

  input.addEventListener("change", () => {
    if (input.value !== "") {
      input.parentElement.classList.add("filled");
    } else {
      input.parentElement.classList.remove("filled");
    }
  });
});
