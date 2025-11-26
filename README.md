# MyWeeklyAllowance - Guide de démarrage

## 🚀 Démarrage rapide

### 1. Lancer l'environnement Docker

```bash
docker-compose up -d
```

Cela démarre :
- **PHP 8.2** avec Apache (port 8080)
- **MySQL 8.0** (port 3306)
- **phpMyAdmin** (port 8081)

### 2. Installer les dépendances

```bash
docker exec -it app_php composer install
```

### 3. Créer la base de données

Accédez à phpMyAdmin : http://localhost:8081
- User: `root`
- Password: `root`

Exécutez le script SQL : `database/migrations.sql`

### 4. Tester l'API

L'API est disponible sur : http://localhost:8080

#### Inscription d'un parent

```bash
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "parent@example.com",
    "password": "password123",
    "role": "PARENT"
  }'
```

#### Inscription d'un teen

```bash
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "teen@example.com",
    "password": "password123",
    "role": "TEEN",
    "parentId": 1
  }'
```

#### Connexion

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "parent@example.com",
    "password": "password123"
  }'
```

#### Créer un wallet

```bash
curl -X POST http://localhost:8080/api/wallet \
  -H "Content-Type: application/json" \
  -d '{
    "userId": 2,
    "initialBalance": 5000
  }'
```

#### Déposer de l'argent

```bash
curl -X POST http://localhost:8080/api/wallet/deposit \
  -H "Content-Type: application/json" \
  -d '{
    "walletId": 1,
    "amount": 1000
  }'
```

### 5. Lancer les tests

```bash
# Tests unitaires
docker exec -it app_php ./vendor/bin/phpunit --testsuite=Unit

# Tests d'intégration
docker exec -it app_php ./vendor/bin/phpunit --testsuite=Integration

# Tous les tests
docker exec -it app_php ./vendor/bin/phpunit

# Avec couverture de code
docker exec -it app_php ./vendor/bin/phpunit --coverage-html coverage
```

## 📁 Structure du projet

```
Project_Weekly_Allowance/
├── config/              # Configuration (routes)
├── database/            # Migrations SQL
├── public/              # Point d'entrée (index.php)
├── src/
│   ├── Domain/         # Entités, Value Objects, Interfaces
│   ├── Application/    # Use Cases, DTOs
│   ├── Infrastructure/ # Repositories SQL, Services
│   └── Presentation/   # Controllers
├── tests/
│   ├── Unit/          # Tests unitaires
│   └── Integration/   # Tests d'intégration
└── vendor/            # Dépendances Composer
```

## 🔧 Configuration

### Variables d'environnement

Créez un fichier `.env` (optionnel) :

```env
DB_HOST=db
DB_PORT=3306
DB_NAME=test_db
DB_USER=root
DB_PASSWORD=root
JWT_SECRET=your-secret-key-change-in-production
```

## 📚 Documentation API

### Endpoints d'authentification

- `POST /api/auth/register` - Inscription
- `POST /api/auth/login` - Connexion
- `POST /api/auth/logout` - Déconnexion

### Endpoints Wallet

- `POST /api/wallet` - Créer un wallet
- `GET /api/wallet/{userId}` - Récupérer un wallet
- `POST /api/wallet/deposit` - Déposer de l'argent
- `POST /api/wallet/expense` - Enregistrer une dépense
- `PUT /api/wallet/allowance` - Définir l'allocation hebdomadaire

## 🧪 Tests

- **51 tests unitaires** ✅
- **Tests d'intégration** pour les repositories
- **Couverture de code** : >80% visée

## 🏗️ Architecture

Le projet suit les principes de **Clean Architecture** :
- **Domain Layer** : Logique métier pure
- **Application Layer** : Use Cases
- **Infrastructure Layer** : Implémentations techniques
- **Presentation Layer** : Controllers HTTP

## 📝 Développement avec TDD

Tous les composants ont été développés en suivant le cycle TDD :
1. **RED** : Écrire les tests (qui échouent)
2. **GREEN** : Implémenter le code (tests passent)
3. **REFACTOR** : Améliorer le code

## 🤝 Contribution

Pour ajouter une nouvelle fonctionnalité :
1. Créer les tests d'abord
2. Implémenter le Use Case
3. Créer le Controller
4. Ajouter la route
5. Vérifier que tous les tests passent
