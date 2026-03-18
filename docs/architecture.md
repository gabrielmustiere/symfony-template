# Architecture & Conventions

## Couches et flux de donnees

Request → Controller → Service/Manager → Repository → Entity → Response

### Controller (`src/Controller/`)

- Responsabilite : recevoir la requete, deleguer, retourner une reponse
- JAMAIS de logique metier, de requetes Doctrine, de calculs
- 1 action = 1 methode publique, injection des dependances par constructeur
- Retourne : Response, JsonResponse, ou RedirectResponse

### Service / Manager (`src/Service/`)

- Responsabilite : logique metier reutilisable
- Manager quand il orchestre plusieurs operations/services
- Service quand il encapsule une logique unitaire
- Peut appeler des repositories, jamais l'inverse

### Repository (`src/Repository/`)

- Responsabilite : acces donnees uniquement
- Jamais de logique metier
- QueryBuilder pour requetes complexes, methodes find* pour le simple

### Entity (`src/Entity/`)

- Responsabilite : structure de donnees + regles de domaine simples
- Getters/setters, validations de contraintes, methodes de domaine simples
- JAMAIS d'injection de service, JAMAIS d'appel repository

## Arbre de decision — "Ou placer mon code ?"

| Je dois...                      | Type de classe        | Repertoire             |
|---------------------------------|-----------------------|------------------------|
| Gerer une requete HTTP          | Controller            | `src/Controller/`      |
| Executer de la logique metier   | Service / Manager     | `src/Service/`         |
| Requeter la base de donnees     | Repository            | `src/Repository/`      |
| Reagir a un evenement Symfony   | EventSubscriber       | `src/EventSubscriber/` |
| Reagir a un evenement Doctrine  | EntityListener        | `src/EntityListener/`  |
| Traiter un message async        | Message + Handler     | `src/Message/`         |
| Creer un objet complexe         | Factory               | `src/Factory/`         |
| Transformer/mapper des donnees  | Mapper / Transformer  | `src/Mapper/`          |
| Definir un formulaire           | FormType              | `src/Form/`            |
| Valider une contrainte custom   | Constraint+Validator  | `src/Validator/`       |
| Envoyer un email                | Classe Mailer dediee  | `src/Mailer/`          |
| Ajouter un filtre/fonction Twig | TwigExtension+Runtime | `src/Twig/`            |
| Composant interactif serveur    | Live Component        | `src/Twig/Components/` |
| Enum metier                     | Backed string enum    | `src/Enum/Type/`       |

## Anti-patterns (a ne JAMAIS faire)

- Controller qui contient du QueryBuilder ou de la logique metier
- Un QueryBuilder en dehors d'un repository
- Entity qui injecte un service ou appelle un repository
- Service qui retourne une Response HTTP
- Repository qui contient de la logique metier
- Logique dans un template Twig (au-dela d'affichage conditionnel simple)
- `new Service()` au lieu de l'injection de dependances

## Conventions

### Nommage

- **Classes** : PascalCase, suffixees par leur type (`UserRepository`, `InvoiceManager`, `OrderCreatedEvent`)
- **Methodes** : camelCase, verbe d'action (`createUser`, `findByEmail`, `handleOrderCreated`)
- **Templates** : snake_case, miroir de la route (`security/login.html.twig`)
- **Routes** : snake_case prefixees par domaine (`app_login`, `app_page`)

### Doctrine

- **Entites** : `inversedBy`/`mappedBy` bidirectionnels, `ArrayCollection` dans constructeurs, `__toString()` pour l'admin
- **Contraintes d'unicite** : pattern `UNIQ_{TABLE}_{CHAMP}`
- **Migrations** : ne jamais modifier une migration commitee, en creer une nouvelle

### Services

- Injection par constructeur uniquement (pas de setter injection)
- 1 service = 1 responsabilite
