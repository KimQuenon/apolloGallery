// update chrono
function chrono() {
    const divDays = document.querySelector('#days');
    const divHours = document.querySelector('#hour');
    const divMin = document.querySelector('#min');
    const divSec = document.querySelector('#sec');

    // count endDate
    function compte() {
        var actualDatetime = new Date();
        var endDate = new Date("{{ artwork.endDate|date('Y-m-d H:i:s') }}");
        var totalSecondes = (endDate - actualDatetime) / 1000;

        // if timer ends
        if (totalSecondes < 0) {
            divCount.innerHTML = "end";
            return;
        }

        // values
        var days = Math.floor(totalSecondes / (60 * 60 * 24));
        var hours = Math.floor((totalSecondes - (days * 60 * 60 * 24)) / (60 * 60));
        var minutes = Math.floor((totalSecondes - ((days * 60 * 60 * 24 + hours * 60 * 60))) / 60);
        var secondes = Math.floor(totalSecondes - ((days * 60 * 60 * 24 + hours * 60 * 60 + minutes * 60)));

        // display
        divDays.innerHTML = days + " days";
        divHours.innerHTML = hours + ":";
        divMin.innerHTML = minutes + ":";
        divSec.innerHTML = secondes;

        var actualisation = setTimeout(compte, 1000);
    }
    compte();
}
chrono();