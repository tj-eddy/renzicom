# 🤖 Guide d'Automatisation des Traductions Symfony

## 🎯 Mission de l'Agent IA

Analyser tous les fichiers Twig de l'application, détecter les textes en dur, les remplacer par des clés de traduction, et mettre à jour les fichiers de traduction FR/EN.

---

## 📋 Étape 1 : Analyse et Détection

### Règles de détection des textes en dur

**❌ TEXTES EN DUR À REMPLACER :**
```twig
{# Mauvais exemples #}
<h3>Tableau de bord</h3>
<button>Ajouter</button>
<th>Nom</th>
<label>Email</label>
<p>Aucune donnée disponible</p>
Êtes-vous sûr de vouloir supprimer ?
```

**✅ TEXTES TRADUITS (À CONSERVER) :**
```twig
{# Bons exemples #}
<h3>{{ 'dashboard.title'|trans }}</h3>
<button>{{ 'actions.add'|trans }}</button>
<th>{{ 'user.table.name'|trans }}</th>
<label>{{ 'user.form.email.label'|trans }}</label>
<p>{{ 'datatable.no_data'|trans }}</p>
{{ 'user.confirm_delete'|trans }}
```

### Patterns à ignorer (ne PAS traduire)

```twig
{# Variables Twig - NE PAS TOUCHER #}
{{ user.name }}
{{ product.id }}
{{ intervention.createdAt|date('d/m/Y') }}

{# Attributs HTML techniques - NE PAS TOUCHER #}
class="btn btn-primary"
id="myModal"
href="{{ path('app_user_index') }}"
style="width: 100%"

{# Valeurs numériques/symboles - NE PAS TOUCHER #}
{{ total_stock|number_format(0, ',', ' ') }}
%
#
```

---

## 📂 Étape 2 : Convention de Nommage des Clés

### Structure hiérarchique

```yaml
# Format: section.sous_section.element.type
section:           # Nom de la section (user, product, dashboard, etc.)
  sous_section:    # Type de contexte (list, form, table, actions)
    element:       # Élément spécifique (name, email, title, etc.)
      type:        # Type de contenu (label, placeholder, help, etc.)
```

### Exemples de clés par contexte

#### Navigation et Menus
```yaml
nav:
  dashboard: "Tableau de bord"
  users: "Utilisateurs"
  logout: "Déconnexion"
```

#### Listes et Tables
```yaml
user:
  list:
    title: "Liste des utilisateurs"
    no_users: "Aucun utilisateur trouvé"
  table:
    id: "ID"
    name: "Nom"
    email: "Email"
    actions: "Actions"
```

#### Formulaires
```yaml
user:
  form:
    name:
      label: "Nom"
      placeholder: "Nom complet"
      help: "Texte d'aide"
    email:
      label: "Email"
      placeholder: "adresse@email.com"
```

#### Actions et Boutons
```yaml
user:
  actions:
    new: "Nouvel utilisateur"
    edit: "Modifier l'utilisateur"
    delete: "Supprimer l'utilisateur"
    show: "Voir l'utilisateur"

actions:
  add: "Ajouter"
  save: "Enregistrer"
  cancel: "Annuler"
  confirm: "Confirmer"
```

#### Messages de confirmation
```yaml
user:
  confirm_delete: "Êtes-vous sûr de vouloir supprimer cet utilisateur ?"

actions:
  confirm_delete_title: "Confirmer la suppression"
```

---

## 🔄 Étape 3 : Processus de Remplacement

### Pour chaque fichier Twig analysé :

#### 1. Identifier le contexte
```
Fichier: templates/user/index.html.twig
Section: user
Contexte: list (liste), table (tableau)
```

#### 2. Extraire les textes en dur
```twig
AVANT:
<h3>Liste des utilisateurs</h3>
<button>Nouvel utilisateur</button>
<th>Nom</th>
<th>Email</th>
<td colspan="5">Aucun utilisateur trouvé</td>
```

#### 3. Créer les clés de traduction
```yaml
user:
  list:
    title: "Liste des utilisateurs"
  actions:
    new: "Nouvel utilisateur"
  table:
    name: "Nom"
    email: "Email"
    no_users: "Aucun utilisateur trouvé"
```

#### 4. Remplacer dans le Twig
```twig
APRÈS:
<h3>{{ 'user.list.title'|trans }}</h3>
<button>{{ 'user.actions.new'|trans }}</button>
<th>{{ 'user.table.name'|trans }}</th>
<th>{{ 'user.table.email'|trans }}</th>
<td colspan="5">{{ 'user.list.no_users'|trans }}</td>
```

---

## 📝 Étape 4 : Format de Sortie Requis

### Pour chaque fichier Twig analysé, fournir :

```markdown
## Fichier: templates/[chemin]/[fichier].html.twig

### 🔍 Textes en dur détectés
1. "Tableau de bord" (ligne 12)
2. "Ajouter" (ligne 45)
3. "Êtes-vous sûr ?" (ligne 78)

### 🔧 Modifications Twig

**AVANT (ligne 12):**
```twig
<h3>Tableau de bord</h3>
```

**APRÈS (ligne 12):**
```twig
<h3>{{ 'dashboard.title'|trans }}</h3>
```

---

**AVANT (ligne 45):**
```twig
<button>Ajouter</button>
```

**APRÈS (ligne 45):**
```twig
<button>{{ 'actions.add'|trans }}</button>
```

### 📚 Nouvelles clés de traduction

**Français (messages.fr.yaml):**
```yaml
dashboard:
  title: "Tableau de bord"

actions:
  add: "Ajouter"
```

**Anglais (messages.en.yaml):**
```yaml
dashboard:
  title: "Dashboard"

actions:
  add: "Add"
```
```

---

## 🗂️ Étape 5 : Fichiers à Analyser (par priorité)

### Priorité 1 - Pages principales
- [ ] `templates/base.html.twig`
- [ ] `templates/dashboard/index.html.twig`

### Priorité 2 - Module User
- [ ] `templates/user/index.html.twig`
- [ ] `templates/user/new.html.twig`
- [ ] `templates/user/edit.html.twig`
- [ ] `templates/user/show.html.twig`

### Priorité 3 - Module Product
- [ ] `templates/product/index.html.twig`
- [ ] `templates/product/new.html.twig`
- [ ] `templates/product/edit.html.twig`
- [ ] `templates/product/show.html.twig`

### Priorité 4 - Module Warehouse
- [ ] `templates/warehouse/index.html.twig`
- [ ] `templates/warehouse/new.html.twig`
- [ ] `templates/warehouse/edit.html.twig`
- [ ] `templates/warehouse/show.html.twig`

### Priorité 5 - Module Hotel
- [ ] `templates/hotel/index.html.twig`
- [ ] `templates/hotel/new.html.twig`
- [ ] `templates/hotel/edit.html.twig`
- [ ] `templates/hotel/show.html.twig`

### Priorité 6 - Module Display
- [ ] `templates/display/index.html.twig`
- [ ] `templates/display/new.html.twig`
- [ ] `templates/display/edit.html.twig`
- [ ] `templates/display/show.html.twig`

### Priorité 7 - Module Rack
- [ ] `templates/rack/index.html.twig`
- [ ] `templates/rack/new.html.twig`
- [ ] `templates/rack/edit.html.twig`
- [ ] `templates/rack/show.html.twig`

### Priorité 8 - Module Distribution
- [ ] `templates/distribution/index.html.twig`
- [ ] `templates/distribution/new.html.twig`
- [ ] `templates/distribution/edit.html.twig`
- [ ] `templates/distribution/show.html.twig`

### Priorité 9 - Module Intervention
- [ ] `templates/intervention/index.html.twig`
- [ ] `templates/intervention/new.html.twig`
- [ ] `templates/intervention/edit.html.twig`
- [ ] `templates/intervention/show.html.twig`

### Priorité 10 - Module Stock
- [ ] `templates/stock/index.html.twig`
- [ ] `templates/stock/new.html.twig`
- [ ] `templates/stock/edit.html.twig`
- [ ] `templates/stock/show.html.twig`

---

## ✅ Étape 6 : Checklist de Validation

Pour chaque fichier traité :

- [ ] Tous les textes en dur français sont remplacés
- [ ] Les clés suivent la convention de nommage
- [ ] Les variables Twig sont préservées
- [ ] Les attributs HTML techniques sont intacts
- [ ] Traduction FR ajoutée dans `messages.fr.yaml`
- [ ] Traduction EN ajoutée dans `messages.en.yaml`
- [ ] Les deux fichiers ont la même structure
- [ ] Aucune clé n'est dupliquée
- [ ] Les paramètres (`%count%`, `{start}`) sont préservés

---

## 🎯 Résultat Final Attendu

### 1. Fichiers Twig modifiés
Tous les fichiers `.twig` sans texte en dur, uniquement des clés `|trans`

### 2. Fichier de traduction FR complet
`translations/messages.fr.yaml` avec toutes les clés organisées

### 3. Fichier de traduction EN complet
`translations/messages.en.yaml` miroir exact du FR en anglais

### 4. Rapport de migration
Document listant tous les changements effectués

---

## 🚀 Commandes de Validation Post-Migration

```bash
# Vérifier la syntaxe YAML
php bin/console lint:yaml translations/

# Lister les traductions manquantes FR
php bin/console debug:translation fr --only-missing

# Lister les traductions manquantes EN
php bin/console debug:translation en --only-missing

# Vider le cache
php bin/console cache:clear

# Tester l'application
symfony server:start
```

---

## 📊 Template de Rapport Final

```markdown
# Rapport de Migration des Traductions

## Statistiques
- **Fichiers analysés**: X
- **Textes en dur détectés**: Y
- **Clés de traduction créées**: Z
- **Fichiers modifiés**: X

## Résumé par module
- User: X textes remplacés
- Product: X textes remplacés
- Dashboard: X textes remplacés
[...]

## Clés de traduction
- Total clés FR: X
- Total clés EN: X
- Clés communes: X
- Cohérence: 100%

## Validation
✅ Aucune traduction manquante
✅ Structure FR/EN identique
✅ Syntaxe YAML valide
✅ Application fonctionnelle
```

---

## 🎓 Exemples Concrets de Remplacement

### Exemple 1 : Page de liste

**AVANT:**
```twig
<div class="card-header">
    <h4>Liste des produits</h4>
    <a href="{{ path('app_product_new') }}" class="btn btn-primary">
        Nouveau produit
    </a>
</div>
```

**APRÈS:**
```twig
<div class="card-header">
    <h4>{{ 'product.list.title'|trans }}</h4>
    <a href="{{ path('app_product_new') }}" class="btn btn-primary">
        {{ 'product.actions.new'|trans }}
    </a>
</div>
```

**TRADUCTIONS:**
```yaml
# FR
product:
  list:
    title: "Liste des produits"
  actions:
    new: "Nouveau produit"

# EN
product:
  list:
    title: "Product list"
  actions:
    new: "New product"
```

### Exemple 2 : Tableau

**AVANT:**
```twig
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {% for user in users %}
            <tr>
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>Actions ici</td>
            </tr>
        {% else %}
            <tr>
                <td colspan="4">Aucun utilisateur trouvé</td>
            </tr>
        {% endfor %}
    </tbody>
</table>
```

**APRÈS:**
```twig
<table>
    <thead>
        <tr>
            <th>{{ 'user.table.id'|trans }}</th>
            <th>{{ 'user.table.name'|trans }}</th>
            <th>{{ 'user.table.email'|trans }}</th>
            <th>{{ 'user.table.actions'|trans }}</th>
        </tr>
    </thead>
    <tbody>
        {% for user in users %}
            <tr>
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{# Actions ici #}</td>
            </tr>
        {% else %}
            <tr>
                <td colspan="4">{{ 'user.list.no_users'|trans }}</td>
            </tr>
        {% endfor %}
    </tbody>
</table>
```

**TRADUCTIONS:**
```yaml
# FR
user:
  table:
    id: "ID"
    name: "Nom"
    email: "Email"
    actions: "Actions"
  list:
    no_users: "Aucun utilisateur trouvé"

# EN
user:
  table:
    id: "ID"
    name: "Name"
    email: "Email"
    actions: "Actions"
  list:
    no_users: "No users found"
```

### Exemple 3 : Formulaire

**AVANT:**
```twig
{{ form_start(form) }}
    <div class="form-group">
        {{ form_label(form.name, 'Nom du produit') }}
        {{ form_widget(form.name, {'attr': {'placeholder': 'Ex: Paris Match'}}) }}
        <small>Entrez le nom complet du produit</small>
    </div>
    
    <button type="submit">Enregistrer</button>
    <a href="{{ path('app_product_index') }}">Annuler</a>
{{ form_end(form) }}
```

**APRÈS:**
```twig
{{ form_start(form) }}
    <div class="form-group">
        {{ form_label(form.name, 'product.form.name.label'|trans) }}
        {{ form_widget(form.name, {'attr': {'placeholder': 'product.form.name.placeholder'|trans}}) }}
        <small>{{ 'product.form.name.help'|trans }}</small>
    </div>
    
    <button type="submit">{{ 'form.button.save'|trans }}</button>
    <a href="{{ path('app_product_index') }}">{{ 'form.button.cancel'|trans }}</a>
{{ form_end(form) }}
```

**TRADUCTIONS:**
```yaml
# FR
product:
  form:
    name:
      label: "Nom du produit"
      placeholder: "Ex: Paris Match"
      help: "Entrez le nom complet du produit"

form:
  button:
    save: "Enregistrer"
    cancel: "Annuler"

# EN
product:
  form:
    name:
      label: "Product name"
      placeholder: "Ex: Paris Match"
      help: "Enter the full product name"

form:
  button:
    save: "Save"
    cancel: "Cancel"
```

### Exemple 4 : Messages JavaScript

**AVANT:**
```twig
<script>
function confirmDelete(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
```

**APRÈS:**
```twig
<script>
function confirmDelete(id) {
    if (confirm('{{ 'actions.confirm_delete_message'|trans }}')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
```

**TRADUCTIONS:**
```yaml
# FR
actions:
  confirm_delete_message: "Êtes-vous sûr de vouloir supprimer cet élément ?"

# EN
actions:
  confirm_delete_message: "Are you sure you want to delete this item?"
```

---

## 🔒 Règles Strictes à Respecter

### ✅ À FAIRE
1. Remplacer TOUS les textes visibles en français
2. Utiliser la convention de nommage hiérarchique
3. Créer les clés FR ET EN simultanément
4. Préserver les variables Twig (`{{ user.name }}`)
5. Garder les attributs HTML techniques intacts
6. Utiliser des noms de clés explicites et cohérents

### ❌ À NE PAS FAIRE
1. Traduire les noms de variables Twig
2. Traduire les noms de classes CSS
3. Traduire les chemins de routes
4. Créer des clés FR sans équivalent EN
5. Utiliser des espaces dans les noms de clés
6. Dupliquer des clés avec des noms différents

---

## 📌 Points d'Attention Spécifiques

### Gestion des pluriels
```yaml
# Utiliser %count% pour les valeurs dynamiques
stats:
  total_products: "%count% produits différents"
  total_products: "%count% different products"
```

### Gestion des paramètres
```yaml
# Préserver les paramètres entre accolades
datatable:
  info: "Affichage de {start} à {end} sur {rows} entrées"
  info: "Showing {start} to {end} of {rows} entries"
```

### Messages de confirmation avec paramètres
```yaml
user:
  confirm_delete: "Êtes-vous sûr de vouloir supprimer l'utilisateur %name% ?"
  confirm_delete: "Are you sure you want to delete user %name%?"
```

---

## 🎯 Objectif Final

**0 texte en dur dans les fichiers Twig**
**100% de traduction FR et EN**
**Structure cohérente et maintenable**
**Application multilingue fonctionnelle**

---

## 📞 Support

En cas de doute sur :
- Le contexte d'un texte
- La clé de traduction appropriée
- La structure hiérarchique

➡️ Demander une clarification avant de procéder au remplacement