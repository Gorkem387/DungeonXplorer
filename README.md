# 🏰 DungeonXplorer

**DungeonXplorer** est une application Web de récits interactifs de type "Livre dont vous êtes le héros".

## 📖 Présentation du projet
Le projet consiste à offrir une expérience de *dark fantasy* immersive où le joueur crée un personnage et progresse dans une aventure dont il influence le scénario.

## 🛠️ Socle Technique & Objectifs
Conformément aux modalités de réalisation, l'application repose sur :
**Langages :** PHP , MySQL, JavaScript, HTML5 et CSS3.
**Architecture :** Design Pattern **MVC** (Modèle-Vue-Contrôleur) avec un mini-framework personnalisé.
**Sécurité :** Utilisation de **PDO** pour prévenir les injections SQL et gestion des secrets via fichier **.env**..
**Versionnage :** Git avec un dépôt distant sur GitHub.

## 🚀 Fonctionnalités (V1)
### Pour le Joueur
**Gestion de compte :** Création, connexion et suppression sécurisée du compte.
**Système de RPG :** Création de personnage parmi trois classes (Guerrier, Voleur, Magicien)
**Progression :** Sauvegarde des caractéristiques, de l'expérience (XP) et de l'inventaire en base de données.
**Combats :** Système de gestion des affrontements contre des monstres.

### Pour l'Administrateur
**Modération :** Possibilité de supprimer les comptes des joueurs.
**Gestion de contenu (CRUD) :** Ajout, modification et suppression des chapitres.

## 🚀 Fonctionnalités (V2)

### 📊 Statistiques Joueurs
**Suivi de progression :** Accès à des données détaillées sur l’évolution des personnages.  
**Analyse des performances :** Statistiques sur les héros.

### ⚔️ Combat visuel
**Interface graphique :** Système de combat plus immersif avec animations et effets visuels.  
**Lisibilité accrue :** Affichage clair des actions, dégâts et états des personnages.

### 🔐 Conformité RGPD
**Protection des données :** Mise en place de mesures garantissant la sécurité et la confidentialité des informations personnelles.  
**Gestion des comptes :** Prise en compte des conséquences liées à la suppression de compte (anonymisation, suppression des données).

## 🎨 Charte Graphique
L'identité visuelle respecte les codes du genre médiéval fantastique :
**Couleurs :** Fond sombre (#1A1A1A), texte foncé (#c41e1e) pour les éléments interactifs.
**Typographie :** *Pirata One* pour une ambiance gothique et *Roboto* pour la lisibilité du contenu.
**Iconographie :** Utilisation du kit Font Awesome pour renforcer l'immersion.

## 📁 Structure du Projet
```text
DungeonXplorer/
├── core/           # Moteur (Router, Loader .env)
├── controllers/    # Logique métier (Auth, Chapter, Combat, Hero)
├── models/         # Interactions BDD (Entités et DAOs)
├── views/          # Fichiers de rendu (Templates HTML/PHP)
├── public/         # Assets (CSS, JS, Images, Fonts)
└── .env            # Configuration locale (exclus de Git)
```

## 🤝 Équipe de développement
Projet réalisé par un groupe de 4 étudiants :
**Gorkem387**
**EthanCoombes**
**sunlyimo**
**Oryx87**

---
*Projet tutoré réalisé sous la direction de Christophe Vallot.*
