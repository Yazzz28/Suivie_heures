name: CI - Continuous Integration

on:
push:
branches:
- development
- staging
- production
pull_request:
branches:
- development
- staging
- production

jobs:
# 🧪 TESTS UNITAIRES ET D'INTÉGRATION
unit-integration-tests:
runs-on: ubuntu-latest
steps:
- name: 📥 Récupérer le code
uses: actions/checkout@v4

      - name: 🔧 Installer Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: 📦 Installer les dépendances
        run: npm ci

      - name: 📝 Vérifier la syntaxe (Lint)
        run: npm run lint

      - name: 🧪 Lancer les tests unitaires et d'intégration
        run: npm run test

      - name: 📊 Tests avec couverture
        run: npm run test:coverage

# 🎯 TESTS E2E FRONTEND (CYPRESS)
e2e-frontend-tests:
runs-on: ubuntu-latest
needs: unit-integration-tests
strategy:
matrix:
environment: [staging, production]
steps:
- name: 📥 Récupérer le code
uses: actions/checkout@v4

      - name: 🔧 Installer Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: 📦 Installer les dépendances
        run: npm ci

      - name: 🏗️ Construire l'application pour les tests
        run: npm run build --env=${{ matrix.environment }}

      - name: 🚀 Démarrer le serveur de test
        run: |
          npm run start-server &
          sleep 10

      - name: 🎯 Lancer les tests E2E frontend avec Cypress
        run: npm run e2e:ci --env=${{ matrix.environment }}

# 🌐 TESTS E2E FULL STACK
e2e-fullstack-tests:
runs-on: ubuntu-latest
needs: unit-integration-tests
strategy:
matrix:
environment: [staging]  # On utilise staging pour les tests full stack
steps:
- name: 📥 Récupérer le code
uses: actions/checkout@v4

      - name: 🔧 Installer Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: 🐳 Configurer Docker
        uses: docker/setup-buildx-action@v3

      - name: 🔄 Récupérer l'image Docker du backend
        run: |
          # Pull de l'image backend réelle en environnement staging
          echo "🔄 Récupération de l'image backend en environnement ${{ matrix.environment }}"
          
          # Remplacez par votre vraie image backend
          # docker pull your-registry/ambu-connect-backend:${{ matrix.environment }}
          
          # Pour l'instant, simulation avec nginx qui expose une API REST basique
          docker run -d --name backend-test \
            -p 8080:80 \
            --env NODE_ENV=${{ matrix.environment }} \
            nginx:alpine
          
          # Attendre que le backend soit prêt
          sleep 10

      - name: 📦 Installer les dépendances frontend
        run: npm ci

      - name: 🏗️ Construire l'application frontend
        run: npm run build --env=${{ matrix.environment }}

      - name: 🚀 Démarrer le frontend
        run: |
          npm run start-server &
          sleep 10

      - name: 🌐 Lancer les tests E2E full stack
        run: |
          echo "🧪 Tests E2E complets avec backend en ${{ matrix.environment }}"
          npm run e2e:ci --env=${{ matrix.environment }}

      - name: 🧹 Nettoyer les conteneurs
        if: always()
        run: |
          docker stop backend-test || true
          docker rm backend-test || true

# 🐳 CONSTRUIRE LES IMAGES DOCKER
build-docker-images:
runs-on: ubuntu-latest
needs: [unit-integration-tests, e2e-frontend-tests]
if: github.event_name == 'push'
strategy:
matrix:
environment: [development, staging, production]
steps:
- name: 📥 Récupérer le code
uses: actions/checkout@v4

      - name: 🐳 Configurer Docker
        uses: docker/setup-buildx-action@v3

      - name: 🏗️ Construire l'image Docker
        run: |
          docker build -f Dockerfile.${{ matrix.environment }} \
            -t ambu-connect:${{ matrix.environment }} .

      - name: 💾 Sauvegarder l'image
        run: |
          docker save ambu-connect:${{ matrix.environment }} > ambu-connect-${{ matrix.environment }}.tar

      - name: 📤 Partager l'image
        uses: actions/upload-artifact@v4
        with:
          name: docker-image-${{ matrix.environment }}
          path: ambu-connect-${{ matrix.environment }}.tar
          retention-days: 1

# ✅ VALIDATION FINALE
ci-success:
runs-on: ubuntu-latest
needs: [unit-integration-tests, e2e-frontend-tests, e2e-fullstack-tests, build-docker-images]
if: always()
steps:
- name: ✅ Vérifier le statut du CI
run: |
if [[ "${{ needs.unit-integration-tests.result }}" == "success" && \
"${{ needs.e2e-frontend-tests.result }}" == "success" && \
"${{ needs.e2e-fullstack-tests.result }}" == "success" && \
"${{ needs.build-docker-images.result }}" == "success" ]]; then
echo "🎉 CI réussi ! Toutes les étapes sont passées avec succès."
echo "✅ Tests unitaires/intégration : OK"
echo "✅ Tests E2E frontend : OK"
echo "✅ Tests E2E full stack : OK"
echo "✅ Construction des images Docker : OK"
else
echo "❌ CI échoué. Vérifiez les logs des étapes précédentes."
exit 1
fi

name: CD - Continuous Deployment

on:
push:
branches:
- staging
- production

jobs:
build-and-push:
name: 🛠️ Build & Push Docker image
runs-on: ubuntu-latest

    outputs:
      tag: ${{ steps.set_tag.outputs.tag }}

    steps:
      - name: 📥 Checkout code
        uses: actions/checkout@v4

      - name: 🐳 Set up Docker Buildx
        uses: docker/setup-buildx-action@v3

      - name: 🔐 Login to DockerHub
        uses: docker/login-action@v3
        with:
          username: ${{ secrets.DOCKER_HUB_USERNAME }}
          password: ${{ secrets.DOCKER_HUB_ACCESS_TOKEN }}

      - name: 🏷️ Set Docker image tag
        id: set_tag
        run: |
          if [[ "${GITHUB_REF##*/}" == "production" ]]; then
            echo "tag=production" >> "$GITHUB_OUTPUT"
          else
            echo "tag=staging" >> "$GITHUB_OUTPUT"
          fi

      - name: 🔧 Set Dockerfile based on environment
        id: set_dockerfile
        run: |
          TAG=${{ steps.set_tag.outputs.tag }}
          echo "dockerfile=Dockerfile.$TAG" >> "$GITHUB_OUTPUT"

      - name: 🛠️ Build and Push Docker image
        uses: docker/build-push-action@v5
        with:
          context: .
          file: ${{ steps.set_dockerfile.outputs.dockerfile }}
          push: true
          tags: |
            ${{ secrets.DOCKER_HUB_USERNAME }}/ambu-connect:${{ steps.set_tag.outputs.tag }}
            ${{ secrets.DOCKER_HUB_USERNAME }}/ambu-connect:${{ steps.set_tag.outputs.tag }}-${{ github.sha }}

deploy:
name: 🚀 Deploy to VPS
needs: build-and-push
runs-on: ubuntu-latest

    steps:
      - name: 🔑 Set up SSH key
        run: |
          mkdir -p ~/.ssh
          echo "${{ secrets.VPS_SSH_PRIVATE_KEY }}" > ~/.ssh/id_ed25519
          chmod 600 ~/.ssh/id_ed25519

      - name: 🚀 SSH to VPS and deploy
        run: |
          TAG=${{ needs.build-and-push.outputs.tag }}
          VPS_USER="${{ secrets.VPS_USER }}"
          VPS_HOST="${{ secrets.VPS_HOST }}"
          SSH_KEY="~/.ssh/id_ed25519"
          
          if [[ "$TAG" == "production" ]]; then
            VPS_PATH="~/ambuConnect/ambuConnect_prod/frontend"
            PORT="4202"
          else
            VPS_PATH="~/ambuConnect/ambuConnect_staging/frontend"
            PORT="4201"
          fi

          ssh -o StrictHostKeyChecking=no -i $SSH_KEY $VPS_USER@$VPS_HOST << EOF
            echo "🚀 Déploiement de ambu-connect en environnement $TAG"
            
            # Créer les répertoires si nécessaire
            mkdir -p $VPS_PATH/logs
            mkdir -p $VPS_PATH/config
            
            # Se déplacer dans le répertoire de déploiement
            cd $VPS_PATH
            
            # Arrêter et supprimer le conteneur existant
            echo "🔄 Arrêt du conteneur existant..."
            docker stop ambu-connect-$TAG || true
            docker rm ambu-connect-$TAG || true
            
            # Nettoyer les anciennes images
            echo "🧹 Nettoyage des anciennes images..."
            docker image prune -f
            
            # Récupérer la nouvelle image
            echo "📦 Récupération de l'image Docker..."
            docker pull ${{ secrets.DOCKER_HUB_USERNAME }}/ambu-connect:$TAG
            
            # Démarrer le nouveau conteneur
            echo "🚀 Démarrage du nouveau conteneur..."
            docker run -d \
              --name ambu-connect-$TAG \
              --restart unless-stopped \
              -p $PORT:80 \
              -v $VPS_PATH/logs:/var/log/nginx \
              -v $VPS_PATH/config:/etc/nginx/conf.d \
              ${{ secrets.DOCKER_HUB_USERNAME }}/ambu-connect:$TAG
            
            # Vérifier que le conteneur est démarré
            echo "🔍 Vérification du conteneur..."
            sleep 5
            docker ps | grep ambu-connect-$TAG
            
            echo "✅ Déploiement terminé pour l'environnement $TAG"
            echo "🌐 Application disponible sur le port $PORT"
          EOF

      - name: 🏥 Health check
        run: |
          TAG=${{ needs.build-and-push.outputs.tag }}
          VPS_HOST="${{ secrets.VPS_HOST }}"
          
          if [[ "$TAG" == "production" ]]; then
            PORT="4202"
          else
            PORT="4201"
          fi
          
          echo "🔍 Vérification de santé de l'application..."
          sleep 10
          
          # Tentative de connexion à l'application
          if curl -f -s --max-time 30 http://$VPS_HOST:$PORT/ > /dev/null; then
            echo "✅ Application accessible et fonctionnelle"
          else
            echo "⚠️ Application potentiellement indisponible, vérifiez manuellement"
            echo "🌐 URL: http://$VPS_HOST:$PORT/"
          fi

deployment-notification:
name: 📢 Notification de déploiement
needs: [build-and-push, deploy]
runs-on: ubuntu-latest
if: always()

    steps:
      - name: 📋 Générer le rapport de déploiement
        run: |
          TAG=${{ needs.build-and-push.outputs.tag }}
          
          echo "📊 RAPPORT DE DÉPLOIEMENT AMBU-CONNECT"
          echo "======================================"
          echo "🕐 Date: $(date)"
          echo "🌿 Branche: ${{ github.ref_name }}"
          echo "🏷️ Tag: $TAG"
          echo "📝 Commit: ${{ github.sha }}"
          echo "👤 Auteur: ${{ github.actor }}"
          echo "🐳 Image: ${{ secrets.DOCKER_HUB_USERNAME }}/ambu-connect:$TAG"
          echo ""
          
          if [[ "${{ needs.build-and-push.result }}" == "success" && "${{ needs.deploy.result }}" == "success" ]]; then
            echo "✅ Déploiement réussi"
            echo "🌐 Application disponible en environnement $TAG"
            if [[ "$TAG" == "production" ]]; then
              echo "🎯 URL Production: http://${{ secrets.VPS_HOST }}:4202/"
            else
              echo "🎯 URL Staging: http://${{ secrets.VPS_HOST }}:4201/"
            fi
          else
            echo "❌ Déploiement échoué"
            echo "🔍 Build: ${{ needs.build-and-push.result }}"
            echo "🔍 Deploy: ${{ needs.deploy.result }}"
            echo "📝 Vérifiez les logs des étapes précédentes"
          fi


cree un fichier ci et un fichier cd dans le dossier .github/workflows inspirer de ce code
si il le faut modifie aussi dockerfile et docker-compose
