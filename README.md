# Shop — Marketplace e-commerce

Application marketplace multi-vendeurs développée avec **Symfony 6.4**. Elle permet à des vendeurs de publier des produits et à des clients de les consulter, les ajouter au panier, passer commande et laisser des avis.

## Démo en ligne

Application déployée sur Render : **https://bouffay-shop.onrender.com/fr**

> Le service free tier s'endort après 15 minutes d'inactivité — la première requête après une période d'inactivité peut prendre ~30 secondes.

### Comptes de test (Render)

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | `admin@bouffay.test` | `password` |
| Vendeur | `vendeur@bouffay.test` | `password` |
| Client | `client@bouffay.test` | `password` |

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Backend | Symfony 6.4 (PHP 8.2) |
| Base de données | MySQL 8.0 |
| ORM | Doctrine ORM |
| Templates | Twig |
| Serveur web | Nginx (Alpine) |
| Conteneurisation | Docker / Docker Compose |
| Interface DB | PhpMyAdmin |

## Fonctionnalités

- **Authentification** — inscription, connexion, déconnexion
- **Rôles** — `ROLE_ADMIN`, `ROLE_VENDEUR`, `ROLE_CLIENT`
- **Catalogue produits** — création, édition, suppression avec images, catégories et tags
- **Panier** — ajout/suppression d'articles, calcul du total
- **Commandes** — historique et détail des commandes
- **Profil utilisateur** — gestion des informations et adresses de livraison
- **Favoris** — liste de souhaits par utilisateur
- **Avis** — notation et commentaires sur les produits

## Prérequis

- [Docker](https://www.docker.com/) et Docker Compose installés
- `make` disponible (inclus sur Linux/macOS, sur Windows via [Make for Windows](https://gnuwin32.sourceforge.net/packages/make.htm) ou Git Bash)

## Lancer le projet

### 1. Cloner le dépôt

```bash
git clone <url-du-repo>
cd shop
```

### 2. Démarrer les conteneurs Docker

```bash
make up
# ou sans make :
docker compose up -d
```

Cela démarre quatre services :
- **php** (port 9000) — application Symfony
- **nginx** (port **8080**) — serveur web
- **db** (port **8889**) — MySQL 8.0
- **phpmyadmin** (port **8081**) — interface d'administration de la base de données

### 3. Installer les dépendances PHP

```bash
make composer-install
# ou sans make :
docker compose exec php composer install
```

### 4. Créer la base de données et appliquer les migrations

```bash
make console cmd="doctrine:database:create"
make console cmd="doctrine:migrations:migrate"
# ou sans make :
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:migrations:migrate
```

### 5. (Optionnel) Charger les données de test

```bash
make console cmd="doctrine:fixtures:load"
# ou sans make :
docker compose exec php php bin/console doctrine:fixtures:load
```

### 6. Accéder à l'application

| Service | URL |
|---------|-----|
| Application | http://localhost:8080 |
| PhpMyAdmin | http://localhost:8081 |

---

## Commandes utiles

| Commande | Description |
|----------|-------------|
| `make up` | Démarrer les conteneurs |
| `make down` | Arrêter les conteneurs |
| `make build` | Reconstruire les images Docker |
| `make bash` | Ouvrir un shell dans le conteneur PHP |
| `make composer-install` | Installer les dépendances Composer |
| `make composer-update` | Mettre à jour les dépendances |
| `make cache-clear` | Vider le cache Symfony |
| `make logs` | Afficher les logs en temps réel |
| `make console cmd="<commande>"` | Exécuter une commande `bin/console` |

## Variables d'environnement

Le fichier `.env` contient les valeurs par défaut pour le développement. Pour surcharger localement sans modifier le fichier versionné, créer un fichier `.env.local` :

```dotenv
APP_ENV=dev
DATABASE_URL="mysql://user_devWeb:mdp_devWeb@127.0.0.1:8889/db_devWeb?serverVersion=8.0.32&charset=utf8mb4"
```

## Structure du projet

```
shop/
├── config/          # Configuration Symfony (sécurité, doctrine, services...)
├── migrations/      # Migrations Doctrine
├── public/          # Point d'entrée web (index.php)
├── src/
│   ├── Controller/  # Contrôleurs (Home, Security, Product, Account)
│   ├── Entity/      # Entités Doctrine (User, Product, Order, Cart...)
│   ├── Form/        # Formulaires Symfony
│   ├── Repository/  # Repositories Doctrine
│   └── DataFixtures/# Données de test
├── templates/       # Templates Twig
├── docker-compose.yml
├── Dockerfile
└── Makefile
```
