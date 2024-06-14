# OpenGAIA - Projet Cartes de Prières
<small>CMS de ECommerce sous la licence Creative Commons Attribution-NoDerivs (CC BY-ND), développé par OpenPixl.</small>
**Réutilisation** : Vous pouvez réutiliser l'œuvre à des fins personnelles et commerciales.
**Crédit** : Vous devez créditer l'auteur original de manière appropriée.
**Pas de modifications** : Vous n'avez pas le droit de modifier, adapter ou créer des œuvres dérivées de l'œuvre originale.

### AVANT LA PROCEDURE DE DEPLOIEMENT DU PROJET

#### 1. PRÉPARER LES CONTENEURS "DOCKER"

##### 1.1 Cloner la Stack
Cloner la stack dans votre dossier de dev

```bash
git clone git@github.com:Corwin40/StackSymfony.git
```

##### 1.2 Préparation des variables
Le fichier _docker-compose.yaml_ s'appuie sur un fichier _.env_ contenant l"nesemble des variables nécessaire : 
- à la base de donnée, 
- aux differents ports attribués à vos conteneurs, 
- ...  

Créez ce fichier avec les commandes suivantes depuis votre terminal:

```bash
mv StackSymfony SoeurMarie
cd SoeurMarie
nano .env
```
Puis copier/coller le contenu suivant.

```
# Variable Project
PROJECT=soeurmarie
PROJECT_IP=22
RESTART=no

# Variables Mariadb
PMA_HOST=db
MARIA_ROOT_PASSWORD=Corwin_40280
MARIADB_USER=SoeurMarie
MARIADB_PASSWORD=M2022Lourdes65
MARIADB_DBNAME=sm_dbsf5

# Variables Serveur apache php
HTTP_HOST_PORT=80
HTTP_HOST_PORTDEV=8001
HTTPS_HOST_PORT=443
MARIA_HOST_PORT=3307
```

##### 1.3 Personnalisation du fichier _vhost.conf_
Pour finaliser la configuration de la stack, pensez à adapter le fichier _"vhosts.cnf"_ situé dans le dossier "php". Vous devez modifier les lignes suivantes par votre nom de projet.

Ligne 4
```bash
DocumentRoot /var/www/html/soeurmarie_sf5/public
```
Ligne 7
```bash
<Directory /var/www/html/soeurmarie_sf5/public>
```
Ligne24
```bash
<Directory /var/www/html/soeurmarie_sf5/public/bundles>
```


#### 1.4. Docker-compose
Lancer docker-compose avec la commande suivante.  
La commande _build_ va construire l'image de votre serveur www contenant apache, php8, composer, symfony et yarn. Ainsi que l'ensemble des extensions nécessaire à _PHP_ et les _frameworks_ actuels.

```bash
  docker-compose build
  docker-compose up -d
```

### PROCEDURE DE DEPLOIEMENT

#### II. déploiement du projet

##### II. A déploiement d'un nouveau projet Symfony pour développement
Connectez-vous au terminal du conteneur PHP, à partir de ce point, toutes les commandes se feront depuis le terminal du conteneur en root.  
**Attention** : utilisez le nom du conteneur approprié selon votre projet.

```bash
  docker exec -it "nom_conteneur_www" bash
```

Utilisez la commande du _CLI Symfony_ pour créer votre nouveau projet. 
**Attention** : donner un nom différent à votre projet sans les guillements.

```bash
  symfony new "nouveau_projet" --full
```

> Cette partie n'a pas encore été testée concernant le serveur interne à symfony.  

```
  cd new-project
  symfony serve -d
```

Pour assurer votre développement futur depuis un IDE, il vous faut créer un compte _user_ dans votre conteneur. Ce dernier sera identique à votre session Linux et et nous lui donnerons les droits d'accès dans ce conteneur.

```bash
adduser username
chown username:username -R .
```

*L'application devrait être accessible à cette adresse : [http://127.0.0.1](http://127.0.0.1)*

##### II. B déploiement d'un projet Symfony existant depuis votre dépot Git.


##### Connexion de l'application à la basse de données. 
apr 

```yaml
  DATABASE_URL="postgresql://symfony:ChangeMe@database:5432/app?serverVersion=13&charset=utf8"
```

## Ready to use with

This docker-compose provides you :

- PHP-8.0.13-cli (Debian)
    - Composer
    - Symfony CLI
    - and some other php extentions
    - nodejs, npm, yarn
- postgres:13-alpine
- mailcatcher


## Requirements

Out of the box, this docker-compose is designed for a Linux operating system, provide adaptations for a Mac or Windows environment.

- Linux (Ubuntu 20.04 or other)
- Docker
- Docker-compose
## Author
- xavier Burke - OpenPixl.fr    |     [Email](xavier.burke@openpixl.fr)  /  [Web](ww.openpixl.fr)
