# Plugin Ideabox — Documentation

## Présentation

Le plugin **Ideabox** pour GLPI permet de collecter, gérer et suivre les idées soumises par les utilisateurs finaux. Il offre un système de votes, de commentaires et de notifications pour impliquer l'ensemble des utilisateurs dans un processus d'amélioration continue.

- **Licence** : GPLv3+
- **Auteurs** : Xavier CAILLAUD, Infotel

---

## Fonctionnalités

- Soumission d'idées par les utilisateurs (interface centrale et helpdesk)
- Système de **votes** : chaque utilisateur peut voter ou annuler son vote pour une idée
- **Commentaires** sur les idées, avec modération par l'auteur
- **Cycle de vie** des idées : Nouvelle → En étude → En cours → Fermée
- **Notifications par e-mail** : création, modification, suppression d'idée, nouveaux commentaires
- **Recherche rapide** (fuzzy search) dans l'interface helpdesk via Fuse.js
- Association des idées à des **tickets GLPI**
- Gestion des **droits par profil**
- Compatibilité avec le plugin **Service Catalog**
- Compatibilité avec le plugin **Data Injection**
- Respect des **entités** GLPI (restriction par entité)
- **Traductions multilingues** de la configuration (titre et description de la boîte à idées)

---

## Installation

1. Télécharger l'archive depuis [GitHub Releases](https://github.com/InfotelGLPI/ideabox/releases).
2. Décompresser dans le répertoire `plugins/` de GLPI.
3. Se connecter à GLPI en tant qu'administrateur.
4. Aller dans **Configuration → Plugins** et activer **Ideabox**.

---

## Configuration

### Droits par profil

Dans **Administration → Profils**, un onglet **Ideas** apparaît sur chaque profil.

| Droit | Description |
|---|---|
| `plugin_ideabox` | Accès complet aux idées (lecture, création, modification, suppression) |
| `plugin_ideabox_open_ticket` | Autoriser l'association des idées aux tickets |

Les droits standard GLPI s'appliquent : `READ`, `CREATE`, `UPDATE`, `DELETE`, `PURGE`.

### Configuration générale

Dans le menu **Ideabox → Configuration**, deux champs sont disponibles :

| Champ | Description |
|---|---|
| **Titre** | Titre affiché en tête de la liste des idées (traductible par langue) |
| **Commentaire** | Description ou accroche affichée aux utilisateurs (traductible par langue) |

La configuration supporte des **traductions par langue** via l'onglet **Traductions** (table `glpi_plugin_ideabox_configtranslations`).

---

## Utilisation

### Soumettre une idée

1. Accéder au menu **Outils → Ideas** (interface centrale) ou à l'entrée **Ideabox** du menu helpdesk.
2. Cliquer sur **Ajouter**.
3. Renseigner :
   - **Nom** *(obligatoire)* : titre court de l'idée
   - **Description** *(obligatoire)* : détail de l'idée (éditeur riche TinyMCE)
4. Valider. Une notification e-mail est envoyée si les notifications sont activées.

> Seul l'auteur peut modifier une idée en interface helpdesk. Les administrateurs peuvent modifier toute idée depuis l'interface centrale.

### Voter pour une idée

Dans la liste des idées, cliquer sur le bouton **👍** (vote positif). Un second clic annule le vote. Chaque utilisateur ne peut voter qu'une seule fois par idée.

### Commenter une idée

Sur la fiche d'une idée, onglet **Comments** :
- **Nom** *(obligatoire)* : titre du commentaire
- **Description** *(obligatoire)* : contenu du commentaire

Un auteur ne peut supprimer que ses propres commentaires (en interface helpdesk).

### Cycle de vie d'une idée

| Statut | Valeur | Couleur |
|---|---|---|
| Nouvelle | 1 | Bleu (`#2d98b1`) |
| En étude | 2 | Jaune (`#D1A712`) |
| En cours | 3 | Vert (`#4DAA77`) |
| Fermée | 4 | Orange (`#d5703b`) |

Le statut est modifiable depuis la fiche de l'idée (interface centrale, droit UPDATE requis).

### Associer une idée à un ticket

Si le droit `plugin_ideabox_open_ticket` est activé sur le profil, les idées apparaissent dans le sélecteur d'éléments liés lors de la création d'un ticket (champ **Éléments associables à un ticket**).

---

## Notifications

Le plugin crée automatiquement les notifications suivantes à l'installation :

| Événement | Description |
|---|---|
| `new` | Nouvelle idée soumise |
| `update` | Idée modifiée |
| `delete` | Idée supprimée |
| `newcomment` | Nouveau commentaire sur une idée |
| `updatecomment` | Commentaire modifié |
| `deletecomment` | Commentaire supprimé |

Les modèles de notification sont configurables dans **Configuration → Notifications**.  
Variables disponibles dans les templates : `##ideabox.url##`, `##ideabox.name##`, `##ideabox.comment##`, `##ideabox.entity##`, et les variables de boucle `##FOREACHcomments##`.

---

## Structure des tables

| Table | Description |
|---|---|
| `glpi_plugin_ideabox_ideaboxes` | Idées (nom, description, auteur, date, statut, entité) |
| `glpi_plugin_ideabox_comments` | Commentaires attachés aux idées |
| `glpi_plugin_ideabox_votes` | Votes (un par utilisateur par idée) |
| `glpi_plugin_ideabox_configs` | Configuration du plugin (titre, description) |
| `glpi_plugin_ideabox_configtranslations` | Traductions de la configuration par langue |

---

## Intégrations

### Plugin Service Catalog

Si le plugin **servicecatalog** est actif, Ideabox s'intègre dans le catalogue de services via le hook `servicecatalog`. Le lien helpdesk standard est alors désactivé au profit de l'interface du catalogue.

### Plugin Data Injection

Les idées peuvent être importées en masse via le plugin **datainjection** (type `GlpiPlugin\Ideabox\Ideabox`, code interne `4900`).

---

## Désinstallation

Dans **Configuration → Plugins**, désactiver puis désinstaller **Ideabox**. Les tables suivantes sont supprimées :

- `glpi_plugin_ideabox_ideaboxes`
- `glpi_plugin_ideabox_comments`
- `glpi_plugin_ideabox_votes`
- `glpi_plugin_ideabox_configs`
- `glpi_plugin_ideabox_configtranslations`

Les notifications, modèles de notification et préférences d'affichage associés sont également supprimés.

---

## Liens utiles

- [Dépôt GitHub](https://github.com/InfotelGLPI/ideabox)
- [Signaler un bug](https://github.com/InfotelGLPI/ideabox/issues)
- [Contribuer à la traduction](https://explore.transifex.com/infotelGLPI/GLPI_ideabox/)
- [Blog Infotel GLPI](https://blogglpi.infotel.com)
