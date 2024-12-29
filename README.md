Afin de remercier ses clients entreprises, l'entreprise décide d'offrir des bons d'achat. Ces coupons sont en effet des bons d'impression pour 100 cartes de visite. Pour optimiser le suivi et la gestion de ces coupons, elle décide de créer une application web. L'application web répondra au scénario suivant :
1 - Vérification de la validité du coupon cadeau ;
Une fois sur l'application web, le client saisit le numéro de coupon (le recordId) pour vérifier sa validité ; le processus sera le suivant :
Il faudra vérifier que l'id_coupon (le recordId) existe dans la base de données « AIRTABLE » et que le champ « Email » du recordId est vide.

2 - Retour de la validité (existence du recordId) du coupon cadeau.

3- Activation du coupon par le remplissage d'un formulaire qui ne sera visible que lorsque le coupon aura été vérifié valide :
Le formulaire comprendra les éléments suivants : - Nom ;

- Téléphone ;
- Email ;
- Entreprise.

4- Une fois le formulaire rempli et soumis, recevoir un feedback « coupon cadeau activé » avec un message personnalisé du « nom » et de la référence « motif » (dans la table AIRTABLE, un champ prérempli pour le motif du coupon cadeaux, ex : 100 cartes de visite).

- Frontend : - Frontend : HTML, CSS, Tailwind, Vanilla JS.
- Backend :
  TDD - POO - MVC - SOLID - Clean Code (une intention par ligne de code).
  Écrire du code testable afin d'éviter la régression.
  Il faut éviter de créer des bugs à chaque implémentation, car cela occasionne beaucoup de temps pour les corriger.

1. Cahier des charges
   1.1. Contexte & Objectifs
1. Contexte :L’entreprise souhaite offrir, à titre de remerciement, des coupons cadeaux permettant l’impression de 100 cartes de visite. Pour gérer la distribution, la validation et l’activation de ces coupons, l’entreprise a besoin d’une application web facilitant :
   - La vérification en temps réel de la validité du coupon (via la base de données AIRTABLE).
   - L’activation du coupon après avoir rempli un formulaire (Nom, Téléphone, Email, Entreprise).
   - L’enregistrement final des informations relatives au coupon dans la base de données.
1. Objectifs :
   _ Proposer une interface simple, intuitive et responsive pour les utilisateurs.
   _ Assurer un suivi précis des coupons (validité, activation).
   _ Offrir un système de feedback clair lors de la saisie et la soumission (messages de confirmation).
   _ Mettre en place un back-end maintenable, testable et évolutif.
   1.2. Périmètre fonctionnel
1. Authentification ou accès direct :
   - Pas de compte client à créer, l’utilisateur atterrit directement sur la page de vérification du coupon.
   - Possibilité d’intégrer une section d’authentification pour l’équipe interne (administrateurs) si nécessaire (hors périmètre immédiat, mais à garder en tête).
1. Formulaire de vérification :
   - Champ de saisie du numéro de coupon (recordId).
   - Vérification de l’existence du recordId dans AIRTABLE et vérification que le champ “Email” (ou tout autre champ indiquant l’activation) est vide.
   - Retour visuel à l’utilisateur : « Coupon valide » ou « Coupon non valide ou déjà utilisé ».
1. Formulaire d’activation :
   - Visible uniquement si le coupon est valide.
   - Champs obligatoires : Nom, Téléphone, Email, Entreprise.
   - Règles de validation côté front (ex. vérifier format de l’email, champs requis, etc.).
   - Possibilité d’envoi d’un email de confirmation à l’utilisateur (optionnel, selon besoin).
1. Confirmation d’activation :
   - Message de succès : « Coupon cadeau activé », avec affichage du nom et de la référence motif issue de la base de données (ex. “100 cartes de visite”).
   - Enregistrement dans AIRTABLE (ou autre système de stockage) de toutes les informations du formulaire.
1. Gestion d’erreurs et retours :
   _ Gestion des erreurs de connexion à la base de données.
   _ Gestion des coupons non valides ou déjà utilisés. \* Feedback utilisateur clair (messages d’erreur, champs de formulaire mal renseignés, etc.).
   1.3. Périmètre technique
1. Front-End :
   - Technologies : HTML, CSS/Tailwind, JavaScript (Vanilla).
   - Interface :
     - Page de vérification du coupon.
     - Affichage conditionnel du formulaire d’activation.
     - Style responsive (optimisé pour mobile et desktop).
   - Validation côté front : Vérification des champs, retours d’erreur immédiats.
1. Back-End :
   - Architecture : MVC (Modèle, Vue, Contrôleur).
   - Principes : TDD (Test Driven Development), POO, SOLID, Clean Code.
   - Langage : PHP (sans framework)
   - Base de données : AIRTABLE (interaction via API Airtable).
1. Sécurité & performances :
   - Gestion sécurisée des accès à l’API AIRTABLE (Clé d’API, tokens, nom de la table d’enregistrement).
   - Implémentation de validations côté serveur pour éviter toute injection ou exploitation de failles.
   - Mesures d’optimisation de la performance (mise en cache éventuelle, requêtes asynchrones, etc.).

1.4. Architecture globale

1. Schéma conceptuel :
   - Front-End (HTML/CSS/Tailwind + JS) ⟷Contrôleur ⟷Modèle (communication via l’API Airtable).
   - Utilisation du pattern MVC pour séparer la logique métier (Modèle), la logique de présentation (Vue) et la logique de navigation/contrôle (Contrôleur).
2. Workflow simplifié :
   - L’utilisateur saisit le recordId dans le champ de formulaire.
   - Le contrôleur appelle le service (Modèle) pour vérifier l’existence et l’état du coupon.
   - Si valide, retour “Valide” -> affichage du formulaire d’activation.
   - L’utilisateur remplit et soumet le formulaire.
   - Le contrôleur enregistre les données via le Modèle (mise à jour de la table AIRTABLE).
   - Retour d’un message de confirmation à l’utilisateur : “Coupon cadeau activé”.

2.2. Expression de Besoin & User Stories
2.2.1. Principales User Stories

1. US1 - Vérification de coupon
   - En tant que client, je veux saisir mon numéro de coupon (recordId) afin de vérifier s’il est valide ou déjà utilisé.
2. US2 - Activation de coupon
   - En tant que client, je veux remplir un formulaire (Nom, Téléphone, Email, Entreprise) afin d’activer mon coupon valide.
3. US3 - Confirmation et message personnalisé
   - En tant que client, je veux recevoir un message confirmant l’activation de mon coupon, indiquant mon nom et la référence du motif (ex. “100 cartes de visite”).

2.3. Spécifications Techniques
2.3.1. Architecture logicielle

- Modèle (M) : Gère les interactions avec AIRTABLE (lecture/écriture).
- Vue (V) : Pages HTML/Tailwind, gère l’affichage conditionnel (coupon valide ou non, affichage du formulaire, etc.).
- Contrôleur (C) : Fait le lien entre la Vue et le Modèle, orchestre la logique métier (vérification coupon, mise à jour des champs).

  2.3.3. Gestion de la Base de Données (AIRTABLE)

- Champs requis dans la table :
  - recordId (identifiant unique – géré par Airtable ou custom).
  - Motif (ex. “100 cartes de visite”).
  - Email (vide tant que le coupon n’est pas activé).
  - Autres champs : Nom, Téléphone, Entreprise.
- Workflow : 1. Lecture du record via recordId. 2. Vérification de l’état (Email vide ou non). 3. Mise à jour des champs (Email, Nom, Téléphone, Entreprise, Date d’activation).
  2.3.4. Sécurité & Authentification
- Clés d’API : stockées dans des variables d’environnement .env (non commitées dans Git).
- Middleware (Back-end) : Toute requête vers Airtable passe par un module de service sécurisé.
  2.4. Étapes de développement : TDD, POO, MVC, SOLID, Clean Code
  2.4.1. TDD (Test Driven Development)

1. Écriture du test (rouge) : On commence par écrire des tests pour vérifier la validité du coupon, l’activation, etc.
2. Implémentation minimale (vert) : On code juste ce qu’il faut pour faire passer le test.
3. Refactoring (bleu) : On améliore la lisibilité, on applique SOLID et Clean Code tout en s’assurant que les tests restent au vert.

2.4.2. POO (Programmation Orientée Objet)

- Classes : CouponService, AirtableClient, CouponController.
- Méthodes : checkValidity(recordId), activateCoupon(recordId, userData), etc.
- Avantages : Organisation logique, réutilisabilité du code, clarté de l’architecture.
  2.4.3. MVC (Modèle-Vue-Contrôleur)
- Modèle : Coupon (entité) + AirtableRepository (pour la persistance).
- Vue : Pages HTML/Tailwind, rendues côté client.
- Contrôleur : CouponController, orchestre les requêtes HTTP, fait la liaison entre la vue (formulaires) et le modèle (Airtable).
  2.4.4. SOLID
- Single Responsibility Principle (SRP) : Chaque classe ou module a une seule responsabilité.
- Open/Closed Principle (OCP) : Les classes sont ouvertes à l’extension mais fermées à la modification.
- Liskov Substitution Principle (LSP) : Les sous-classes doivent être substituables à leurs super-classes.
- Interface Segregation Principle (ISP) : Préférer plusieurs interfaces spécifiques plutôt qu’une interface générale.
- Dependency Inversion Principle (DIP) : Dépendre d’abstractions plutôt que de concrétions (ex. injection de dépendances).
  2.4.5. Clean Code
- Lisibilité : Noms de fonctions explicites, commentaires quand nécessaire, indentation claire.
- Structure : Fichiers organisés (routes, contrôleurs, services, tests, etc.).
- Simplicité : Éviter la duplication de code, factoriser les parties communes.
  2.5. Mise en place des Tests (Unitaires, Intégration, E2E)

1. Tests Unitaires :
   - Vérifier la logique métier de chaque module (CouponService, par ex.).
   - Validation du recordId, vérification du champ Email dans la réponse.
2. Tests d’intégration :
   - Vérifier que l’on peut effectivement communiquer avec l’API Airtable et que les réponses sont correctement traitées.
   - S’assurer que le flux complet “Vérification -> Activation -> Confirmation” fonctionne.
3. Tests End-to-End (E2E) :
   - Simuler l’utilisation du formulaire côté front, soumission, réponse, etc.
   - Vérifier l’expérience utilisateur et la navigation.
