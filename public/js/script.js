/* Profil Section */

function ouvrirInfo(){
    document.getElementById('info').classList.add('active');
    document.body.style.overflow='hidden';
}

function fermerInfo(){
    document.getElementById('info').classList.remove('active');
    document.body.style.overflow = 'auto';
}