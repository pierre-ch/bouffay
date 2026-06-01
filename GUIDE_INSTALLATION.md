# Bouffay - Projet Web E-Commerce

Ce projet est une application web e-commerce de vente de snacks internationaux (Bouffay) développée avec Symfony 6/7.

## 1. Installation sur une machine vierge

Étant donné que la machine cible ne dispose ni de PHP, ni de Composer, ni de Symfony, voici les étapes complètes pour installer et lancer l'application. La configuration requise (port 8889) indique que l'environnement privilégié est **MAMP** (Mac) ou un environnement équivalent.

### Prérequis
1. **Installer MAMP** (ou XAMPP/WAMP) :
   - Téléchargez et installez MAMP depuis le site officiel.
   - Lancez MAMP et assurez-vous que les serveurs Apache et MySQL sont démarrés. Le port MySQL par défaut sur MAMP est bien **8889**.
   - Ouvrez phpMyAdmin via MAMP, allez dans l'onglet "Comptes utilisateurs" et créez un utilisateur nommé `user_devWeb` avec le mot de passe `mdp_devWeb`. Cochez la case "Créer une base de données portant son nom et donner tous les privilèges". (Ou exécutez simplement les requêtes SQL classiques).

2. **Installer Composer** :
   - Téléchargez l'installeur depuis `getcomposer.org`.
   - Suivez les instructions d'installation globale.

3. **Installer Symfony CLI** (Optionnel mais recommandé) :
   - Téléchargez Symfony CLI depuis `symfony.com/download` pour lancer le serveur local facilement.

### Initialisation du projet
1. Extrayez l'archive ZIP du projet dans un dossier de votre choix (ex: `htdocs` ou `www`).
2. Ouvrez un terminal dans le dossier du projet.
3. Installez les dépendances PHP (le dossier `vendor` n'étant pas inclus) :
   ```bash
   composer install
   ```
4. Le fichier `.env` est déjà configuré avec les bons paramètres de base de données :
   `DATABASE_URL="mysql://user_devWeb:mdp_devWeb@localhost:8889/db_devWeb?serverVersion=8.0&charset=utf8"`
5. Créez la base de données (si non créée manuellement) et exécutez les migrations :
   ```bash
   php bin/console doctrine:database:create --if-not-exists
   php bin/console doctrine:migrations:migrate -n
   ```
6. Chargez les données de test (Fixtures) pour avoir des produits, utilisateurs et avis fonctionnels :
   ```bash
   php bin/console doctrine:fixtures:load -n
   ```
7. Démarrez le serveur local :
   ```bash
   symfony serve -d
   ```
   L'application est maintenant accessible sur `http://localhost:8000`.

---

## 2. Présentation des Bundles utilisés

L'application repose sur le socle Symfony, enrichi par plusieurs bundles essentiels pour répondre au cahier des charges de manière optimale :

* **Doctrine ORM (`doctrine/doctrine-bundle`)** : Choix incontournable dans l'écosystème Symfony pour la gestion de la persistance des données (CRUD). Il permet de mapper nos entités PHP (User, Product, Order) directement en base de données de façon sécurisée (prévention des injections SQL).
* **Symfony Security (`symfony/security-bundle`)** : Utilisé pour l'authentification (hashage des mots de passe) et le contrôle des droits d'accès via les rôles (`ROLE_CLIENT`, `ROLE_VENDEUR`, `ROLE_ADMIN`). Il garantit la sécurité des espaces personnels.
* **EasyAdmin (`easycorp/easyadmin-bundle`)** : Choisi pour générer rapidement et proprement un back-office d'administration puissant et ergonomique (Dashboard). Il a permis de créer des interfaces de gestion complètes pour toutes nos entités en un temps record.
* **Twig (`symfony/twig-bundle`)** : Moteur de template natif de Symfony. Indispensable pour séparer la logique métier (Contrôleurs) de l'affichage (Vues HTML), et pour intégrer facilement des composants réutilisables (header, footer, formulaires).
* **DoctrineFixtures (`doctrine/doctrine-fixtures-bundle`)** : Utilisé pour générer un jeu de données cohérent (fausses annonces, utilisateurs, avis) afin de tester l'application et de réaliser des démonstrations fonctionnelles immédiates.
* **Monolog (`symfony/monolog-bundle`)** : Implémenté pour tracer les actions (système de logs). Nous l'avons configuré spécifiquement pour tracer les actions d'administration dans un fichier dédié, renforçant ainsi la sécurité et l'auditabilité de l'application.
* **Symfony Translation (`symfony/translation`)** : Nécessaire pour répondre au critère de l'application multilingue (FR/EN).

---

## 3. Déploiement sur Infomaniak (Hébergement Mutualisé)

Pour déployer ce projet Symfony sur un hébergement web classique Infomaniak, voici la procédure recommandée :

### Étape 1 : Préparation de la base de données (Infomaniak Manager)
1. Connectez-vous à votre interface Manager Infomaniak.
2. Allez dans **Hébergement Web** > **Bases de données**.
3. Créez une nouvelle base de données MySQL/MariaDB (ex: `ik_shop`).
4. Créez un utilisateur MySQL et associez-le à cette base. Notez les informations (Serveur, Nom de la base, Utilisateur, Mot de passe).

### Étape 2 : Transfert des fichiers
1. Dans le Manager Infomaniak, allez dans **Sites Web** > **Gestion des sites** et notez le dossier cible de votre site (souvent `web/` ou `sites/monsite.com/`).
2. **IMPORTANT** : Pour des raisons de sécurité, le document root de votre domaine doit pointer vers le dossier `public/` de Symfony. Modifiez la configuration du site dans Infomaniak pour que le répertoire cible pointe sur `web/public/` (ou le chemin correspondant).
3. Via un client FTP (FileZilla) ou via le SSH d'Infomaniak, transférez l'intégralité des fichiers de votre projet (sans le dossier `vendor` ni `.git`).

### Étape 3 : Configuration et Installation (via SSH Infomaniak)
1. Connectez-vous à votre hébergement en SSH (les identifiants sont dans le Manager Infomaniak).
2. Naviguez vers le dossier de votre projet : `cd web/` (ou le nom défini).
3. Créez un fichier `.env.local` pour surcharger la configuration avec les identifiants de base de données Infomaniak :
   ```bash
   APP_ENV=prod
   DATABASE_URL="mysql://VOTRE_USER_IK:VOTRE_MDP_IK@VOTRE_SERVEUR_IK/VOTRE_BASE_IK?serverVersion=10.5.15-MariaDB&charset=utf8mb4"
   ```
4. Exécutez Composer pour installer les dépendances en mode production :
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
5. Appliquez les migrations à la base de données de production :
   ```bash
   APP_ENV=prod php bin/console doctrine:migrations:migrate -n
   ```
6. Videz le cache :
   ```bash
   APP_ENV=prod php bin/console cache:clear
   ```

*Lien d'accès de test* : https://bouffay.infomaniak.com (À adapter selon le nom de domaine que vous aurez réservé).
