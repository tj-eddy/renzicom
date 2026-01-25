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
