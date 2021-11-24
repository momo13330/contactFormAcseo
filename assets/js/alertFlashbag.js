(function () {
    // Récuperation de l'alerte
    let alertsMessage = document.getElementsByClassName('alert');
    Array.from(alertsMessage).forEach(function (alert) {
        // au click on retire l'alerte
        alert.addEventListener('click', ()=>{
            alert.remove();
        })
        // ou au bout de 2 seconde
        setTimeout(function () {
            alert.remove();
        }, 2000)
    });
})();