# 🎮 Guide de Traduction des Controllers et Entities Symfony

## 🎯 Mission de l'Agent IA

Analyser tous les fichiers `Controller` et `Entity` PHP de l'application, détecter les messages flash, les exceptions, les messages de validation, et créer les clés de traduction appropriées.

---

## 📋 Partie 1 : CONTROLLERS

### 🔍 Éléments à Traduire dans les Controllers

#### 1. **Messages Flash** (addFlash)

**❌ TEXTES EN DUR:**
```php
// Messages de succès
$this->addFlash('success', 'Utilisateur créé avec succès');
$this->addFlash('success', 'Produit modifié avec succès');
$this->addFlash('success', 'Élément supprimé avec succès');

// Messages d'erreur
$this->addFlash('error', 'Une erreur s\'est produite');
$this->addFlash('error', 'Élément non trouvé');
$this->addFlash('danger', 'Vous n\'avez pas les permissions nécessaires');

// Messages d'information
$this->addFlash('info', 'Aucune modification effectuée');
$this->addFlash('warning', 'Attention, cette action est irréversible');
```

**✅ AVEC TRADUCTION:**
```php
// Messages de succès
$this->addFlash('success', $translator->trans('messages.success.created'));
$this->addFlash('success', $translator->trans('messages.success.updated'));
$this->addFlash('success', $translator->trans('messages.success.deleted'));

// Messages d'erreur
$this->addFlash('error', $translator->trans('messages.error.general'));
$this->addFlash('error', $translator->trans('messages.error.not_found'));
$this->addFlash('danger', $translator->trans('messages.error.permission_denied'));

// Messages d'information
$this->addFlash('info', $translator->trans('messages.info.no_changes'));
$this->addFlash('warning', $translator->trans('messages.warning.irreversible'));
```

#### 2. **Exceptions et Messages d'erreur**

**❌ TEXTES EN DUR:**
```php
throw new \Exception('Utilisateur non trouvé');
throw new AccessDeniedException('Accès refusé');
throw new NotFoundHttpException('La page demandée n\'existe pas');
```

**✅ AVEC TRADUCTION:**
```php
throw new \Exception($translator->trans('exception.user_not_found'));
throw new AccessDeniedException($translator->trans('exception.access_denied'));
throw new NotFoundHttpException($translator->trans('exception.page_not_found'));
```

#### 3. **Titres de pages et métadonnées**

**❌ TEXTES EN DUR:**
```php
return $this->render('user/show.html.twig', [
    'user' => $user,
    'page_title' => 'Détails de l\'utilisateur'
]);
```

**✅ AVEC TRADUCTION:**
```php
return $this->render('user/show.html.twig', [
    'user' => $user,
    'page_title' => $translator->trans('user.show.title')
]);
```

---

### 🎨 Convention de Nommage pour Controllers

```yaml
# Messages de succès/erreur/info
messages:
  success:
    created: "Élément créé avec succès"
    updated: "Élément mis à jour avec succès"
    deleted: "Élément supprimé avec succès"
    saved: "Enregistrement réussi"
  error:
    general: "Une erreur s'est produite"
    not_found: "Élément non trouvé"
    validation: "Veuillez vérifier les données saisies"
    permission_denied: "Vous n'avez pas les permissions nécessaires"
    database: "Erreur de base de données"
  info:
    no_changes: "Aucune modification effectuée"
    already_exists: "Cet élément existe déjà"
  warning:
    irreversible: "Attention, cette action est irréversible"
    confirm_action: "Veuillez confirmer cette action"

# Messages spécifiques par entité
user:
  messages:
    created: "Utilisateur créé avec succès"
    updated: "Utilisateur modifié avec succès"
    deleted: "Utilisateur supprimé avec succès"
    not_found: "Utilisateur non trouvé"
    email_exists: "Cet email est déjà utilisé"

product:
  messages:
    created: "Produit créé avec succès"
    updated: "Produit modifié avec succès"
    deleted: "Produit supprimé avec succès"
    not_found: "Produit non trouvé"
    image_uploaded: "Image uploadée avec succès"
    image_error: "Erreur lors de l'upload de l'image"

# Exceptions
exception:
  user_not_found: "Utilisateur non trouvé"
  access_denied: "Accès refusé"
  page_not_found: "La page demandée n'existe pas"
  invalid_token: "Token CSRF invalide"
  database_error: "Erreur de connexion à la base de données"
```

---

### 🔄 Exemple Complet de Controller

**AVANT (avec textes en dur):**
```php
<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('app_user_index');
    }
}
```

**APRÈS (avec traductions):**
```php
<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {}

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('user.messages.created'));

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('user.messages.updated'));

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('user.messages.deleted'));
        } else {
            $this->addFlash('error', $this->translator->trans('exception.invalid_token'));
        }

        return $this->redirectToRoute('app_user_index');
    }
}
```

---

## 📋 Partie 2 : ENTITIES

### 🔍 Éléments à Traduire dans les Entities

#### 1. **Messages de Validation (Constraints)**

**❌ TEXTES EN DUR:**
```php
use Symfony\Component\Validator\Constraints as Assert;

class User
{
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $name = null;

    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    private ?string $email = null;

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères'
    )]
    private ?string $password = null;
}
```

**✅ AVEC TRADUCTION:**
```php
use Symfony\Component\Validator\Constraints as Assert;

class User
{
    #[Assert\NotBlank(message: 'validation.user.name.not_blank')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'validation.user.name.min_length',
        maxMessage: 'validation.user.name.max_length'
    )]
    private ?string $name = null;

    #[Assert\NotBlank(message: 'validation.user.email.not_blank')]
    #[Assert\Email(message: 'validation.user.email.invalid')]
    private ?string $email = null;

    #[Assert\NotBlank(message: 'validation.user.password.not_blank')]
    #[Assert\Length(
        min: 8,
        minMessage: 'validation.user.password.min_length'
    )]
    private ?string $password = null;
}
```

#### 2. **Contraintes Personnalisées**

**❌ TEXTES EN DUR:**
```php
#[Assert\Callback]
public function validate(ExecutionContextInterface $context): void
{
    if ($this->requiredQuantity < $this->currentQuantity) {
        $context->buildViolation('La quantité actuelle ne peut pas dépasser la quantité requise')
            ->atPath('currentQuantity')
            ->addViolation();
    }
}
```

**✅ AVEC TRADUCTION:**
```php
#[Assert\Callback]
public function validate(ExecutionContextInterface $context): void
{
    if ($this->requiredQuantity < $this->currentQuantity) {
        $context->buildViolation('validation.rack.current_exceeds_required')
            ->atPath('currentQuantity')
            ->addViolation();
    }
}
```

---

### 🎨 Convention de Nommage pour Entities

```yaml
# Validation générique
validation:
  not_blank: "Ce champ est obligatoire"
  invalid_email: "L'email {{ value }} n'est pas valide"
  min_length: "Ce champ doit contenir au moins {{ limit }} caractères"
  max_length: "Ce champ ne peut pas dépasser {{ limit }} caractères"
  invalid_format: "Format invalide"
  positive: "La valeur doit être positive"
  unique: "Cette valeur existe déjà"

# Validation spécifique par entité
validation:
  user:
    name:
      not_blank: "Le nom est obligatoire"
      min_length: "Le nom doit contenir au moins {{ limit }} caractères"
      max_length: "Le nom ne peut pas dépasser {{ limit }} caractères"
    email:
      not_blank: "L'email est obligatoire"
      invalid: "L'email {{ value }} n'est pas valide"
      unique: "Cet email est déjà utilisé"
    password:
      not_blank: "Le mot de passe est obligatoire"
      min_length: "Le mot de passe doit contenir au moins {{ limit }} caractères"
      weak: "Le mot de passe est trop faible"
    role:
      not_blank: "Le rôle est obligatoire"
      invalid: "Rôle invalide"
  
  product:
    name:
      not_blank: "Le nom du produit est obligatoire"
      min_length: "Le nom doit contenir au moins {{ limit }} caractères"
      unique: "Un produit avec ce nom existe déjà"
    year_edition:
      not_blank: "L'année d'édition est obligatoire"
      invalid: "Année invalide"
      future: "L'année ne peut pas être dans le futur"
    image:
      invalid_type: "Type de fichier invalide. Formats acceptés: JPG, PNG, WEBP"
      too_large: "L'image est trop volumineuse. Taille maximum: {{ limit }}MB"
  
  rack:
    name:
      not_blank: "Le nom du rack est obligatoire"
    required_quantity:
      not_blank: "La quantité requise est obligatoire"
      positive: "La quantité doit être positive"
    current_quantity:
      positive: "La quantité doit être positive"
      exceeds_required: "La quantité actuelle ne peut pas dépasser la quantité requise"
    position:
      not_blank: "La position est obligatoire"
      positive: "La position doit être positive"
  
  distribution:
    quantity:
      not_blank: "La quantité est obligatoire"
      positive: "La quantité doit être positive"
    status:
      not_blank: "Le statut est obligatoire"
      invalid: "Statut invalide"
    user:
      not_blank: "Le livreur est obligatoire"
    product:
      not_blank: "Le produit est obligatoire"
  
  intervention:
    quantity_added:
      not_blank: "La quantité ajoutée est obligatoire"
      positive: "La quantité doit être positive"
    distribution:
      not_blank: "La distribution est obligatoire"
    rack:
      not_blank: "Le rack est obligatoire"
```

---

### 🔄 Exemple Complet d'Entity

**AVANT (avec textes en dur):**
```php
<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'L\'année doit être positive')]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'L\'année ne peut pas être dans le futur'
    )]
    private ?int $yearEdition = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(
        max: 10,
        maxMessage: 'Le code langue ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $language = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $variant = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters et Setters...
}
```

**APRÈS (avec traductions):**
```php
<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'validation.product.name.not_blank')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'validation.product.name.min_length',
        maxMessage: 'validation.product.name.max_length'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'validation.product.year_edition.positive')]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'validation.product.year_edition.future'
    )]
    private ?int $yearEdition = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(
        max: 10,
        maxMessage: 'validation.product.language.max_length'
    )]
    private ?string $language = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $variant = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters et Setters...
}
```

---

## 📂 Liste des Fichiers à Traiter

### Controllers à analyser

- [ ] `src/Controller/DashboardController.php`
- [ ] `src/Controller/UserController.php`
- [ ] `src/Controller/ProductController.php`
- [ ] `src/Controller/WarehouseController.php`
- [ ] `src/Controller/HotelController.php`
- [ ] `src/Controller/DisplayController.php`
- [ ] `src/Controller/RackController.php`
- [ ] `src/Controller/DistributionController.php`
- [ ] `src/Controller/InterventionController.php`
- [ ] `src/Controller/StockController.php`

### Entities à analyser

- [ ] `src/Entity/User.php`
- [ ] `src/Entity/Product.php`
- [ ] `src/Entity/Warehouse.php`
- [ ] `src/Entity/Hotel.php`
- [ ] `src/Entity/Display.php`
- [ ] `src/Entity/Rack.php`
- [ ] `src/Entity/Distribution.php`
- [ ] `src/Entity/Intervention.php`
- [ ] `src/Entity/Stock.php`

---

## 📝 Format de Sortie Requis

### Pour chaque Controller analysé

```markdown
## Fichier: src/Controller/UserController.php

### 🔍 Messages Flash détectés

**Méthode: new() (ligne 28)**
- Type: success
- Message: "Utilisateur créé avec succès"
- Clé: `user.messages.created`

**Méthode: edit() (ligne 45)**
- Type: success
- Message: "Utilisateur modifié avec succès"
- Clé: `user.messages.updated`

**Méthode: delete() (ligne 62)**
- Type: success
- Message: "Utilisateur supprimé avec succès"
- Clé: `user.messages.deleted`

**Méthode: delete() (ligne 65)**
- Type: error
- Message: "Token CSRF invalide"
- Clé: `exception.invalid_token`

### 🔧 Modifications du Controller

1. **Ajouter l'injection de TranslatorInterface:**
```php
public function __construct(
    private TranslatorInterface $translator
) {}
```

2. **Remplacer les messages:**

**AVANT (ligne 28):**
```php
$this->addFlash('success', 'Utilisateur créé avec succès');
```

**APRÈS (ligne 28):**
```php
$this->addFlash('success', $this->translator->trans('user.messages.created'));
```

**AVANT (ligne 45):**
```php
$this->addFlash('success', 'Utilisateur modifié avec succès');
```

**APRÈS (ligne 45):**
```php
$this->addFlash('success', $this->translator->trans('user.messages.updated'));
```

### 📚 Clés de traduction

**Français (messages.fr.yaml):**
```yaml
user:
  messages:
    created: "Utilisateur créé avec succès"
    updated: "Utilisateur modifié avec succès"
    deleted: "Utilisateur supprimé avec succès"

exception:
  invalid_token: "Token CSRF invalide"
```

**Anglais (messages.en.yaml):**
```yaml
user:
  messages:
    created: "User created successfully"
    updated: "User updated successfully"
    deleted: "User deleted successfully"

exception:
  invalid_token: "Invalid CSRF token"
```
```

### Pour chaque Entity analysée

```markdown
## Fichier: src/Entity/Product.php

### 🔍 Contraintes de validation détectées

**Propriété: name (ligne 18)**
- NotBlank: "Le nom du produit est obligatoire"
- Length (min): "Le nom doit contenir au moins {{ limit }} caractères"
- Length (max): "Le nom ne peut pas dépasser {{ limit }} caractères"

**Propriété: yearEdition (ligne 28)**
- Positive: "L'année doit être positive"
- LessThanOrEqual: "L'année ne peut pas être dans le futur"

**Propriété: language (ligne 35)**
- Length (max): "Le code langue ne peut pas dépasser {{ limit }} caractères"

### 🔧 Code Entity Modifié

```php
<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Product
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'validation.product.name.not_blank')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'validation.product.name.min_length',
        maxMessage: 'validation.product.name.max_length'
    )]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'validation.product.year_edition.positive')]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'validation.product.year_edition.future'
    )]
    private ?int $yearEdition = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(
        max: 10,
        maxMessage: 'validation.product.language.max_length'
    )]
    private ?string $language = null;
}
```

### 📚 Clés de traduction

**Français (validators.fr.yaml):**
```yaml
validation:
  product:
    name:
      not_blank: "Le nom du produit est obligatoire"
      min_length: "Le nom doit contenir au moins {{ limit }} caractères"
      max_length: "Le nom ne peut pas dépasser {{ limit }} caractères"
    year_edition:
      positive: "L'année doit être positive"
      future: "L'année ne peut pas être dans le futur"
    language:
      max_length: "Le code langue ne peut pas dépasser {{ limit }} caractères"
```

**Anglais (validators.en.yaml):**
```yaml
validation:
  product:
    name:
      not_blank: "Product name is required"
      min_length: "Name must contain at least {{ limit }} characters"
      max_length: "Name cannot exceed {{ limit }} characters"
    year_edition:
      positive: "Year must be positive"
      future: "Year cannot be in the future"
    language:
      max_length: "Language code cannot exceed {{ limit }} characters"
```
```

---

## ✅ Checklist de Validation

### Pour les Controllers

- [ ] Tous les `addFlash()` utilisent le translator
- [ ] Toutes les exceptions utilisent des clés
- [ ] `TranslatorInterface` injecté dans le constructeur
- [ ] Imports corrects (`use Symfony\Contracts\Translation\TranslatorInterface;`)
- [ ] Clés créées dans `messages.fr.yaml` et `messages.en.yaml`
- [ ] Messages testés dans l'application

### Pour les Entities

- [ ] Toutes les contraintes utilisent des clés
- [ ] Les paramètres `{{ limit }}`, `{{ value }}` sont préservés
- [ ] Clés créées dans `validators.fr.yaml` et `validators.en.yaml`
- [ ] Validation testée avec des formulaires
- [ ] Messages d'erreur affichés correctement

---

## 🚀 Commandes de Test

```bash
# Tester les contraintes de validation
php bin/console debug:validator "App\Entity\Product"

# Vérifier les traductions de validation
php bin/console debug:translation fr validators
php bin/console debug:translation en validators

# Vérifier les traductions de messages
php bin/console debug:translation fr messages
php bin/console debug:translation en messages

# Vider le cache
php bin/console cache:clear

# Lancer les tests
php bin/phpunit
```

---

## 🎯 Résultat Final Attendu

### 1. Controllers modifiés
- Injection de `TranslatorInterface`
- Tous les messages flash traduits
- Toutes les exceptions traduites

### 2. Entities modifiées
- Toutes les contraintes de validation traduites
- Messages clairs et cohérents

### 3. Fichiers de traduction
- `translations/messages.fr.yaml` et `messages.en.yaml` pour les flash
- `translations/validators.fr.yaml` et `validators.en.yaml` pour la validation

### 4. Structure cohérente
```yaml
# messages.yaml
user:
  messages:
    created: "..."
    updated: "..."
    deleted: "..."

messages:
  success: { ... }
  error: { ... }
  
exception:
  invalid_token: "..."
  access_denied: "..."

# validators.yaml
validation:
  user:
    name: { not_blank, min_length,  }