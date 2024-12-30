# Checklist de Déploiement

## Pré-déploiement

1. [ ] Vérifier la configuration serveur

   - [ ] PHP 8.1+ installé
   - [ ] Extensions PHP requises activées (curl, json, mbstring)
   - [ ] Composer installé
   - [ ] Node.js & NPM installés
   - [ ] SSL/TLS configuré

2. [ ] Préparer l'environnement

   - [ ] Créer le dossier /ns2po-coupon sur topdigitalevel.site
   - [ ] Configurer les permissions (755 pour les dossiers, 644 pour les fichiers)
   - [ ] Préparer le fichier .env de production
   - [ ] Configurer le vhost Apache/Nginx

3. [ ] Tests préliminaires
   - [ ] Exécuter les tests unitaires (phpunit)
   - [ ] Vérifier les tests d'intégration
   - [ ] Valider les tests E2E
   - [ ] Tester les emails en préproduction

## Déploiement

1. [ ] Backup

   - [ ] Créer une sauvegarde de la configuration existante
   - [ ] Exporter les données Airtable actuelles
   - [ ] Sauvegarder les logs existants

2. [ ] Installation

   ```bash
   # Dans /var/www/html/ns2po-coupon
   git clone [repository] .
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```

3. [ ] Configuration
   - [ ] Copier le .env de production
   - [ ] Vérifier les permissions :
     ```bash
     chmod 755 logs/
     chmod 644 .env
     chown -R www-data:www-data .
     ```
   - [ ] Configurer les logs
   - [ ] Activer le SSL avec Let's Encrypt

## Post-déploiement

1. [ ] Tests de validation

   - [ ] Vérifier l'accès HTTPS à https://topdigitalevel.site/ns2po-coupon
   - [ ] Tester la validation des coupons
   - [ ] Valider le processus d'activation
   - [ ] Confirmer l'envoi des emails
   - [ ] Vérifier les mises à jour Airtable

2. [ ] Monitoring

   - [ ] Vérifier les logs d'erreur dans /logs
   - [ ] Surveiller les performances avec New Relic
   - [ ] Valider les quotas API Airtable
   - [ ] Tester les alertes email

3. [ ] Documentation
   - [ ] Mettre à jour la documentation de production
   - [ ] Noter les changements de configuration
   - [ ] Documenter les problèmes rencontrés
   - [ ] Mettre à jour les guides utilisateur/admin

## Variables d'Environnement Production

```env
APP_ENV=production
APP_DEBUG=false
AIRTABLE_API_KEY=xxx
AIRTABLE_BASE_ID=xxx
AIRTABLE_TABLE_NAME=xxx
SMTP_HOST=xxx
SMTP_PORT=587
SMTP_USERNAME=xxx
SMTP_PASSWORD=xxx
SMTP_ENCRYPTION=tls
```

## Contacts d'urgence

- Support Technique : support@topdigitalevel.site
- Administrateur Système : admin@topdigitalevel.site
- Responsable Projet : project@topdigitalevel.site

## Procédure de Rollback

En cas de problème majeur :

1. Restaurer la dernière sauvegarde

   ```bash
   cd /var/www/html
   mv ns2po-coupon ns2po-coupon-probleme
   git clone [repository] ns2po-coupon -b last-stable-tag
   ```

2. Restaurer la configuration

   ```bash
   cp ns2po-coupon-probleme/.env ns2po-coupon/
   cp -r ns2po-coupon-probleme/logs ns2po-coupon/
   ```

3. Réinstaller les dépendances

   ```bash
   cd ns2po-coupon
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```

4. Vérifier les permissions

   ```bash
   chmod 755 logs/
   chmod 644 .env
   chown -R www-data:www-data .
   ```

5. Tester l'application
   - Vérifier l'accès HTTPS
   - Tester les fonctionnalités principales
   - Vérifier les logs
