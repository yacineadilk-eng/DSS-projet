const state = {
  user: null,
  films: [],
  categories: []
};

const msgBox = document.getElementById("messageBox");
const sessionStatus = document.getElementById("sessionStatus");
const logoutBtn = document.getElementById("logoutBtn");

function showMessage(message, isError = false) {
  msgBox.textContent = message;
  msgBox.style.color = isError ? "#9e2a2b" : "#2f4858";
}

async function api(action, method = "GET", body = null, query = "") {
  const url = `api.php?action=${action}${query ? `&${query}` : ""}`;
  const options = {
    method,
    headers: {
      "Content-Type": "application/json"
    }
  };

  if (body) {
    options.body = JSON.stringify(body);
  }

  const res = await fetch(url, options);
  const data = await res.json();
  if (!res.ok || data.ok === false) {
    throw new Error(data.message || "Erreur API");
  }
  return data;
}

function setTabs() {
  const buttons = document.querySelectorAll(".tab-btn");
  const tabs = document.querySelectorAll(".tab");

  buttons.forEach((btn) => {
    btn.addEventListener("click", () => {
      buttons.forEach((b) => b.classList.remove("active"));
      tabs.forEach((t) => t.classList.remove("active"));
      btn.classList.add("active");
      document.getElementById(`tab-${btn.dataset.tab}`).classList.add("active");
    });
  });
}

function renderFilms(list, target = "filmsGrid") {
  const grid = document.getElementById(target);
  grid.innerHTML = "";

  if (!list.length) {
    grid.innerHTML = "<p>Aucun film trouve.</p>";
    return;
  }

  list.forEach((film) => {
    const card = document.createElement("article");
    card.className = "card";
    card.innerHTML = `
      <h3>${film.title}</h3>
      <p><strong>Annee:</strong> ${film.year}</p>
      <p><strong>Genres:</strong> ${film.genres.join(", ")}</p>
      <p><strong>Note:</strong> ${film.rating}/10</p>
      <p><strong>Age:</strong> ${film.ageLimit}+</p>
      <p>${film.description}</p>
      <button class="btn" data-watch="${film.id}">Marquer vu</button>
    `;

    card.querySelector("[data-watch]").addEventListener("click", async () => {
      if (!state.user) {
        showMessage("Connecte-toi d abord pour marquer un film.", true);
        return;
      }
      try {
        await api("mark_watched", "POST", { filmId: film.id, ratingGiven: film.rating });
        showMessage("Film marque comme vu.");
      } catch (err) {
        showMessage(err.message, true);
      }
    });

    grid.appendChild(card);
  });
}

function renderCategories() {
  const wrap = document.getElementById("categoriesList");
  wrap.innerHTML = "";
  state.categories.forEach((cat) => {
    const btn = document.createElement("button");
    btn.className = "chip";
    btn.textContent = `${cat.name} (${cat.id})`;
    btn.addEventListener("click", () => {
      const filtered = state.films.filter((f) => f.genres.includes(cat.id));
      renderFilms(filtered);
      document.querySelector('[data-tab="catalog"]').click();
    });
    wrap.appendChild(btn);
  });
}

async function refreshSession() {
  try {
    const data = await api("session", "GET");
    state.user = data.user;

    if (state.user) {
      sessionStatus.textContent = `Connecte: ${state.user.firstName} (${state.user.role})`;
      logoutBtn.classList.remove("hidden");
    } else {
      sessionStatus.textContent = "Non connecte";
      logoutBtn.classList.add("hidden");
    }
  } catch {
    sessionStatus.textContent = "Session indisponible";
  }
}

async function loadCatalog() {
  const [filmsRes, categoriesRes] = await Promise.all([
    api("list_films"),
    api("list_categories")
  ]);
  state.films = filmsRes.films;
  state.categories = categoriesRes.categories;
  renderFilms(state.films);
  renderCategories();
}

document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const payload = Object.fromEntries(formData.entries());

  try {
    await api("login", "POST", payload);
    await refreshSession();
    showMessage("Connexion reussie.");
  } catch (err) {
    showMessage(err.message, true);
  }
});

document.getElementById("registerForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const payload = Object.fromEntries(formData.entries());
  payload.age = Number(payload.age);

  try {
    const result = await api("register", "POST", payload);
    showMessage(`${result.message} Token: ${result.verificationToken}`);
  } catch (err) {
    showMessage(err.message, true);
  }
});

document.getElementById("verifyForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const token = new FormData(e.target).get("token");
  try {
    const result = await api("verify_email", "POST", { token });
    showMessage(result.message);
  } catch (err) {
    showMessage(err.message, true);
  }
});

logoutBtn.addEventListener("click", async () => {
  try {
    await api("logout", "POST", {});
    await refreshSession();
    showMessage("Deconnecte.");
  } catch (err) {
    showMessage(err.message, true);
  }
});

document.getElementById("searchForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const q = new FormData(e.target).get("q") || "";

  try {
    const res = await api("search_films", "GET", null, `q=${encodeURIComponent(q)}`);
    renderFilms(res.results);
    document.getElementById("searchHint").textContent = res.suggestion
      ? `Suggestion intelligente: ${res.suggestion}`
      : "";
  } catch (err) {
    showMessage(err.message, true);
  }
});

document.getElementById("profileForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const age = Number(formData.get("age"));
  const favoriteGenres = (formData.get("favoriteGenres") || "")
    .split(",")
    .map((g) => g.trim())
    .filter(Boolean);

  try {
    const result = await api("update_profile", "POST", { age, favoriteGenres });
    await refreshSession();
    showMessage(result.message);
  } catch (err) {
    showMessage(err.message, true);
  }
});

document.getElementById("recommendBtn").addEventListener("click", async () => {
  try {
    const res = await api("recommendations");
    renderFilms(res.recommendations, "recommendationsList");
  } catch (err) {
    showMessage(err.message, true);
  }
});

document.getElementById("chatbotForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const payload = Object.fromEntries(new FormData(e.target).entries());
  payload.duration = Number(payload.duration || 0);

  try {
    const res = await api("chatbot", "POST", payload);
    document.getElementById("chatbotMessage").textContent = res.message;
    renderFilms(res.films, "chatbotFilms");
  } catch (err) {
    showMessage(err.message, true);
  }
});

document.getElementById("adminStatsBtn").addEventListener("click", async () => {
  try {
    const res = await api("admin_dashboard");
    document.getElementById("adminStats").textContent = JSON.stringify(res.stats, null, 2);
  } catch (err) {
    showMessage(err.message, true);
  }
});

document.getElementById("filmForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = Object.fromEntries(new FormData(e.target).entries());
  formData.year = Number(formData.year);
  formData.rating = Number(formData.rating);
  formData.durationMin = Number(formData.durationMin);
  formData.ageLimit = Number(formData.ageLimit);
  formData.genres = String(formData.genres).split(",").map((x) => x.trim()).filter(Boolean);
  formData.actors = String(formData.actors).split(",").map((x) => x.trim()).filter(Boolean);

  try {
    const res = await api("save_film", "POST", formData);
    showMessage(res.message);
    await loadCatalog();
  } catch (err) {
    showMessage(err.message, true);
  }
});

document.getElementById("deleteFilmForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const id = new FormData(e.target).get("id");
  try {
    const res = await api("delete_film", "POST", { id });
    showMessage(res.message);
    await loadCatalog();
  } catch (err) {
    showMessage(err.message, true);
  }
});

setTabs();
refreshSession();
loadCatalog().catch((err) => showMessage(err.message, true));
