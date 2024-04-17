document.addEventListener("DOMContentLoaded", function() {
    var bannerImages = document.querySelectorAll('.banner_img');

    // add a fade in animation
    function fadeInImage(element, duration) {
        var opacity = 0;
        var interval = 15;
        var increment = interval / duration;

        element.style.opacity = 0;
        element.style.display = 'block';

        function increaseOpacity() {
            opacity += increment;
            element.style.opacity = opacity;

            if (opacity >= 1) {
                clearInterval(fadeInterval);
            }
        }

        var fadeInterval = setInterval(increaseOpacity, interval);
    }

    // display images one by one
    function showImagesSequentially(index) {
        if (index < bannerImages.length) {
            fadeInImage(bannerImages[index], 500); // fade in duration
            setTimeout(function() {
                showImagesSequentially(index + 1);
            }, 500); // time between each image
        }
    }

    // hide all images at first
    bannerImages.forEach(function(image) {
        image.style.display = 'none';
    });

    // Commencez à afficher les images séquentiellement
    showImagesSequentially(0);
});