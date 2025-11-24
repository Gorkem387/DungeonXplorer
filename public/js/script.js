/* Profil Section */

const btnVoirDetails = document.getElementById('btnVoirDetails');
const btnFermerInfo = document.getElementById('btnFermerInfo');
const info = document.getElementById('info');

btnVoirDetails.addEventListener('click', function() {
    info.classList.add('active');
    document.body.style.overflow='hidden';
});

btnFermerInfo.addEventListener('click', function(event) {
    info.classList.remove('active');
    document.body.style.overflow='auto';
});