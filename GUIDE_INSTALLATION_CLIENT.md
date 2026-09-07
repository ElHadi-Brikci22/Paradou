# Guide d'Installation Hors Ligne - MSK DRY PLUS

Ce guide explique étape par étape comment installer et configurer l'application chez votre client pour une utilisation 100% hors ligne et autonome.

---

## 1. Prérequis sur le PC du Client

* **Système d'exploitation** : Windows 10 ou Windows 11 (64 bits).
* **Navigateur** : Microsoft Edge (déjà intégré à Windows) ou Google Chrome.
* **Environnement Serveur** : Laragon (ou le dossier portable `C:\laragon`).
* *Note : Node.js et Git ne sont **PAS** nécessaires sur le PC du client car les fichiers du site sont déjà compilés.*

---

## 2. Étapes d'Installation chez le Client

### Étape 1 : Copier les dossiers sur le PC du client
1. Si le client n'a pas Laragon : Installez Laragon sur `C:\laragon` (ou copiez votre dossier `C:\laragon` sur sa machine).
2. Placez le dossier du projet dans le répertoire `www` :
   `C:\laragon\www\msk-dry-plus`

### Étape 2 : Importer la Base de Données
1. Lancez Laragon (ou démarrez MySQL).
2. Ouvrez le terminal Laragon ou HeidiSQL / phpMyAdmin.
3. Importez le fichier `database.sql` situé à la racine du projet :
   * Soit via la commande :
     ```cmd
     mysql -u root < C:\laragon\www\msk-dry-plus\database.sql
     ```
   * Soit en ouvrant le fichier `database.sql` dans HeidiSQL et en cliquant sur **Exécuter**.

### Étape 3 : Créer le Raccourci sur le Bureau
1. Allez dans le dossier `C:\laragon\www\msk-dry-plus\`.
2. Double-cliquez sur le fichier :
   👉 **`Creer_Raccourci_Bureau.bat`**
3. Un raccourci nommé **« MSK DRY PLUS - Caisse »** avec le logo de l'application apparaît instantanément sur le Bureau de l'ordinateur.

---

## 3. Utilisation au Quotidien

* **Pour lancer l'application** : Double-cliquez simplement sur le raccourci **« MSK DRY PLUS - Caisse »** sur le Bureau.
  * Les services locaux se lancent automatiquement en arrière-plan sans aucune fenêtre noire.
  * L'application s'ouvre directement en plein écran en mode Application dédiée (sans barre d'adresse ni onglets).

* **Pour sauvegarder les données** :
  * Double-cliquez sur **`Sauvegarder_Base_De_Donnees.bat`**.
  * Un fichier horodaté est automatiquement créé dans le dossier `sauvegardes/` (pratique pour copier sur clé USB).

* **Pour le démarrage automatique (Optionnel)** :
  * Si le client souhaite que la caisse s'ouvre toute seule dès qu'il allume son ordinateur, double-cliquez sur **`Installer_Demarrage_Auto.bat`** et tapez `O`.

---

## 4. Comptes Utilisateurs par Défaut

* **Administrateur** :
  * Identifiant : `admin` (ou `admin@paradou.com`)
  * Mot de passe : `password` (ou celui configuré)
* **Caissier** :
  * Identifiant : `caissier` (ou `caissier@paradou.com`)
  * Mot de passe : `password`
