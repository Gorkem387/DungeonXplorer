/* Profil Section */

const btnVoirDetails = document.getElementById('btnVoirDetails');
const btnFermerInfo = document.getElementById('btnFermerInfo');
const info = document.getElementById('info');
const infoContent = document.getElementById('cadre-info');

btnVoirDetails.addEventListener('click', function() {
    info.classList.add('active');
    document.body.style.overflow='hidden';
});

function openInfo(characterId){
    fetch(`/getCharacterDetails?id=${characterId}`)
        .then(response => response.json())
        .then(data => {
            const infoContent = document.querySelector('.cadre-info');

            infoContent.innerHTML = `
                <div class="cadre-info-item">
                        <span class="cadre-info-label">Nom :</span> ${data.name}
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Classe :</span> ${data.class_name}
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Progression :</span> ${data.chapter}
                        Chapitre ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Nombre de PV :</span> ${data.pv}
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Initiative :</span> ${data.initiative}
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Force :</span> ${data.strength}
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Mana :</span> ${data.mana}
                        ...
                    </div>
                    <div class="cadre-info-item">
                        <span class="cadre-info-label">Equipements :</span> ${data.equipement}
                        ...
                </div>
            `;
            info.classList.add('active');
            document.body.style.overflow = 'hidden';
        })
}

btnFermerInfo.addEventListener('click', function(event) {
    info.classList.remove('active');
    document.body.style.overflow='auto';
});

