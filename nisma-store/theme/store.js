/* Wishlist stores only product IDs; commerce state stays in WooCommerce. */
(() => {
  const key = "nisma-saved";
  let saved = [];
  try {
    const parsed = JSON.parse(localStorage.getItem(key) || "[]");
    if (Array.isArray(parsed)) saved = parsed.filter(Number.isInteger);
  } catch {}
  const announce = document.querySelector("#store-status");
  const persist = () => {
    try {
      localStorage.setItem(key, JSON.stringify(saved));
    } catch {
      if (announce)
        announce.textContent =
          "Browser storage is unavailable. Saved pieces will last for this page only.";
    }
  };
  const update = () =>
    document.querySelectorAll("[data-save]").forEach((button) => {
      const on = saved.includes(Number(button.dataset.save));
      button.setAttribute("aria-pressed", String(on));
      button.innerHTML = on ? "♥ <span>Saved</span>" : "♡ <span>Save</span>";
    });
  const container = document.querySelector("#saved-pieces");
  const render = () => {
    if (!container) return;
    const catalog = JSON.parse(
      document.querySelector("#saved-catalog").textContent,
    );
    container.replaceChildren();
    const items = catalog.filter((p) => saved.includes(p.id));
    if (!items.length) {
      const p = document.createElement("p");
      p.textContent =
        "No saved pieces yet. Explore the collection and use Save on a product.";
      const a = document.createElement("a");
      a.href = "/?post_type=product";
      a.textContent = "Explore the shop →";
      container.append(p, a);
      return;
    }
    for (const product of items) {
      const article = document.createElement("article"),
        link = document.createElement("a"),
        img = document.createElement("img"),
        title = document.createElement("h2"),
        price = document.createElement("p"),
        button = document.createElement("button");
      link.href = product.url;
      img.src = product.image;
      img.alt = product.name + " — illustrative stock photograph";
      img.loading = "lazy";
      title.textContent = product.name;
      price.textContent = product.price;
      link.append(img, title, price);
      button.type = "button";
      button.className = "save-piece";
      button.dataset.save = product.id;
      button.setAttribute(
        "aria-label",
        "Remove " + product.name + " from saved pieces",
      );
      article.append(link, button);
      container.append(article);
    }
    update();
  };
  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-save]");
    if (!button) return;
    const id = Number(button.dataset.save);
    const on = saved.includes(id);
    saved = on ? saved.filter((v) => v !== id) : [...saved, id];
    persist();
    if (announce)
      announce.textContent = on
        ? "Piece removed from saved items."
        : "Piece saved in this browser.";
    update();
    if (container) {
      render();
      const next = container.querySelector("button,a");
      next?.focus();
    }
  });
  window.addEventListener("storage", (event) => {
    if (event.key === key) {
      try {
        saved = JSON.parse(event.newValue || "[]");
        if (!Array.isArray(saved)) saved = [];
      } catch {
        saved = [];
      }
      update();
      render();
    }
  });
  update();
  render();
})();
