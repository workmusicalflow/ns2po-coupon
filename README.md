# Système de Gestion des Coupons Cadeaux

## Contexte

L'entreprise souhaite offrir des coupons cadeaux permettant l'impression de 100 cartes de visite. Cette application web permet de gérer la validation et l'activation de ces coupons.

## Fonctionnalités

### 1. Vérification du Coupon

- Saisie du numéro de coupon (recordId)
- Vérification en temps réel dans la base Airtable
- Validation de la disponibilité (champ email non rempli)

### 2. Activation du Coupon

Formulaire de saisie comprenant :

- Nom
- Téléphone
- Email
- Entreprise

### 3. Confirmation

- Message personnalisé avec le nom du client
- Affichage du motif du coupon (ex: "100 cartes de visite")

## Spécifications Techniques

### Frontend

- HTML
- CSS (Tailwind)
- JavaScript (Vanilla)

### Backend

- PHP
- Base de données : Airtable

### Gestion des Erreurs

- Middleware de gestion d'erreurs centralisé
- Système de logs journaliers
- Retours utilisateur adaptés (développement/production)
- Niveaux de logs : ERROR, INFO, WARNING, DEBUG

Les logs sont stockés dans le dossier `/logs` avec un fichier par jour au format `YYYY-MM-DD.log`.

#### Niveaux de Logs

- ERROR : Erreurs critiques nécessitant une attention immédiate
- WARNING : Avertissements importants mais non critiques
- INFO : Informations générales sur le fonctionnement
- DEBUG : Détails techniques (uniquement en développement)

## Sécurité

- Validation des données côté serveur
- Protection des clés d'API
- Gestion sécurisée des accès à Airtable

## Installation

1. Cloner le projet

```bash
git clone [url-du-projet]
cd ns2po-coupon
```

2. Installer les dépendances

```bash
composer install
npm install
```

3. Configuration de l'environnement

```bash
# Copier le fichier d'exemple
cp .env.example .env

# Éditer le fichier .env avec vos identifiants Airtable
AIRTABLE_API_KEY=votre_clé_api
AIRTABLE_BASE_ID=votre_base_id
AIRTABLE_TABLE_NAME=votre_table
```

## Documentation Technique

Pour plus de détails sur l'architecture, les principes de développement et la documentation technique, consultez le [DEVBOOK.md](DEVBOOK.md).
