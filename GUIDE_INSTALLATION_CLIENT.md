# Guide d'Installation Hors Ligne - MSK DRY PLUS

Ce guide explique étape par étape comment installer et configurer l'application chez votre client pour une utilisation 100% hors ligne et autonome.

---

## 1. Prérequis sur le PC du Client

* **Système d'exploitation** : Windows 10 ou Windows 11 (64 bits).
* **Navigateur** : Microsoft Edge (déjà intégré à Windows) ou Google Chrome.
* **Environnement Serveur** : Laragon Full 64 bits (sur `C:\laragon`).
* *Note : Node.js, Git et Composer ne sont **PAS** nécessaires sur le PC du client car les dépendances et fichiers du site sont déjà présents.*

---

## 2. Étapes d'Installation chez le Client

### Étape 1 : Installer Laragon et Copier le Projet
1. Installez Laragon sur `C:\laragon`.
2. Ouvrez Laragon et cliquez sur **« Tout Démarrer »**.
3. Placez le dossier du projet dans le répertoire `www` :
   `C:\laragon\www\Paradou` (ou `C:\laragon\www\msk-dry-plus`).

### Étape 2 : Importer la Base de Données
1. Dans Laragon, cliquez sur **« Terminal »**.
2. Exécutez simplement la commande :
   ```cmd
   mysql -u root < C:\laragon\www\Paradou\database.sql
   ```
   *(Ou importez le fichier `database.sql` via HeidiSQL).*

### Étape 3 : Créer le Raccourci sur le Bureau
1. Allez dans le dossier `C:\laragon\www\Paradou\`.
2. Double-cliquez sur le fichier :
   👉 **`Creer_Raccourci_Bureau.bat`**
3. Un raccourci nommé **« MSK DRY PLUS - Caisse »** avec le logo de l'application apparaît instantanément sur le Bureau de l'ordinateur.

### Étape 4 : Configurer l'Imprimante Ticket (Impression Directe)
1. Branchez votre imprimante thermique en USB et installez son pilote Windows.
2. Dans **Paramètres Windows** > **Imprimantes et scanners**, définissez l'imprimante thermique comme **« Imprimante par défaut »**.
3. Réglez le format de papier sur **80 mm** dans les préférences d'impression.
4. L'impression automatique sans boîte de dialogue est déjà activée dans le lanceur.

---

## 3. Utilisation au Quotidien

* **Pour lancer l'application** : Double-cliquez simplement sur le raccourci **« MSK DRY PLUS - Caisse »** sur le Bureau.
  * Les services locaux se lancent automatiquement en arrière-plan sans aucune fenêtre noire.
  * L'application s'ouvre directement en plein écran en mode Application dédiée tactile.

* **Pour sauvegarder les données** :
  * Double-cliquez sur **`Sauvegarder_Base_De_Donnees.bat`**.
  * Un fichier horodaté est automatiquement créé dans le dossier `sauvegardes/` (pratique pour copier sur clé USB).

* **Pour le démarrage automatique (Optionnel)** :
  * Si le client souhaite que la caisse s'ouvre toute seule dès qu'il allume son ordinateur, double-cliquez sur **`Installer_Demarrage_Auto.bat`** et tapez `O`.

---

## 4. Comptes Utilisateurs par Défaut

* **Administrateur** :
  * Identifiant : `admin` (ou `admin@paradou.com`)
  * Mot de passe : `password`
* **Caissier** :
  * Identifiant : `caissier` (ou `caissier@paradou.com`)
  * Mot de passe : `password`

