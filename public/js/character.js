document.addEventListener('DOMContentLoaded', function() {
    const classRadios = document.querySelectorAll('input[name="class_id"]');
    const characterPreview = document.getElementById('characterPreview');
    const selectedClassName = document.getElementById('selectedClassName');
    const basePv = document.getElementById('basePv');
    const baseMana = document.getElementById('baseMana');
    const baseStrength = document.getElementById('baseStrength');
    const baseInitiative = document.getElementById('baseInitiative');
    
    function updatePreview(radio) {
        const className = radio.dataset.name;
        const imagePath = radio.dataset.image;
        const pv = radio.dataset.pv;
        const mana = radio.dataset.mana;
        const strength = radio.dataset.strength;
        const initiative = radio.dataset.initiative;
        
        characterPreview.style.opacity = '0';
        
        setTimeout(() => {
            characterPreview.src = imagePath;
            selectedClassName.textContent = className;
            basePv.textContent = pv;
            baseMana.textContent = mana;
            baseStrength.textContent = strength;
            baseInitiative.textContent = initiative;
            characterPreview.style.opacity = '1';
        }, 200);
    }

    classRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            updatePreview(this);
        });
    });
    
    const form = document.getElementById('characterForm');
    
    form.addEventListener('submit', function(e) {
        const heroName = document.getElementById('heroName').value.trim();
        const selectedClass = document.querySelector('input[name="class_id"]:checked');
        
        if (heroName === '') {
            e.preventDefault();
            alert('Veuillez entrer un nom pour votre héros !');
            return false;
        }
        
        if (!selectedClass) {
            e.preventDefault();
            alert('Veuillez sélectionner une classe !');
            return false;
        }
        
        console.log('Création du héros :', heroName, 'Classe:', selectedClass.value);
    });
    
    const classItems = document.querySelectorAll('.class-item');
    
    classItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (!radio.checked) {
                const content = this.querySelector('.class-content');
                content.style.borderColor = 'var(--accent-red)';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (!radio.checked) {
                const content = this.querySelector('.class-content');
                content.style.borderColor = 'var(--border-gray)';
            }
        });
    });
    
});