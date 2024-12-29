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

3. **Error Handling**
   - [ ] Middleware de gestion d'erreurs
   - [ ] Retours utilisateur
   - [ ] Système de logs

### Phase 2: Activation Form

1. **Form Development**

   - [ ] Create form component
   - [ ] Implement client-side validation
   - [ ] Add AJAX submission

2. **Backend Processing**
   - [ ] Develop form processing logic
   - [ ] Implement Airtable update functionality
   - [ ] Add validation rules

### Phase 3: Confirmation & Polish

1. **Success Flow**

   - [ ] Implement success message display
   - [ ] Add personalization (name + motif)
   - [ ] Setup email notifications (if needed)

2. **Final Testing & Optimization**
   - [ ] Complete E2E testing
   - [ ] Performance optimization
   - [ ] Security audit

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

## Development Principles

### 1. Test-Driven Development (TDD)

- Write tests first (Red)
- Implement minimum code (Green)
- Refactor while keeping tests green (Blue)

### 2. Object-Oriented Programming (POO)

Key Classes:

- `CouponService`
- `AirtableClient`
- `CouponController`

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

### [Unreleased]

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
