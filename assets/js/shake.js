function shake(square, tremblings, distance, speed) {
    let move = 0;
    let intervalId = setInterval(() => {
        move = move === 0 ? distance : 0;
        square.style.transform = `translateX(${move}px)`;

        tremblings--;

        if (tremblings <= 0) {
            clearInterval(intervalId);
            square.style.transform = `translateX(0)`;
        }
    }, speed);
}


document.addEventListener("DOMContentLoaded", function () {
    const squares = document.querySelectorAll(".square");

    squares.forEach((square) => {
        shake(square, 10, 2, 50);
    });

});