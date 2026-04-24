# DSS-projet - CineLab (Lab 12)

Application web complete de filmographie basee sur:
- stockage JSON
- validation JSON Schema
- interface dynamique avec espace utilisateur et espace admin

## Objectifs Lab 12 couverts

- Stockage structure: fichiers JSON dans `data/`
- Validation par schema: fichiers JSON Schema dans `schema/`
- Interface CRUD web: via `index.php` + `api.php`

## Fonctionnalites implementees

- Authentification:
	- inscription (nom, prenom, age, email, mot de passe)
	- connexion / deconnexion
	- roles `user` et `admin`
- Confirmation email (mode simulation):
	- token genere a l'inscription
	- confirmation via endpoint `verify_email`
- Catalogue films:
	- affichage de tous les films
	- details principaux
	- filtrage par categories
- Recherche intelligente:
	- recherche par titre
	- suggestion par distance de Levenshtein si faute de frappe
- Espace profil:
	- mise a jour age
	- mise a jour genres preferes
- Recommandation films:
	- basee sur age, genres preferes, historique, notes
- Chatbot cinema:
	- questions humeur/genre/duree/langue
	- proposition de films non vus
- Espace admin:
	- dashboard stats (films, users, historique, top genres)
	- CRUD films (ajouter / modifier / supprimer)

## Arborescence

```
.
|-- api.php
|-- index.php
|-- assets/
|   |-- app.js
|   `-- style.css
|-- data/
|   |-- categories.json
|   |-- films.json
|   |-- users.json
|   `-- watch_history.json
|-- schema/
|   |-- categories.schema.json
|   |-- films.schema.json
|   |-- users.schema.json
|   `-- watch_history.schema.json
`-- src/
		|-- auth.php
		|-- helpers.php
		|-- recommendation.php
		|-- storage.php
		`-- validator.php
```

## Donnees initiales

- 8 films minimum deja precharges dans `data/films.json`
- categories predefinies dans `data/categories.json`
- compte admin cree dans `data/users.json`

Compte admin de test:
- Email: `admin@cine.local`
- Mot de passe: `Admin123!`

## Lancement local

Depuis la racine du projet:

```bash
php -S localhost:8000
```

Puis ouvrir:

```text
http://localhost:8000
```

## Notes techniques

- Validation appliquee cote backend a chaque ajout/modification critique.
- Les schemas utilisent les contraintes attendues:
	- types
	- champs obligatoires
	- enums
	- limites numeriques
	- formats (email, date-time)
- Le projet est pret a zipper pour remise.
