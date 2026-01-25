# Agent de Détection et Nettoyage Symfony

## 🎯 Objectif
Détecter et analyser les fonctions inutilisées dans les Controllers et Services, ainsi que les fichiers inutilisés dans Controller, Form et Entity.

## 📋 Zones d'analyse
- `src/Controller/` - Controllers et leurs méthodes
- `src/Service/` - Services et leurs méthodes
- `src/Form/` - Classes de formulaires
- `src/Entity/` - Entités Doctrine

## 🔍 Phase 1 : Détection des fonctions inutilisées

### Dans les Controllers (`src/Controller/`)

**Critères de détection :**
1. Méthodes publiques sans annotation de route (`#[Route]` ou `@Route`)
2. Méthodes privées/protégées jamais appelées dans le même controller
3. Méthodes qui ne sont pas des actions (pas de return Response/JsonResponse)
4. Méthodes non référencées dans les templates Twig

**Analyse :**
```
Pour chaque fichier Controller :
  ├─ Extraire toutes les méthodes publiques
  ├─ Vérifier présence d'annotations #[Route] ou @Route
  ├─ Vérifier les appels dans le projet (grep/recherche)
  ├─ Vérifier références dans templates (.twig)
  └─ Marquer comme inutilisée si aucune référence
```

**Exceptions à garder :**
- `__construct()`, `__invoke()`
- Méthodes avec `#[Required]` (injection de dépendances)
- Méthodes héritées de AbstractController utilisées

### Dans les Services (`src/Service/`)

**Critères de détection :**
1. Méthodes publiques jamais appelées dans le projet
2. Méthodes privées/protégées jamais utilisées en interne
3. Services complets non injectés nulle part

**Analyse :**
```
Pour chaque fichier Service :
  ├─ Extraire toutes les méthodes publiques
  ├─ Rechercher les injections du service (constructeurs, arguments)
  ├─ Rechercher les appels de méthodes dans tout le projet
  ├─ Analyser services.yaml pour autowiring/configuration
  └─ Marquer méthodes/service comme inutilisés si aucun usage
```

**Exceptions à garder :**
- `__construct()`, `__invoke()`
- Méthodes utilisées par EventSubscriber
- Méthodes appelées dynamiquement (via conteneur)

## 🗑️ Phase 2 : Détection des fichiers inutilisés

### Controllers inutilisés

**Critères :**
- Aucune route définie dans le controller
- Controller non référencé dans routing.yaml/annotations
- Aucun extends/use dans d'autres controllers

**Vérifications :**
```
Pour chaque Controller :
  ├─ Compter le nombre de routes (#[Route])
  ├─ Vérifier références dans config/routes/
  ├─ Vérifier si classe parente (extended by others)
  └─ Marquer comme inutilisé si 0 routes ET non parent
```

### Forms inutilisés

**Critères :**
- FormType non utilisé dans aucun controller
- Non référencé dans createForm() ou $this->createFormBuilder()
- Non utilisé dans d'autres forms (embedded forms)

**Vérifications :**
```
Pour chaque FormType :
  ├─ Rechercher createForm(XxxType::class)
  ├─ Rechercher dans tous les controllers
  ├─ Vérifier usage dans d'autres FormTypes
  └─ Marquer comme inutilisé si aucune référence
```

### Entities inutilisées

**Critères :**
- Entity non référencée dans aucun repository query
- Non utilisée dans relations Doctrine (OneToMany, ManyToOne, etc.)
- Non injectée dans controllers/services
- Aucune table correspondante en base (optionnel)

**Vérifications :**
```
Pour chaque Entity :
  ├─ Rechercher dans tous les repositories
  ├─ Vérifier relations Doctrine dans autres entities
  ├─ Rechercher injections/utilisations (::class)
  ├─ Vérifier FormTypes associés
  └─ Marquer comme inutilisée si aucun usage
```

## 🛠️ Script de détection (bash/PHP)

```bash
#!/bin/bash

PROJECT_ROOT="."
REPORT_FILE="unused_detection_report.md"

echo "# Rapport de détection - $(date)" > $REPORT_FILE
echo "" >> $REPORT_FILE

# 1. Détection des méthodes dans Controllers
echo "## 🎮 Controllers - Méthodes inutilisées" >> $REPORT_FILE
echo "" >> $REPORT_FILE

for controller in src/Controller/*.php; do
    if [ -f "$controller" ]; then
        echo "### $(basename $controller)" >> $REPORT_FILE
        
        # Extraction des méthodes publiques sans #[Route]
        grep -n "public function" "$controller" | while read line; do
            line_num=$(echo $line | cut -d: -f1)
            method_name=$(echo $line | sed 's/.*public function \([a-zA-Z_]*\).*/\1/')
            
            # Vérifier si route existe au-dessus
            route_check=$(sed -n "$((line_num-5)),$((line_num-1))p" "$controller" | grep -c "#\[Route\]")
            
            if [ $route_check -eq 0 ] && [ "$method_name" != "__construct" ]; then
                # Rechercher usage dans le projet
                usage_count=$(grep -r "$method_name" --include="*.php" --include="*.twig" . | grep -v "function $method_name" | wc -l)
                
                if [ $usage_count -eq 0 ]; then
                    echo "- ⚠️  \`$method_name()\` ligne $line_num - **0 usage trouvé**" >> $REPORT_FILE
                fi
            fi
        done
        echo "" >> $REPORT_FILE
    fi
done

# 2. Détection des méthodes dans Services
echo "## 🔧 Services - Méthodes inutilisées" >> $REPORT_FILE
echo "" >> $REPORT_FILE

for service in src/Service/*.php; do
    if [ -f "$service" ]; then
        service_class=$(basename "$service" .php)
        echo "### $service_class" >> $REPORT_FILE
        
        grep -n "public function" "$service" | while read line; do
            line_num=$(echo $line | cut -d: -f1)
            method_name=$(echo $line | sed 's/.*public function \([a-zA-Z_]*\).*/\1/')
            
            if [ "$method_name" != "__construct" ]; then
                usage_count=$(grep -r "$method_name" --include="*.php" . | grep -v "function $method_name" | wc -l)
                
                if [ $usage_count -eq 0 ]; then
                    echo "- ⚠️  \`$method_name()\` ligne $line_num - **0 usage trouvé**" >> $REPORT_FILE
                fi
            fi
        done
        echo "" >> $REPORT_FILE
    fi
done

# 3. Détection des Controllers inutilisés
echo "## 📁 Controllers inutilisés (complets)" >> $REPORT_FILE
echo "" >> $REPORT_FILE

for controller in src/Controller/*.php; do
    if [ -f "$controller" ]; then
        route_count=$(grep -c "#\[Route\]" "$controller")
        controller_name=$(basename "$controller")
        
        if [ $route_count -eq 0 ]; then
            echo "- 🗑️  **$controller_name** - Aucune route définie" >> $REPORT_FILE
        fi
    fi
done
echo "" >> $REPORT_FILE

# 4. Détection des Forms inutilisés
echo "## 📝 Forms inutilisés" >> $REPORT_FILE
echo "" >> $REPORT_FILE

for form in src/Form/*Type.php; do
    if [ -f "$form" ]; then
        form_class=$(basename "$form" .php)
        usage_count=$(grep -r "createForm($form_class" --include="*.php" . | wc -l)
        usage_count2=$(grep -r "$form_class::class" --include="*.php" . | grep -v "class $form_class" | wc -l)
        
        total_usage=$((usage_count + usage_count2))
        
        if [ $total_usage -eq 0 ]; then
            echo "- 🗑️  **$form_class** - Aucune utilisation trouvée" >> $REPORT_FILE
        fi
    fi
done
echo "" >> $REPORT_FILE

# 5. Détection des Entities inutilisées
echo "## 🗄️  Entities inutilisées" >> $REPORT_FILE
echo "" >> $REPORT_FILE

for entity in src/Entity/*.php; do
    if [ -f "$entity" ]; then
        entity_class=$(basename "$entity" .php)
        
        # Recherche dans repositories
        repo_usage=$(grep -r "$entity_class" src/Repository/ 2>/dev/null | wc -l)
        
        # Recherche dans controllers/services
        general_usage=$(grep -r "$entity_class::class" --include="*.php" src/ | grep -v "class $entity_class" | wc -l)
        
        total_usage=$((repo_usage + general_usage))
        
        if [ $total_usage -eq 0 ]; then
            echo "- 🗑️  **$entity_class** - Aucune utilisation trouvée" >> $REPORT_FILE
        fi
    fi
done

echo "" >> $REPORT_FILE
echo "---" >> $REPORT_FILE
echo "Rapport généré le $(date)" >> $REPORT_FILE

echo "✅ Rapport généré : $REPORT_FILE"
```

## 📊 Format du rapport généré

Le script génère un fichier `unused_detection_report.md` avec :

- Liste des méthodes inutilisées par Controller
- Liste des méthodes inutilisées par Service
- Liste des Controllers complets sans routes
- Liste des FormTypes non utilisés
- Liste des Entities non référencées

## ⚙️ Utilisation

```bash
# Rendre le script exécutable
chmod +x detect_unused.sh

# Exécuter l'analyse
./detect_unused.sh

# Consulter le rapport
cat unused_detection_report.md
```

## ⚠️ Précautions avant suppression

**Ne pas supprimer automatiquement :**

1. **Vérifier manuellement** chaque élément détecté
2. **Méthodes appelées dynamiquement** (via strings, events, etc.)
3. **Code legacy** potentiellement utilisé par API externe
4. **Entities** liées à des migrations non révoquées
5. **Services** utilisés dans config YAML uniquement

**Checklist avant suppression :**
- [ ] Vérifier dans les fichiers YAML (services.yaml, config/)
- [ ] Rechercher usage dynamique (`$this->get('service_name')`)
- [ ] Vérifier événements Symfony (EventSubscriber)
- [ ] Consulter historique git (peut être récemment désactivé)
- [ ] Tester l'application après suppression

## 🔄 Script de suppression sécurisé

```bash
#!/bin/bash
# Créer une branche de backup avant suppression
git checkout -b cleanup-unused-code
git commit -am "Backup avant nettoyage"

# Supprimer les fichiers marqués (manuellement après validation)
# rm src/Controller/UnusedController.php
# rm src/Form/UnusedFormType.php
# etc.

# Tester
php bin/console cache:clear
composer install
php bin/phpunit

# Si OK, merger. Sinon :
# git checkout main && git branch -D cleanup-unused-code
```

## 📈 Améliorations possibles

- Intégration avec PHPStan/Psalm pour analyse statique
- Détection des imports inutilisés (`use` statements)
- Analyse des EventSubscribers
- Vérification des commandes console
- Export JSON pour traitement automatisé

## 🎓 Bonnes pratiques

1. **Exécuter régulièrement** (mensuel recommandé)
2. **Versionner les rapports** pour suivre l'évolution
3. **Review en équipe** avant suppression
4. **Documenter** pourquoi certains éléments sont gardés malgré détection
5. **Tests unitaires** pour code critique avant suppression