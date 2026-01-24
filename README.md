# RenziCom - codeddy

## 📋 Description

Application web de gestion de magasin (magazine), stocks et distribution développée avec Symfony 7.4. Ce système permet une gestion précise des entrepôts, des racks, et du suivi des produits distribués notamment dans le secteur hôtelier.

## 🛠️ Fonctionnalités

- **Tableau de Bord** : Vue d'ensemble des indicateurs clés et des activités récentes.
- **Gestion des Produits** : Catalogue complet des articles avec gestion des caractéristiques techniques.
- **Suivi des Stocks** : Contrôle en temps réel des quantités, entrées et sorties par entrepôt.
- **Système de Distribution** : Organisation et suivi des livraisons vers les clients ou sites (Hôtels).
- **Logistique & Stockage** : Structuration hiérarchique des espaces de stockage (Entrepôts et Racks).
- **Registre d'Interventions** : Suivi détaillé des opérations de maintenance et d'interventions sur site.
- **Gestion Utilisateurs** : Système sécurisé de rôles et d'accès pour les administrateurs et gestionnaires.

## ⚙️ Prérequis

- PHP 8.2 ou supérieur
- Composer 2.x
- Base de données supportée par Doctrine (MySQL recommandé)
- Symfony CLI (optionnel)
- Node.js & npm (pour la gestion des assets via AssetMapper)

## 🚀 Installation

### 1. Cloner le repository

```bash
git clone https://github.com/codeddy/renzicom.git
cd renzicom
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configuration de l'environnement

Copier le fichier `.env` et configurer votre `.env.local` :

```bash
cp .env .env.local
```

Configurez votre `DATABASE_URL` et `MAILER_DSN` dans le fichier `.env.local`.

### 4. Initialiser la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Lancer le serveur

```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

L'application sera accessible sur `http://localhost:8000`

## 🛠️ Stack Technique

- **Framework** : Symfony 7.4
- **Templating** : Twig 3.x
- **Gestion des Assets** : Symfony AssetMapper & Stimulus (UX Turbo)
- **Base de données** : Doctrine ORM
- **Gestion d'Images** : VichUploaderBundle
- **UI/UX** : Vanilla CSS & JavaScript

## 👥 Auteur

- **codeddy** - *Développement et Conception*

## 📄 Licence

Ce projet est sous licence propriétaire.

---

**Version** : 1.0.0  
**Dernière mise à jour** : Janvier 2026
