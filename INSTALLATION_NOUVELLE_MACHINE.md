# Guide d'Installation et Déploiement sur une Nouvelle Machine
## Application de Caisse & Gestion de Pressing - MSK DRY PLUS

Ce document décrit pas à pas la procédure complète pour installer et démarrer l'application sur un nouvel ordinateur (100% autonome et hors ligne, sans connexion Internet requise).

---

### 📦 1. Ce qu'il faut installer au préalable
Puisque le dossier copié contient déjà toutes les dépendances PHP (`vendor/`), les styles compilés (`public/build/`) et la configuration (`.env`), **un seul logiciel** est nécessaire :

* **[Laragon Full (64 bits)](https://laragon.org/download/)** *(PHP 8.x + MySQL inclus)*.
* *(Pas besoin d'installer Node.js, Git ou Composer).*
* *(Navigateur Microsoft Edge ou Google Chrome déjà présent sur Windows).*

---

### 🚀 2. Procédure d'installation en 5 étapes simples

#### Étape 1 : Installer Laragon
1. Lancez l'installeur de Laragon sur la nouvelle machine.
2. Laissez l'emplacement par défaut : `C:\laragon`.
3. Une fois l'installation terminée, ouvrez Laragon et cliquez sur le bouton **« Tout Démarrer »** (*Start All*).
   *(Vérifiez que Apache et MySQL ont le voyant vert).*

#### Étape 2 : Copier le dossier du projet
1. Collez votre dossier de projet dans le répertoire `www` de Laragon :
   👉 `C:\laragon\www\Paradou` *(ou `C:\laragon\www\msk-dry-plus`)*.
2. Assurez-vous que le fichier **`.env`** est bien présent à la racine du dossier.

#### Étape 3 : Importer la Base de Données
Le fichier `database.sql` situé à la racine du projet contient déjà toute la structure et toutes vos données (148 articles, rubriques, prix, comptes utilisateurs).

* **Méthode Rapide (Recommandée)** :
  1. Dans la fenêtre de Laragon, cliquez sur le bouton **« Terminal »**.
  2. Tapez la commande suivante puis appuyez sur **Entrée** :
     ```cmd
     mysql -u root < C:\laragon\www\Paradou\database.sql
     ```
  *(La base de données `msk_dry_plus` et toutes ses tables se créent automatiquement en 3 secondes).*

* **Méthode Graphique (via HeidiSQL)** :
  1. Dans Laragon, cliquez sur le bouton **« Base de données »** (HeidiSQL s'ouvre).
  2. Cliquez sur **Ouvrir** (utilisateur `root`, mot de passe vide).
  3. Allez dans le menu **Fichier** > **Charger un fichier SQL...** (ou `Ctrl + O`).
  4. Sélectionnez `database.sql` dans le dossier du projet.
  5. Cliquez sur le bouton bleu **Exécuter** (ou appuyez sur la touche `F9`).

#### Étape 4 : Créer le Raccourci sur le Bureau
1. Ouvrez le dossier `C:\laragon\www\Paradou\`.
2. Double-cliquez sur le fichier :
   👉 **`Creer_Raccourci_Bureau.bat`**
3. Un raccourci nommé **« MSK DRY PLUS - Caisse »** avec le logo de votre pressing apparaît instantanément sur le Bureau de Windows.

#### Étape 5 : Configurer l'Imprimante Ticket
1. Branchez votre imprimante thermique en USB et installez son pilote Windows officiel (*Xprinter, POS-80, Epson, etc.*).
2. Dans Windows : **Paramètres** > **Périphériques** > **Imprimantes et scanners**.
3. Cliquez sur votre imprimante thermique puis sur **« Gérer »** > **« Définir par défaut »**.
4. Dans **Préférences d'impression**, vérifiez que la largeur du papier est réglée sur **80 mm** (*Roll Paper 80x297mm*).

---

### 🖥️ 3. Utilisation au quotidien

#### Démarrage de la caisse :
* Double-cliquez simplement sur l'icône **« MSK DRY PLUS - Caisse »** sur le Bureau.
* L'application s'ouvre directement en plein écran (mode application dédiée / tactile).
* Grâce au mode direct activé, chaque clic sur **« Imprimer »** fait sortir le ticket immédiatement sur le rouleau thermique sans afficher de boîte de dialogue.

#### Comptes de connexion :
* **Administrateur** :
  * Identifiant : `admin` (ou `admin@paradou.com`)
  * Mot de passe : `password` (ou votre mot de passe personnalisé)
* **Caissier** :
  * Identifiant : `caissier` (ou `caissier@paradou.com`)
  * Mot de passe : `password`

#### Sauvegarde des données (très important) :
* Pour mettre vos données à l'abri sur une clé USB :
  Double-cliquez sur **`Sauvegarder_Base_De_Donnees.bat`**.
* Un fichier `.sql` horodaté avec la date du jour est automatiquement créé dans le dossier `sauvegardes/`.

#### Démarrage automatique à l'allumage du PC (Optionnel) :
* Si le client souhaite que la caisse s'ouvre toute seule dès qu'il allume l'ordinateur le matin :
  Double-cliquez sur **`Installer_Demarrage_Auto.bat`** et tapez `O`.
