# Workflow — Cycle de developpement par ticket

Chaque ticket suit ce cycle en 4 phases. Ne jamais passer a la phase suivante sans validation explicite du user.

```
PLAN → ANALYZE → BUILD → TEST
 ↑                         │
 └─── retour si regression ┘
```

## Phase 1 — PLAN

Comprendre le ticket avant d'ecrire du code. Entrer en `EnterPlanMode`.

- Reformuler : "Cette US permet a [qui] de [quoi] afin de [pourquoi]."
- Identifier : entites Doctrine, patterns Symfony (EventSubscriber, Live Component, Workflow, Messenger), risques, dependances
- Decouper en sous-taches atomiques

**Checkpoint** : presenter reformulation + entites + patterns + sous-taches → attendre validation.

## Phase 2 — ANALYZE

Lire le code existant avant de concevoir.

- Lire services, repositories, entites, templates concernes
- Choisir l'approche selon la philosophie projet :
    - `EventSubscriber`/`EntityListener` > surcharges directes
    - Live Components pour interactivite serveur (pas de JS custom)
    - Messenger pour traitements async
- Definir fichiers a creer/modifier et strategie de test

**Checkpoint** : presenter approche + fichiers + justification → attendre validation.

## Phase 3 — BUILD

Implementer sous-tache par sous-tache.

**Ordre** : Modele (entite/migration) → Logique metier (service) → Integration (subscriber/workflow) → Interface (component/template)

**Apres chaque sous-tache** :

```bash
symfony php vendor/bin/phpstan analyse
symfony php vendor/bin/php-cs-fixer fix
```

**Checkpoint** : presenter fichiers modifies + comportement + reste a faire → attendre validation.

## Phase 4 — TEST

| Code ecrit                       | Test requis                   |
|----------------------------------|-------------------------------|
| Service / Command Handler        | Unit (`createStub()`)         |
| Repository custom                | Functional (`KernelTestCase`) |
| EventSubscriber / EntityListener | Unit (declenchement manuel)   |
| Workflow / StateMachine          | Functional (`KernelTestCase`) |
| Template / UI / parcours         | E2E Playwright (`data-test`)  |

**Actions** : ecrire les tests, lancer PHPUnit + PHPStan + CS-Fixer, verifier 0 regressions.

**Checkpoint final** : resultats tests + regressions + pret pour review → attendre validation.
