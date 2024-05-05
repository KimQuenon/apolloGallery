function animateRight() {
    const right = document.querySelector(".right");
    if (right) {
        right.classList.add("showX");
    }
}
function animateLeft() {
    const left = document.querySelector(".left");
    if (left) {
        left.classList.add("showX");
    }
}

function animateTop() {
    const top = document.querySelector(".top");
    if (top) {
        top.classList.add("showY");
    }
}

window.addEventListener("load", function () {
    animateRight();
    animateLeft();
    animateTop();
});