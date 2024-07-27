## Présentation de l'application CineList

CineList est un projet personnel d'application web Symfony avec docker qui permet aux utilisateurs de créer et de gérer une liste de films favoris et de playlists. Grâce à l'intégration avec l'API TMDB (The Movie Database), les utilisateurs peuvent facilement rechercher des films, consulter des informations détaillées, et ajouter leurs films préférés à leur liste personnelle de favoris ou à la playlist de leur choix.

Cette application est destinée à être utilisée dans un environnement de développement et n'est pas adaptée à un usage en production.

#### Les principales fonctionnalités sont:
- La création d'un compte
- La validation du compte par mail
- Le renouvellement du mot passe en cas d'oubli
- La consultation des informations détaillées d'un film
- La consultation des informations d'un membre du casting d'un film
- La recherche de films
- L'ajout/retrait d'un film à une liste de favoris
- La création de playlists
- L'ajout/retrait d'un film d'une playlist
- La possibilité de rendre une playlist publique ou privée
- La copie de playlists d'autres utilisateurs

#### Les fonctionnalités à développer sont:
- L'authentification à double facteur
- Lexport et le partage de playlist par mail ou via l'application
- La recherche avancée (par acteur, par note, par date, etc...)
- L'ajout d'avatar pour l'utilisateur
- L'ajout d'un rôle d'administrateur pour gérer les utilisateurs via un dashboard

## Installation

### Clonage du répertoire du projet
```
git clone https://github.com/SimonPineaut/CineList.git
cd CineList
```

Une fois positionné à la racine du projet cloné, deux méthodes d'installation s'offrent à vous : une avec Docker et une sans Docker.

### Prérequis avec Docker
- Docker (<a href="https://www.docker.com/"> documentation officielle </a>).
- Docker Compose (<a href="https://docs.docker.com/compose/"> documentation officielle </a>).
- Docker desktop, optionnel mais recommandé (<a href="https://www.docker.com/products/docker-desktop/"> documentation officielle </a>).
- Pour les systèmes Windows, Docker nécessitant Linux, l'utilisation de WSL est recommandée (<a href="https://docs.docker.com/desktop/wsl/"> documentation officielle </a>).

### Installation avec docker
1. **Construire et démarrer les conteneurs Docker**
```
docker compose up --build
```
2. **Ouvrir l'application**

Votre application est prête à cette URL : <a href="https://localhost/">https://localhost</a>

Lors du processus d'inscription, un mail de confirmation vous sera envoyé. La boîte mail est disponible à cette URL : <a href="http://localhost:8025/">http://localhost:8025/</a>

### Prérequis sans Docker
- PHP (<a href="https://www.php.net/manual/fr/install.php"> documentation officielle </a>)
- Composer (<a href="https://getcomposer.org/doc/00-intro.md"> documentation officielle </a>)
- Node.js et npm (<a href="https://docs.npmjs.com/downloading-and-installing-node-js-and-npm"> documentation officielle </a>)
- Symfony CLI (optionnel mais recommandé) (<a href="https://symfony.com/download?ref=material-tailwind"> documentation officielle </a>)

### Installation et configuration sans Docker 

1. **Installation des dépendances PHP**
```
composer install
```

2. **Installation des dépendances JavaScript**
```
npm install
```

3. **Configuration des variables d'environnement**  Copier le fichier `.env` et renommer la copie en `.env.local` :
```
cp .env .env.local
```
Modifier les valeurs selon votre environnement local, notamment pour la connexion à la base de données et l'envoi de mail de confirmation.

4. **Création de la base de données**
```
php bin/console doctrine:database:create
```

5. **Création des migrations**
```
php bin/console make:migration
```

6. **Exécution des migrations**
```
php bin/console doctrine:migrations:migrate
```

7. **Compilation des assets**
```
npm run dev
```

8. **Démarrage du serveur de développement**
```
symfony server:start
```
Votre application est prête à cette URL : <a href="https://localhost:8000">https://localhost:8000</a>

# Quelques commandes utiles

## Avec Docker

**Vérifier l'état des conteneurs Docker**
```
docker ps
``` 

**Arrêter les conteneurs**
```
docker compose down
``` 

**Recréer les conteneurs (par exemple après une modification du Dockerfile)**
```
docker compose up -d --build
``` 

**Vérifier les logs**
```
docker logs <NOM_DU_CONTAINER>
``` 

**Installer une nouvelle dépendance PHP avec Composer**
```
docker compose exec <NOM_DU_PACKAGE> composer require <NOM_DU_PACKAGE>
``` 

**Installer une nouvelle dépendance JavaScript avec npm**
```
docker compose exec <NOM_DU_CONTAINER> npm install <NOM_DU_PACKAGE>
``` 

**Compiler les assets**
```
docker compose exec <NOM_DU_CONTAINER> npm run build
``` 

## Sans Docker

**Afficher les commandes disponibles**
```
php bin/console list
``` 

**Vérifier les exigences de Symfony**
```
symfony check:requirements
```

**Nettoyer le cache**
```
php bin/console cache:clear
```

**Créer un contrôleur**
```
php bin/console make:controller [NomDuController]
```

**Créer une entité Doctrine**
```
php bin/console make:entity
```

**Suppression de la base de données :**<font color="red">  Cette commande n'est pas réversible</font>
```
php bin/console doctrine:database:drop --force
```

**Stopper le serveur de développement**
```
symfony server:stop
```

**Créer une entité Doctrine**
```
php bin/console make:entity
```

**Vérifier les migrations à appliquer**
```
php bin/console doctrine:migrations:status
```

**Générer des fixtures**
```
php bin/console doctrine:fixtures:load
```

**Créer un formulaire**
```
php bin/console make:form
```

**Générer un CRUD (Create, Read, Update, Delete)**
```
php bin/console make:crud [entité]
```



