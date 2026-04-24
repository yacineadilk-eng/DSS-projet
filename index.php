<?php
declare(strict_types=1);
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CineLab - Plateforme Filmographie</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <header class="hero">
    <div>
      <p class="eyebrow">Lab 12 - Projet Final</p>
      <h1>CineLab</h1>
      <p class="subtitle">Plateforme filmographie avec JSON + JSON Schema + interface dynamique.</p>
    </div>
    <div class="session-box">
      <div id="sessionStatus">Non connecte</div>
      <button id="logoutBtn" class="btn ghost hidden">Se deconnecter</button>
    </div>
  </header>

  <main class="layout">
    <aside class="panel">
      <h2>Navigation</h2>
      <nav class="menu">
        <button data-tab="auth" class="tab-btn active">Connexion / Inscription</button>
        <button data-tab="catalog" class="tab-btn">Catalogue Films</button>
        <button data-tab="categories" class="tab-btn">Categories</button>
        <button data-tab="profile" class="tab-btn">Profil</button>
        <button data-tab="recommend" class="tab-btn">Recommandations</button>
        <button data-tab="chatbot" class="tab-btn">Chatbot</button>
        <button data-tab="admin" class="tab-btn">Admin</button>
      </nav>
    </aside>

    <section class="panel content">
      <section id="tab-auth" class="tab active">
        <h2>Connexion</h2>
        <form id="loginForm" class="form-grid">
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Mot de passe" required>
          <button class="btn" type="submit">Se connecter</button>
        </form>

        <h3>Inscription</h3>
        <form id="registerForm" class="form-grid">
          <input type="text" name="firstName" placeholder="Prenom" required>
          <input type="text" name="lastName" placeholder="Nom" required>
          <input type="number" name="age" min="10" max="100" placeholder="Age" required>
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Mot de passe" required>
          <button class="btn" type="submit">S inscrire</button>
        </form>

        <h3>Confirmation Email (simulation)</h3>
        <form id="verifyForm" class="form-grid">
          <input type="text" name="token" placeholder="Token de verification" required>
          <button class="btn" type="submit">Confirmer</button>
        </form>
      </section>

      <section id="tab-catalog" class="tab">
        <div class="split">
          <h2>Catalogue</h2>
          <form id="searchForm" class="inline-form">
            <input type="text" name="q" placeholder="Recherche intelligente...">
            <button class="btn" type="submit">Rechercher</button>
          </form>
        </div>
        <p id="searchHint" class="hint"></p>
        <div id="filmsGrid" class="cards"></div>
      </section>

      <section id="tab-categories" class="tab">
        <h2>Categories</h2>
        <div id="categoriesList" class="chips"></div>
      </section>

      <section id="tab-profile" class="tab">
        <h2>Profil Utilisateur</h2>
        <form id="profileForm" class="form-grid">
          <input type="number" name="age" min="10" max="100" placeholder="Age">
          <label>Genres preferes (IDs separes par virgule)</label>
          <input type="text" name="favoriteGenres" placeholder="action,drama,sci-fi">
          <button class="btn" type="submit">Sauvegarder profil</button>
        </form>
      </section>

      <section id="tab-recommend" class="tab">
        <div class="split">
          <h2>Recommandations</h2>
          <button id="recommendBtn" class="btn">Generer</button>
        </div>
        <div id="recommendationsList" class="cards"></div>
      </section>

      <section id="tab-chatbot" class="tab">
        <h2>Chatbot Cinema</h2>
        <form id="chatbotForm" class="form-grid">
          <input type="text" name="mood" placeholder="Humeur (joyeux, triste, excite, calme)">
          <input type="text" name="genre" placeholder="Genre souhaite (action,drama...)">
          <input type="number" name="duration" placeholder="Duree max en minutes">
          <input type="text" name="language" placeholder="Langue (FR, EN, JP, ANY)">
          <button class="btn" type="submit">Parler au chatbot</button>
        </form>
        <p id="chatbotMessage" class="hint"></p>
        <div id="chatbotFilms" class="cards"></div>
      </section>

      <section id="tab-admin" class="tab">
        <h2>Admin Dashboard</h2>
        <button id="adminStatsBtn" class="btn">Charger stats</button>
        <pre id="adminStats" class="code"></pre>

        <h3>CRUD Film</h3>
        <form id="filmForm" class="form-grid">
          <input type="text" name="id" placeholder="ID (laisser vide pour creation)">
          <input type="text" name="title" placeholder="Titre" required>
          <input type="number" name="year" placeholder="Annee" required>
          <input type="text" name="genres" placeholder="Genres CSV ex: action,sci-fi" required>
          <input type="text" name="director" placeholder="Realisateur" required>
          <input type="text" name="actors" placeholder="Acteurs CSV" required>
          <input type="number" step="0.1" min="0" max="10" name="rating" placeholder="Note /10" required>
          <input type="number" name="durationMin" placeholder="Duree min" required>
          <input type="number" name="ageLimit" placeholder="Age limit 0/7/10/12/16/18" required>
          <input type="text" name="status" placeholder="Status available/coming_soon/archived" required>
          <input type="text" name="language" placeholder="Langue FR/EN/JP..." required>
          <textarea name="description" placeholder="Description" required></textarea>
          <button class="btn" type="submit">Ajouter / Modifier</button>
        </form>

        <form id="deleteFilmForm" class="inline-form">
          <input type="text" name="id" placeholder="ID du film a supprimer" required>
          <button class="btn danger" type="submit">Supprimer</button>
        </form>
      </section>

      <div id="messageBox" class="message"></div>
    </section>
  </main>

  <script src="assets/app.js"></script>
</body>
</html>
