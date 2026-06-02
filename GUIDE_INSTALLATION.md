# Guide d'Installation et d'Utilisation : Bouffay Shop

🌍 **Site en ligne (Production) :** [https://bouffay-x22l4.ondigitalocean.app/fr/](https://bouffay-x22l4.ondigitalocean.app/fr/)

Ce guide détaille les étapes pour installer et exécuter l'application en local sur une machine vierge de tout environnement de développement PHP (Windows, macOS ou Linux). 

---

## 1. Installation des Prérequis

Puisque votre machine ne dispose ni de PHP, ni de Composer, ni de Symfony, nous devons d'abord installer ces outils fondamentaux.

### A. Installer un environnement serveur (PHP + Base de données)
La méthode la plus simple pour obtenir PHP et une base de données (MySQL/MariaDB) sans configuration complexe est d'utiliser un environnement tout-en-un.
- **Windows :** Téléchargez et installez [XAMPP](https://www.apachefriends.org/fr/index.html) ou [WampServer](https://www.wampserver.com/).
- **macOS :** Téléchargez et installez [MAMP](https://www.mamp.info/en/mac/) ou [XAMPP](https://www.apachefriends.org/fr/index.html).
- **Linux :** Installez PHP et MariaDB via le gestionnaire de paquets de votre distribution (ex: `sudo apt install php php-cli php-mysql mariadb-server`).

*Note : Assurez-vous que l'exécutable `php` est bien ajouté à la variable d'environnement `PATH` de votre système afin de pouvoir l'utiliser en ligne de commande.*

### B. Installer Composer (Gestionnaire de dépendances PHP)
Composer permet d'installer toutes les bibliothèques requises par le projet.
1. Rendez-vous sur [getcomposer.org/download](https://getcomposer.org/download/).
2. **Windows :** Téléchargez l'installateur `Composer-Setup.exe` et laissez-vous guider.
3. **macOS / Linux :** Exécutez les commandes fournies sur le site dans votre terminal pour installer `composer` globalement (souvent déplacé vers `/usr/local/bin/composer`).

### C. Installer Symfony CLI (Outil en ligne de commande)
L'interface en ligne de commande de Symfony offre un serveur web local performant pour tester le projet.
1. Rendez-vous sur [symfony.com/download](https://symfony.com/download).
2. **Windows :** Utilisez la commande Scoop ou téléchargez l'exécutable manuel.
3. **macOS :** Utilisez Homebrew : `brew install symfony-cli/tap/symfony-cli`
4. **Linux :** Utilisez le script bash d'installation fourni sur la page.

---

## 2. Installation de l'Application

Une fois les prérequis installés, ouvrez votre terminal (ou l'invite de commande) et suivez ces étapes :

### Étape 1 : Récupérer le code source
Clonez le dépôt du projet (si Git est installé) ou téléchargez l'archive ZIP et extrayez-la dans le dossier de votre choix.
```bash
git clone https://github.com/pierre-ch/bouffay.git
cd bouffay
```

### Étape 2 : Installer les dépendances
Installez les bibliothèques PHP requises par le projet à l'aide de Composer.
```bash
composer install
```

### Étape 3 : Configurer l'environnement
Copiez le fichier `.env` pour créer un fichier `.env.local` qui contiendra votre configuration propre à votre machine.
```bash
cp .env .env.local
```
Ouvrez le fichier `.env.local` avec un éditeur de texte et configurez l'accès à votre base de données en modifiant la ligne `DATABASE_URL`. Par exemple, pour MAMP avec MySQL :
```env
DATABASE_URL="mysql://root:root@127.0.0.1:8889/bouffay_db?serverVersion=8.0&charset=utf8mb4"
```
*(Adaptez les identifiants `root:root` et le port selon si vous utilisez XAMPP, MAMP ou autre).*

### Étape 4 : Créer la base de données et les tables
Lancez les commandes suivantes pour construire la structure de la base de données :
```bash
# Crée la base de données (ex: bouffay_db)
symfony console doctrine:database:create

# Génère les tables de l'application
symfony console doctrine:schema:update --force
```

### Étape 5 : Charger les fausses données (Fixtures)
Pour ne pas partir d'un site vide, insérez les données de test (produits, utilisateurs) :
```bash
symfony console doctrine:fixtures:load -n
```

---

## 3. Utilisation de l'Application

Démarrez le serveur de développement local fourni par Symfony CLI :
```bash
symfony server:start -d
```
Le terminal vous affichera une URL locale, généralement `https://127.0.0.1:8000`.
Ouvrez cette URL dans votre navigateur pour profiter du site !

**Comptes de test disponibles :**
- **Administrateur :** `admin@bouffay.com` / Mot de passe : `password`
- **Vendeur :** `vendeur@bouffay.com` / Mot de passe : `password`
- **Utilisateur :** `user@bouffay.com` / Mot de passe : `password`

---

## 4. Présentation et Justification des Bundles Utilisés

Symfony est structuré autour de composants modulaires appelés **Bundles**. Voici les principaux utilisés dans ce projet et la raison de leur choix :

### 1. FrameworkBundle & TwigBundle
- **Rôle :** Coeur de l'application et moteur de template.
- **Justification :** Indispensables pour le routage, la gestion des requêtes HTTP et l'affichage des vues HTML. Twig permet de créer des interfaces propres avec de l'héritage de blocs.

### 2. DoctrineBundle & DoctrineMigrationsBundle
- **Rôle :** ORM (Object-Relational Mapping).
- **Justification :** Permet de manipuler la base de données à travers des objets PHP (Entités) plutôt que d'écrire des requêtes SQL brutes. Il simplifie grandement la persistance des produits, utilisateurs et commandes, et permet un versioning sécurisé de la structure de base (Migrations).

### 3. SecurityBundle
- **Rôle :** Authentification et contrôle d'accès.
- **Justification :** Essentiel pour sécuriser les routes (ex: forcer la connexion pour publier une annonce) et gérer les rôles (`ROLE_USER`, `ROLE_VENDEUR`, `ROLE_ADMIN`). Il chiffre les mots de passe et sécurise les sessions de manière robuste.

### 4. EasyAdminBundle
- **Rôle :** Création instantanée d'un Back-Office.
- **Justification :** Plutôt que de redévelopper entièrement une interface d'administration pour la gestion des utilisateurs et des produits, EasyAdmin génère automatiquement un dashboard professionnel et sécurisé à partir de nos entités Doctrine, ce qui représente un gain de temps majeur.

### 5. DoctrineFixturesBundle (& FakerPHP)
- **Rôle :** Génération de données factices.
- **Justification :** Permet d'injecter rapidement des centaines de faux utilisateurs ou de faux produits (comme les Takis, Oreos) dans la base pour tester les performances, la pagination et le design du site sans avoir à tout saisir à la main.

### 6. TwigComponentBundle
- **Rôle :** Création de composants UI réutilisables.
- **Justification :** Rend le code des templates beaucoup plus lisible et modulaire. Plutôt que de dupliquer du HTML (comme des cartes produits ou des modales), on isole le composant et on l'appelle via une balise stylisée `<twig:Card />`, modernisant ainsi le développement Frontend.

### 7. MakerBundle
- **Rôle :** Générateur de code en ligne de commande.
- **Justification :** Utilisé uniquement en développement, il accélère considérablement la création d'Entités, de Contrôleurs ou de Formulaires avec des commandes comme `make:controller`, évitant un fastidieux code répétitif (boilerplate).
