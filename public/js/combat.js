let isProcessing = false;

const combatArena = document.querySelector('.combat-arena');
const HERO_NAME = document.getElementById('hero-name-data')?.textContent;
const HERO_MAX_PV = parseInt(document.getElementById('max-hp-data')?.textContent || '100');
const HERO_MAX_MANA = parseInt(document.getElementById('max-mana-data')?.textContent || '100');
const MONSTER_MAX_PV = parseInt(document.getElementById('monster-max-hp-data')?.textContent || '100');
const SPELL_COST = 20;

function performAction(action) {
    if (isProcessing) return;
    
    isProcessing = true;
    disableButtons();
    
    fetch('/combat/action', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=' + action
    })
    .then(response => {
        return response.json().catch(error => {
            console.error("Erreur de parsing JSON (possible output PHP non JSON):", error);
            throw new Error('Erreur critique de communication avec le serveur (JSON invalide).');
        });
    })
    .then(data => {
        if (data.error) {
            showError(data.error);
            isProcessing = false;
            enableButtons();
            return;
        }
        
        if (data.success && data.actions) {
            displayActions(data.actions, data.combat_ended, data.redirect);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showError(error.message || 'Une erreur inconnue est survenue.');
        isProcessing = false;
        enableButtons();
    });
}

function displayActions(actions, combatEnded, redirect) {
    const display = document.getElementById('action-display');
    let currentIndex = 0;
    
    function showNextAction() {
        if (currentIndex >= actions.length) {
            if (combatEnded) {
                setTimeout(() => {
                    window.location.href = redirect || '/combat/end';
                }, 1000);
            } else {
                display.innerHTML = '<p style="color: #ffd700;">Choisissez votre action</p>';
                isProcessing = false;
                enableButtons();
            }
            return;
        }
        
        const action = actions[currentIndex];
        const isHeroAttacker = action.attacker === HERO_NAME;
        const isDefense = action.attack_name === 'Défense';
        const isHeroTarget = action.target === HERO_NAME;

        let icon = '⚔️';
        let color = '#ff6b6b';
        let damageText = '';
        let damageColor = '';
        
        if (action.attack_name.includes('Sort')) {
            icon = '✨';
            color = '#a855f7';
        } else if (action.attack_name.includes('Monstre') || action.attacker !== HERO_NAME) {
            icon = '👹';
            color = '#ef4444';
        } else if (isDefense) {
            icon = '🛡️';
            color = '#4a7a66';
        }

        display.innerHTML = `
            <p style="color: ${color}; margin: 10px 0; animation: fadeIn 0.5s;">
                ${icon} <strong>${action.attacker}</strong> ${isDefense ? 'se prépare...' : `utilise <em>${action.attack_name}</em>`}
            </p>
        `;

        setTimeout(() => {
            if (isDefense) {
                 damageText = 'Défense activée';
                 damageColor = '#4a7a66';
                 updateDefenseStatus(true);

            } else if (action.damage > 0) {
                damageText = `-${action.damage} PV`;
                damageColor = isHeroAttacker ? '#ffc107' : '#ef4444';
                
                if (isHeroTarget) {
                    updateHeroHP(action.target_pv_left, HERO_MAX_PV);
                    if (!isHeroAttacker) {
                        updateDefenseStatus(false);
                    }
                } else {
                    updateMonsterHP(action.target_pv_left, MONSTER_MAX_PV);
                }
                
                if (action.attack_name.includes('Sort') && action.hero_mana_left !== undefined) {
                     updateHeroMana(action.hero_mana_left, HERO_MAX_MANA);
                }
                
            } else {
                 damageText = 'Bloqué !';
                 damageColor = '#60a5fa';
                 if (!isHeroAttacker) {
                     updateDefenseStatus(false);
                 }
            }

            display.innerHTML += `
                <p style="color: ${damageColor}; font-size: 2rem; animation: scaleUp 0.5s;">
                    ${damageText}
                </p>
            `;
            
            currentIndex++;
            setTimeout(showNextAction, 1000);
        }, 1000); 
    }
    
    showNextAction();
}

function showError(message) {
    const display = document.getElementById('action-display');
    display.innerHTML = `<p style="color: #ff4444; font-weight: bold;">${message}</p>`;
    setTimeout(() => {
        display.innerHTML = '<p style="color: #ffd700;">Choisissez votre action</p>';
    }, 2000);
}

function updateHeroHP(newHP, maxPV) {
    const percentage = (newHP / maxPV) * 100;
    const hpBar = document.getElementById('hero-hp-bar');
    const hpText = document.getElementById('hero-hp-text');
    
    hpBar.style.width = Math.max(0, percentage) + '%';
    hpText.textContent = newHP + ' / ' + maxPV;
    hpBar.style.transition = 'width 0.5s ease-in-out';
}

function updateHeroMana(newMana, maxMana) {
    const percentage = (newMana / maxMana) * 100;
    const manaBar = document.getElementById('hero-mana-bar');
    const manaText = document.getElementById('hero-mana-text');
    
    if (manaBar && manaText) {
        manaBar.style.width = Math.max(0, percentage) + '%';
        manaText.textContent = newMana + ' / ' + maxMana;
        
        const spellBtn = document.getElementById('spell-btn');
        if (spellBtn) {
            spellBtn.disabled = newMana < SPELL_COST;
            spellBtn.style.opacity = newMana < SPELL_COST ? '0.5' : '1';
        }
        
        manaBar.style.transition = 'width 0.5s ease-in-out';
    }
}

function updateMonsterHP(newHP, maxPV) {
    const percentage = (newHP / maxPV) * 100;
    const hpBar = document.getElementById('monster-hp-bar');
    const hpText = document.getElementById('monster-hp-text');
    
    hpBar.style.width = Math.max(0, percentage) + '%';
    hpText.textContent = newHP + ' / ' + maxPV;
    hpBar.style.transition = 'width 0.5s ease-in-out';
}

function updateDefenseStatus(isDefending) {
    const imgContainer = document.querySelector('.combatant.hero-side .combatant-image');
    let defendIndicator = imgContainer.querySelector('.defense-indicator');

    if (isDefending) {
        if (!defendIndicator) {
             defendIndicator = document.createElement('span');
             defendIndicator.className = 'defense-indicator';
             defendIndicator.style.cssText = 'position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); background: #4a7a66; color: white; padding: 5px 10px; border-radius: 5px; box-shadow: 0 0 10px rgba(74, 122, 102, 0.8);';
             defendIndicator.textContent = 'Défense!';
             imgContainer.appendChild(defendIndicator);
        }
    } else {
        defendIndicator?.remove();
    }
    
    const defendBtn = document.getElementById('defend-btn');
    if (defendBtn) {
        defendBtn.disabled = isDefending;
        defendBtn.style.opacity = isDefending ? '0.5' : '1';
    }
}


function disableButtons() {
    document.querySelectorAll('.action-buttons button').forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
    });
}

function enableButtons() {
    const spellBtn = document.getElementById('spell-btn');
    const isDefending = document.querySelector('.defense-indicator') !== null;
    
    document.querySelectorAll('.action-buttons button').forEach(btn => {
        btn.disabled = false;
        btn.style.opacity = '1';
    });
    
    if (spellBtn) {
        const manaText = document.getElementById('hero-mana-text');
        if (manaText) {
            const currentMana = parseInt(manaText.textContent.split('/')[0].trim());
            if (currentMana < SPELL_COST) {
                spellBtn.disabled = true;
                spellBtn.style.opacity = '0.5';
            }
        }
    }
    
    const defendBtn = document.getElementById('defend-btn');
    if (defendBtn) {
        if (isDefending) {
            defendBtn.disabled = true;
            defendBtn.style.opacity = '0.5';
        }
    }
}

function openInventoryModal() {
    const modal = document.getElementById('inventory-modal');
    modal.style.display = 'flex';
    loadInventory();
}

function closeInventoryModal() {
    const modal = document.getElementById('inventory-modal');
    modal.style.display = 'none';
}

function loadInventory() {
    const contentList = document.getElementById('inventory-content-list');
    contentList.innerHTML = '<p style="text-align: center; color: #aaa;">Chargement...</p>';

    fetch('/combat/inventory')
        .then(response => {
             if (!response.ok) {
                 throw new Error(`Erreur HTTP: ${response.status}`);
             }
             return response.json();
        })
        .then(data => {
            if (data.success && data.inventory.length > 0) {
                contentList.innerHTML = '';
                data.inventory.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.className = 'inventory-item';
                    itemElement.innerHTML = `
                        <img src="${item.image || '/public/img/Potions.jpg'}" alt="${item.name}" class="item-icon">
                        <div class="item-info">
                            <strong>${item.name}</strong>
                            <span>(${item.item_type}) x${item.quantity}</span>
                            <p class="item-description">${item.description}</p>
                        </div>
                        <button class="use-item-btn" data-item-id="${item.item_id}" ${item.item_type !== 'consumable' ? 'disabled' : ''}>Utiliser</button>
                    `;
                    contentList.appendChild(itemElement);
                });
            } else {
                contentList.innerHTML = '<p style="text-align: center; color: #ffc107;">Votre inventaire est vide.</p>';
            }
        })
        .catch(error => {
            console.error('Erreur de chargement inventaire:', error);
            contentList.innerHTML = '<p style="text-align: center; color: #ef4444;">Erreur de connexion aux données.</p>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const logContent = document.querySelector('.log-content');
    if (logContent) {
        logContent.scrollTop = logContent.scrollHeight;
    }
    
    const styleSheet = document.createElement('style');
    styleSheet.innerHTML = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { transform: translateY(0); }
        }
        @keyframes scaleUp {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
    `;
    document.head.appendChild(styleSheet);
});