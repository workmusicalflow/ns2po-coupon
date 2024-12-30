# Development Guide & Technical Documentation

## Table of Contents

1. [Implementation Plan](#implementation-plan)
2. [Architecture Overview](#architecture-overview)
3. [Technical Stack](#technical-stack)
4. [Development Principles](#development-principles)
5. [Project Structure](#project-structure)
6. [API Integration](#api-integration)
7. [Testing Strategy](#testing-strategy)
8. [Changelog](#changelog)

## Implementation Plan

### Phase 1: Foundation & Coupon Validation

1. **Project Setup** (Completed)

   - [x] Initialize project structure
   - [x] Setup development environment
   - [x] Configure PHP development server
   - [x] Create initial HTML template with Tailwind

2. **Coupon Validation Feature** (Current Step)

   - [x] Créer les tests pour CouponService
   - [x] Implémenter la validation des codes coupon
   - [x] Implémenter la connexion Airtable
   - [x] Développer l'endpoint de validation
   - [x] Intégrer la validation frontend

3. **Error Handling** ✓
   - [x] Middleware de gestion d'erreurs
   - [x] Retours utilisateur
   - [x] Système de logs

### Phase 2: Activation Form ✓

1. **Form Development**

   - [x] Create form component
   - [x] Implement client-side validation
   - [x] Add AJAX submission

2. **Backend Processing**
   - [x] Develop form processing logic
   - [x] Implement Airtable update functionality
   - [x] Add validation rules

### Phase 3: Confirmation & Polish ✓

1. **Success Flow**

   - [x] Implement success message display with checkmark icon
   - [x] Add personalization (name + motif)
   - [x] Setup email notifications with PHPMailer

2. **Final Testing & Optimization**
   - [x] Complete E2E testing readiness
   - [x] Performance optimization
   - [x] Security audit and improvements

### Phase 4: Production Readiness

1. **Deployment Preparation**

   - [x] Server configuration documentation
         Configuration requise pour le serveur de production :

   ```
   - PHP 8.1+
   - Extension PHP : curl, json, mbstring
   - Composer
   - Node.js & NPM (pour Tailwind)
   - SSL/TLS activé
   ```

   - [x] Production environment setup guide
         Étapes de déploiement :

   ```
   1. Configurer le vhost Apache/Nginx pour pointer vers /ns2po-coupon
   2. Créer le fichier .env avec les variables de production
   3. Installer les dépendances : composer install --no-dev
   4. Compiler les assets : npm run build
   5. Configurer les permissions :
      - logs/ : 755
      - .env : 644
   ```

   - [x] SSL/TLS configuration

   ```apache
   <VirtualHost *:443>
       ServerName topdigitalevel.site
       DocumentRoot /var/www/html/ns2po-coupon

       SSLEngine on
       SSLCertificateFile /path/to/cert.pem
       SSLCertificateKeyFile /path/to/key.pem

       <Directory /var/www/html/ns2po-coupon>
           Options -Indexes +FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog ${APACHE_LOG_DIR}/ns2po-coupon-error.log
       CustomLog ${APACHE_LOG_DIR}/ns2po-coupon-access.log combined
   </VirtualHost>
   ```

   - [x] Backup strategy

   ```
   1. Sauvegardes quotidiennes des logs
   2. Backup hebdomadaire de la configuration
   3. Versionning Git pour le code
   4. Export Airtable hebdomadaire
   ```

2. **Monitoring & Maintenance**

   - [x] Setup monitoring tools

   ```
   1. Monitoring des logs d'erreur
   2. Surveillance des performances PHP
   3. Monitoring Airtable API quota
   4. Alertes email pour erreurs critiques
   ```

   - [x] Define maintenance procedures

   ```
   1. Vérification quotidienne des logs
   2. Rotation hebdomadaire des logs
   3. Mise à jour mensuelle des dépendances
   4. Test trimestriel de restauration backup
   ```

   - [x] Create incident response plan

   ```
   1. Détection : Monitoring + Alertes
   2. Classification : Critique/Majeur/Mineur
   3. Résolution selon type d'incident
   4. Post-mortem et documentation
   ```

   - [x] Document troubleshooting guides

   ```
   1. Erreurs API Airtable
   2. Problèmes d'envoi email
   3. Erreurs de validation
   4. Problèmes de performance
   ```

3. **Documentation**

   - [x] API documentation
         Endpoints :

   ```
   POST /api/validate-coupon
   POST /api/activate-coupon
   ```

   - [x] User manual
         Guide utilisateur dans /docs/user-guide.md

   - [x] Admin guide
         Guide admin dans /docs/admin-guide.md

   - [x] Deployment checklist

   ```
   1. Vérifier configuration serveur
   2. Tester en préproduction
   3. Backup pré-déploiement
   4. Déploiement avec zero-downtime
   5. Tests post-déploiement
   6. Monitoring 24h
   ```

## Architecture Overview

### MVC Architecture

```
Frontend (HTML/CSS/JS) ⟷ Controller ⟷ Model (Airtable API)
```

- **Model**: Handles Airtable interactions (read/write)
- **View**: HTML/Tailwind pages, conditional rendering
- **Controller**: Business logic orchestration

## Technical Stack

### Frontend

- HTML5
- CSS (Tailwind)
- Vanilla JavaScript

### Backend

- PHP (No framework)
- Airtable API
- Error Handling Middleware
- Logging System
- PHPMailer for email notifications

### Error Handling & Logging

#### Error Handler Middleware

- Centralise la gestion des erreurs
- Adapte les messages selon l'environnement (dev/prod)
- Gère les exceptions PHP et les erreurs personnalisées
- Journalise automatiquement les erreurs

#### Système de Logs

Niveaux de logs disponibles :

- **ERROR** : Erreurs critiques nécessitant une intervention
- **WARNING** : Problèmes non-critiques à surveiller
- **INFO** : Événements normaux du système
- **DEBUG** : Informations détaillées (dev uniquement)

Format des logs :

```
[YYYY-MM-DD HH:mm:ss] LEVEL: Message {Context JSON}
```

Stockage :

- Fichiers journaliers dans `/logs/YYYY-MM-DD.log`
- Rotation automatique des fichiers
- Exclus du contrôle de version

## Development Principles

### 1. Test-Driven Development (TDD)

- Write tests first (Red)
- Implement minimum code (Green)
- Refactor while keeping tests green (Blue)

### 2. Object-Oriented Programming (POO)

Key Classes:

- `CouponService`: Handles coupon validation logic
- `AirtableRepository`: Manages Airtable data operations
- `CouponController`: Orchestrates the application flow
- `EmailService`: Handles email notifications
- `Logger`: Manages system logging

### 3. SOLID Principles

- **S**ingle Responsibility: Each class has one job
- **O**pen/Closed: Extend, don't modify
- **L**iskov Substitution: Proper inheritance
- **I**nterface Segregation: Specific interfaces
- **D**ependency Inversion: Depend on abstractions

### 4. Clean Code Guidelines

- One intention per line
- Clear naming conventions
- Minimal code duplication
- Proper documentation
- Small, focused functions

## Project Structure

```
src/
├── Controllers/
│   └── CouponController.php
├── Models/
│   ├── Coupon.php
│   └── AirtableRepository.php
├── Services/
│   └── CouponService.php
├── Views/
│   ├── index.html
│   └── components/
tests/
├── Unit/
├── Integration/
└── E2E/
```

## API Integration

### Airtable Schema

```json
{
  "recordId": "string (17 caractères, commence par 'rec')",
  "motif": "string",
  "email": "string",
  "nom": "string",
  "telephone": "string",
  "entreprise": "string",
  "dateActivation": "datetime"
}
```

### Format des Codes Coupon

- Format complet Airtable: 17 caractères (ex: "recDa96prat6HTvLB")

  - Préfixe: "rec" (3 caractères fixes)
  - Corps: caractères variables (9 caractères)
  - Suffixe: 5 derniers caractères (utilisés pour la validation)

- Format utilisateur: 5 derniers caractères
  - Exemple: Si le code complet est "recDa96prat6HTvLB", l'utilisateur entre "HTvLB"
  - Améliore l'expérience utilisateur tout en maintenant la sécurité
  - Validation alphanumérique uniquement

### Stratégie de Validation

La validation des coupons se fait en plusieurs étapes:

1. Validation du format (5 caractères alphanumériques)
2. Vérification de l'existence dans Airtable (recherche par les 5 derniers caractères)
3. Vérification de la disponibilité (non utilisé)

## Testing Strategy

### Unit Tests

- Coupon validation logic
- Form data validation
- Service layer methods

### Integration Tests

- Airtable API communication
- Complete workflow testing

### E2E Tests

- User journey testing
- UI/UX validation

## Changelog

### [1.0.0] - Phase 3 Complete

- Enhanced success message display with visual feedback
- Added personalized success messages with user's name and motif
- Implemented email notification system using PHPMailer
- Added comprehensive email templates
- Enhanced security with proper input validation
- Implemented SMTP security with TLS
- Added environment configuration for email settings
- Improved error handling and user feedback
- Updated documentation with new features and components

### [0.2.0] - Phase 2 Complete

- Implemented form processing logic
- Added Airtable update functionality
- Implemented validation rules
- Created form component with client-side validation
- Added AJAX submission handling

### [0.1.0] - Phase 1 Complete

- Implemented error handling middleware
- Added comprehensive logging system
- Enhanced error reporting and user feedback
- Added environment-aware error messages
- Added implementation plan
- Initial project setup
- Basic documentation structure
- Created project structure with MVC architecture
- Set up Tailwind CSS with initial template
- Added coupon validation form interface
- Configured PHP development server with basic routing
- Implemented security headers and error reporting
- Created CouponService test cases following TDD principles
- Implemented simplified coupon validation using last 5 characters
- Updated validation logic to match Airtable record ID format
- Implemented Airtable connection with TDD approach
- Added AirtableRepository for coupon management
- Configured environment variables handling
