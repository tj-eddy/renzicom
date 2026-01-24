# 🎨 Guide de Traduction des FormType Symfony

## 🎯 Mission de l'Agent IA

Analyser tous les fichiers `FormType` PHP de l'application, détecter les textes en dur dans les labels, placeholders et options, et créer les clés de traduction appropriées.

---

## 📋 Étape 1 : Comprendre la Structure FormType

### Structure typique d'un FormType

```php
// src/Form/UserType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom complet',                    // ❌ TEXTE EN DUR
                'attr' => [
                    'placeholder' => 'Jean Dupont'            // ❌ TEXTE EN DUR
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',                   // ❌ TEXTE EN DUR
                'help' => 'Format: utilisateur@domaine.com',  // ❌ TEXTE EN DUR
                'attr' => [
                    'placeholder' => 'exemple@email.com'      // ❌ TEXTE EN DUR
                ]
            ]);
    }
}
```

---

## 🔍 Étape 2 : Identifier les Textes à Traduire

### Éléments à traduire dans les FormType

#### 1. **Labels** (`label`)
```php
// ❌ AVANT
'label' => 'Nom du produit'

// ✅ APRÈS
'label' => 'product.form.name.label'
```

#### 2. **Placeholders** (`attr.placeholder`)
```php
// ❌ AVANT
'attr' => ['placeholder' => 'Entrez le nom']

// ✅ APRÈS
'attr' => ['placeholder' => 'product.form.name.placeholder']
```

#### 3. **Textes d'aide** (`help`)
```php
// ❌ AVANT
'help' => 'Ce champ est obligatoire'

// ✅ APRÈS
'help' => 'product.form.name.help'
```

#### 4. **Choix** (`choices`)
```php
// ❌ AVANT
'choices' => [
    'Administrateur' => 'admin',
    'Livreur' => 'driver',
    'Statistiques' => 'statistics'
]

// ✅ APRÈS
'choices' => [
    'role.admin' => 'admin',
    'role.driver' => 'driver',
    'role.statistics' => 'statistics'
]
```

#### 5. **Messages de contraintes** (à gérer séparément)
```php
// ❌ AVANT
new Assert\NotBlank(['message' => 'Le nom est obligatoire'])

// ✅ APRÈS
new Assert\NotBlank(['message' => 'validation.name.required'])
```

---

## 🎨 Étape 3 : Convention de Nommage pour FormType

### Structure des clés de traduction

```yaml
# Format: section.form.champ.type
section:        # Nom de l'entité (user, product, hotel, etc.)
  form:         # Toujours "form" pour les formulaires
    champ:      # Nom du champ (name, email, address, etc.)
      label: "Label du champ"
      placeholder: "Texte du placeholder"
      help: "Texte d'aide"
```

### Exemples de clés

```yaml
user:
  form:
    name:
      label: "Nom complet"
      placeholder: "Jean Dupont"
      help: "Entrez votre nom et prénom"
    email:
      label: "Adresse email"
      placeholder: "utilisateur@domaine.com"
      help: "Format: email valide"
    password:
      label: "Mot de passe"
      placeholder: "••••••••"
      help: "8 caractères minimum"
    role:
      label: "Rôle"
    is_active:
      label: "Compte actif"

product:
  form:
    name:
      label: "Nom du produit"
      placeholder: "Ex: Paris Match, Elle..."
      help: "Nom commercial du produit"
    image:
      label: "Image du produit"
      help: "Formats acceptés: JPG, PNG, WEBP. Taille max: 2MB"
    year_edition:
      label: "Année d'édition"
      placeholder: "2024"
    language:
      label: "Langue"
      placeholder: "FR, EN, DE..."
```

---

## 🔄 Étape 4 : Processus de Remplacement

### Pour chaque FormType :

#### 1. Analyser le fichier
```
Fichier: src/Form/UserType.php
Entité: User
Champs: name, email, password, role, isActive, avatar
```

#### 2. Extraire les textes en dur
```php
// Ligne 15
'label' => 'Nom complet'
'placeholder' => 'Jean Dupont'

// Ligne 20
'label' => 'Adresse email'
'placeholder' => 'utilisateur@domaine.com'
'help' => 'Format: email valide'

// Ligne 28
'label' => 'Mot de passe'

// Ligne 32
'label' => 'Rôle'
'choices' => [
    'Administrateur' => 'admin',
    'Livreur' => 'driver'
]
```

#### 3. Créer les clés de traduction

**Français (messages.fr.yaml):**
```yaml
user:
  form:
    name:
      label: "Nom complet"
      placeholder: "Jean Dupont"
    email:
      label: "Adresse email"
      placeholder: "utilisateur@domaine.com"
      help: "Format: email valide"
    password:
      label: "Mot de passe"
    role:
      label: "Rôle"

role:
  admin: "Administrateur"
  driver: "Livreur"
  statistics: "Statistiques"
```

**Anglais (messages.en.yaml):**
```yaml
user:
  form:
    name:
      label: "Full name"
      placeholder: "John Doe"
    email:
      label: "Email address"
      placeholder: "user@domain.com"
      help: "Format: valid email"
    password:
      label: "Password"
    role:
      label: "Role"

role:
  admin: "Administrator"
  driver: "Driver"
  statistics: "Statistics"
```

#### 4. Modifier le FormType

```php
<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'user.form.name.label',
                'attr' => [
                    'placeholder' => 'user.form.name.placeholder'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'user.form.email.label',
                'help' => 'user.form.email.help',
                'attr' => [
                    'placeholder' => 'user.form.email.placeholder'
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'user.form.password.label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'user.form.password.placeholder'
                ]
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'user.form.role.label',
                'choices' => [
                    'role.admin' => 'admin',
                    'role.driver' => 'driver',
                    'role.statistics' => 'statistics'
                ]
            ])
        ;
    }
}
```

---

## 📂 Étape 5 : Liste des FormType à Traiter

### FormType par module

#### ✅ Module User
- [ ] `src/Form/UserType.php`
  - Champs: name, email, password, role, isActive, avatar

#### ✅ Module Product
- [ ] `src/Form/ProductType.php`
  - Champs: name, image, yearEdition, language, variant

#### ✅ Module Warehouse
- [ ] `src/Form/WarehouseType.php`
  - Champs: name, address

#### ✅ Module Hotel
- [ ] `src/Form/HotelType.php`
  - Champs: name, address, contactName, contactEmail, contactPhone

#### ✅ Module Display
- [ ] `src/Form/DisplayType.php`
  - Champs: name, location, hotel

#### ✅ Module Rack
- [ ] `src/Form/RackType.php`
  - Champs: name, position, requiredQuantity, currentQuantity, display, product

#### ✅ Module Distribution
- [ ] `src/Form/DistributionType.php`
  - Champs: user, product, quantity, status, destination

#### ✅ Module Intervention
- [ ] `src/Form/InterventionType.php`
  - Champs: distribution, rack, quantityAdded, photoBefore, photoAfter, notes

#### ✅ Module Stock
- [ ] `src/Form/StockType.php`
  - Champs: warehouse, product, quantity, note

---

## 📝 Étape 6 : Format de Sortie Requis

### Pour chaque FormType analysé, fournir :

```markdown
## Fichier: src/Form/UserType.php

### 🔍 Textes en dur détectés

**Champ: name (ligne 15)**
- Label: "Nom complet"
- Placeholder: "Jean Dupont"

**Champ: email (ligne 20)**
- Label: "Adresse email"
- Placeholder: "utilisateur@domaine.com"
- Help: "Format: email valide"

**Champ: role (ligne 32)**
- Label: "Rôle"
- Choices:
  - "Administrateur" => 'admin'
  - "Livreur" => 'driver'
  - "Statistiques" => 'statistics'

### 🔧 Code PHP Modifié

```php
<?php
// src/Form/UserType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'user.form.name.label',
                'attr' => ['placeholder' => 'user.form.name.placeholder']
            ])
            ->add('email', EmailType::class, [
                'label' => 'user.form.email.label',
                'help' => 'user.form.email.help',
                'attr' => ['placeholder' => 'user.form.email.placeholder']
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'user.form.role.label',
                'choices' => [
                    'role.admin' => 'admin',
                    'role.driver' => 'driver',
                    'role.statistics' => 'statistics'
                ]
            ])
        ;
    }
}
```

### 📚 Clés de traduction

**Français (messages.fr.yaml):**
```yaml
user:
  form:
    name:
      label: "Nom complet"
      placeholder: "Jean Dupont"
    email:
      label: "Adresse email"
      placeholder: "utilisateur@domaine.com"
      help: "Format: email valide"
    role:
      label: "Rôle"

role:
  admin: "Administrateur"
  driver: "Livreur"
  statistics: "Statistiques"
```

**Anglais (messages.en.yaml):**
```yaml
user:
  form:
    name:
      label: "Full name"
      placeholder: "John Doe"
    email:
      label: "Email address"
      placeholder: "user@domain.com"
      help: "Format: valid email"
    role:
      label: "Role"

role:
  admin: "Administrator"
  driver: "Driver"
  statistics: "Statistics"
```
```

---

## 🎯 Exemples Complets par Type de Champ

### 1. Champ Texte Simple

**AVANT:**
```php
->add('name', TextType::class, [
    'label' => 'Nom du produit',
    'attr' => [
        'placeholder' => 'Ex: Paris Match'
    ]
])
```

**APRÈS:**
```php
->add('name', TextType::class, [
    'label' => 'product.form.name.label',
    'attr' => [
        'placeholder' => 'product.form.name.placeholder'
    ]
])
```

**TRADUCTIONS:**
```yaml
# FR
product:
  form:
    name:
      label: "Nom du produit"
      placeholder: "Ex: Paris Match"

# EN
product:
  form:
    name:
      label: "Product name"
      placeholder: "Ex: Paris Match"
```

### 2. Champ Email

**AVANT:**
```php
->add('email', EmailType::class, [
    'label' => 'Adresse email',
    'help' => 'Utilisez un email valide',
    'attr' => [
        'placeholder' => 'utilisateur@domaine.com'
    ]
])
```

**APRÈS:**
```php
->add('email', EmailType::class, [
    'label' => 'user.form.email.label',
    'help' => 'user.form.email.help',
    'attr' => [
        'placeholder' => 'user.form.email.placeholder'
    ]
])
```

**TRADUCTIONS:**
```yaml
# FR
user:
  form:
    email:
      label: "Adresse email"
      placeholder: "utilisateur@domaine.com"
      help: "Utilisez un email valide"

# EN
user:
  form:
    email:
      label: "Email address"
      placeholder: "user@domain.com"
      help: "Use a valid email"
```

### 3. Champ Textarea

**AVANT:**
```php
->add('notes', TextareaType::class, [
    'label' => 'Notes',
    'required' => false,
    'attr' => [
        'placeholder' => 'Observations et remarques...',
        'rows' => 4
    ]
])
```

**APRÈS:**
```php
->add('notes', TextareaType::class, [
    'label' => 'intervention.form.notes.label',
    'required' => false,
    'attr' => [
        'placeholder' => 'intervention.form.notes.placeholder',
        'rows' => 4
    ]
])
```

**TRADUCTIONS:**
```yaml
# FR
intervention:
  form:
    notes:
      label: "Notes"
      placeholder: "Observations et remarques..."

# EN
intervention:
  form:
    notes:
      label: "Notes"
      placeholder: "Observations and remarks..."
```

### 4. Champ Choice (EntityType)

**AVANT:**
```php
->add('hotel', EntityType::class, [
    'class' => Hotel::class,
    'choice_label' => 'name',
    'label' => 'Hôtel',
    'placeholder' => 'Sélectionner un hôtel'
])
```

**APRÈS:**
```php
->add('hotel', EntityType::class, [
    'class' => Hotel::class,
    'choice_label' => 'name',
    'label' => 'display.form.hotel.label',
    'placeholder' => 'display.form.hotel.placeholder'
])
```

**TRADUCTIONS:**
```yaml
# FR
display:
  form:
    hotel:
      label: "Hôtel"
      placeholder: "Sélectionner un hôtel"

# EN
display:
  form:
    hotel:
      label: "Hotel"
      placeholder: "Select a hotel"
```

### 5. Champ ChoiceType avec options

**AVANT:**
```php
->add('status', ChoiceType::class, [
    'label' => 'Statut',
    'choices' => [
        'En préparation' => 'preparing',
        'En cours' => 'in_progress',
        'Livrée' => 'delivered'
    ]
])
```

**APRÈS:**
```php
->add('status', ChoiceType::class, [
    'label' => 'distribution.form.status.label',
    'choices' => [
        'distribution.status.preparing' => 'preparing',
        'distribution.status.in_progress' => 'in_progress',
        'distribution.status.delivered' => 'delivered'
    ]
])
```

**TRADUCTIONS:**
```yaml
# FR
distribution:
  form:
    status:
      label: "Statut"
  status:
    preparing: "En préparation"
    in_progress: "En cours"
    delivered: "Livrée"

# EN
distribution:
  form:
    status:
      label: "Status"
  status:
    preparing: "Preparing"
    in_progress: "In progress"
    delivered: "Delivered"
```

### 6. Champ FileType

**AVANT:**
```php
->add('image', FileType::class, [
    'label' => 'Image du produit',
    'help' => 'Formats acceptés: JPG, PNG, WEBP. Taille max: 2MB',
    'required' => false,
    'mapped' => false
])
```

**APRÈS:**
```php
->add('image', FileType::class, [
    'label' => 'product.form.image.label',
    'help' => 'product.form.image.help',
    'required' => false,
    'mapped' => false
])
```

**TRADUCTIONS:**
```yaml
# FR
product:
  form:
    image:
      label: "Image du produit"
      help: "Formats acceptés: JPG, PNG, WEBP. Taille max: 2MB"

# EN
product:
  form:
    image:
      label: "Product image"
      help: "Accepted formats: JPG, PNG, WEBP. Max size: 2MB"
```

### 7. Champ IntegerType

**AVANT:**
```php
->add('quantity', IntegerType::class, [
    'label' => 'Quantité',
    'attr' => [
        'placeholder' => '100',
        'min' => 0
    ]
])
```

**APRÈS:**
```php
->add('quantity', IntegerType::class, [
    'label' => 'stock.form.quantity.label',
    'attr' => [
        'placeholder' => 'stock.form.quantity.placeholder',
        'min' => 0
    ]
])
```

**TRADUCTIONS:**
```yaml
# FR
stock:
  form:
    quantity:
      label: "Quantité"
      placeholder: "100"

# EN
stock:
  form:
    quantity:
      label: "Quantity"
      placeholder: "100"
```

### 8. Champ CheckboxType

**AVANT:**
```php
->add('isActive', CheckboxType::class, [
    'label' => 'Compte actif',
    'required' => false
])
```

**APRÈS:**
```php
->add('isActive', CheckboxType::class, [
    'label' => 'user.form.is_active.label',
    'required' => false
])
```

**TRADUCTIONS:**
```yaml
# FR
user:
  form:
    is_active:
      label: "Compte actif"

# EN
user:
  form:
    is_active:
      label: "Active account"
```

---

## ✅ Checklist de Validation

Pour chaque FormType traité :

- [ ] Tous les `label` sont remplacés par des clés
- [ ] Tous les `placeholder` sont remplacés par des clés
- [ ] Tous les `help` sont remplacés par des clés
- [ ] Tous les `choices` utilisent des clés de traduction
- [ ] Les clés suivent la convention de nommage
- [ ] Traductions FR créées pour toutes les clés
- [ ] Traductions EN créées pour toutes les clés
- [ ] Structure FR/EN identique
- [ ] Le FormType compile sans erreur
- [ ] Les formulaires s'affichent correctement

---

## 🚀 Commandes de Test

```bash
# Tester un FormType spécifique
php bin/console debug:form "App\Form\UserType"

# Lister tous les FormType de l'application
php bin/console debug:container --tag=form.type

# Vérifier les traductions
php bin/console debug:translation fr
php bin/console debug:translation en

# Vider le cache
php bin/console cache:clear
```

---

## 🎯 Résultat Final Attendu

### 1. FormType modifiés
Tous les fichiers PHP dans `src/Form/` sans texte en dur français

### 2. Fichiers de traduction mis à jour
`messages.fr.yaml` et `messages.en.yaml` avec les clés pour tous les formulaires

### 3. Structure cohérente
```yaml
user:
  form:
    name: { label, placeholder, help }
    email: { label, placeholder, help }
    
product:
  form:
    name: { label, placeholder }
    image: { label, help }
    
# etc...
```

### 4. Application fonctionnelle
Tous les formulaires affichent les labels traduits selon la locale

---

## 📊 Template de Rapport Final

```markdown
# Rapport de Migration des FormType

## Statistiques
- **FormType analysés**: 9
- **Champs traités**: X
- **Clés créées**: Y
- **Labels traduits**: Z
- **Placeholders traduits**: W
- **Textes d'aide traduits**: V

## FormType traités
✅ UserType (6 champs)
✅ ProductType (5 champs)
✅ WarehouseType (2 champs)
✅ HotelType (5 champs)
✅ DisplayType (3 champs)
✅ RackType (6 champs)
✅ DistributionType (5 champs)
✅ InterventionType (6 champs)
✅ StockType (4 champs)

## Validation
✅ Tous les FormType compilent
✅ Aucun texte en dur restant
✅ Traductions FR/EN synchronisées
✅ Formulaires fonctionnels
```

---

## 🎓 Conseils Pratiques

### 1. Réutilisation des clés communes
```yaml
# Au lieu de dupliquer
user.form.name.label: "Nom"
product.form.name.label: "Nom"
hotel.form.name.label: "Nom"

# Créer une clé générique
form:
  common:
    name:
      label: "Nom"

# Utiliser dans les FormType
'label' => 'form.common.name.label'
```

### 2. Gestion des champs optionnels
```php
// Indiquer si le champ est optionnel
->add('note', TextareaType::class, [
    'label' => 'stock.form.note.label',
    'required' => false,
    'attr' => [
        'placeholder' => 'stock.form.note.placeholder'
    ]
])
```

```yaml
stock:
  form:
    note:
      label: "Note (optionnel)"
      placeholder: "Informations complémentaires..."
```

### 3. Validation des traductions
Après modification, tester chaque formulaire :
- [ ] En français (fr)
- [ ] En anglais (en)
- [ ] Vérifier les placeholders
- [ ] Vérifier les textes d'aide
- [ ] Vérifier les choix déroulants

---

## 🔒 Règles Strictes

### ✅ À FAIRE
1. Traduire TOUS les textes visibles (label, placeholder, help)
2. Utiliser la convention `section.form.champ.type`
3. Créer les clés FR et EN simultanément
4. Tester chaque FormType après modification

### ❌ À NE PAS FAIRE
1. Laisser des textes en dur en français
2. Traduire les noms de champs (`'name'`, `'email'`)
3. Traduire les valeurs de choices (`'admin'`, `'driver'`)
4. Créer des clés sans équivalent EN
5. Modifier la logique métier du FormType

---

## 🎯 Objectif Final

**0 texte en dur dans les FormType**
**100% de labels/placeholders/help traduits**
**Formulaires multilingues fonctionnels**
**Structure cohérente et maintenable**