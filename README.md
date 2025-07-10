# hourProject

Ce projet est une application web basée sur Symfony, MySQL et Nginx, orchestrée avec Docker. Elle permet de gérer des entités telles que les utilisateurs, les transports et les travaux, avec une interface d'administration et des fonctionnalités d'inscription, de connexion et de réinitialisation de mot de passe.

## Prérequis
- [Docker](https://www.docker.com/products/docker-desktop)
- [Docker Compose](https://docs.docker.com/compose/)

## Démarrage rapide

1. **Cloner le dépôt**

```bash
git clone <url-du-repo>
cd hourProject
```

2. **Créer un fichier `.env`**

Copiez le fichier `.env.example` en `.env` et adaptez les variables si besoin.

3. **Lancer les services Docker**

```bash
docker-compose up --build
```

- L'application Symfony sera accessible sur `http://localhost:8080`
- Le conteneur PHP écoute sur le port 9000 (exposé mais non publié)
- La base de données MySQL est accessible uniquement depuis les conteneurs

4. **Arrêter les services**

```bash
docker-compose down
```

5. **Autres commandes utiles**

- Accéder au conteneur PHP :
  ```bash
  docker exec -it php_app bash
  ```
- Lancer les migrations Doctrine :
  ```bash
  docker exec -it php_app php bin/console doctrine:migrations:migrate
  ```
- Installer les dépendances Composer :
  ```bash
  docker exec -it php_app composer install
  ```

## Structure des services

- **app** : Conteneur PHP/Symfony
- **nginx** : Proxy HTTP
- **mysql** : Base de données MySQL

## Volumes persistants
- `mysql-data` : Données MySQL

## Réseau
- `app-network` : Bridge réseau interne entre les services

---

Pour toute question, consultez la documentation Symfony ou ouvrez une issue sur le dépôt.
