document.addEventListener("DOMContentLoaded", function () {
    var accordionButtons = document.querySelectorAll(".accordion-button");
    accordionButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            var icon = button.querySelector(".accordion-icon");
            if (icon.classList.contains("rotated")) {
                icon.classList.remove("rotated");
            } else {
                icon.classList.add("rotated");
            }
        });
    });
});