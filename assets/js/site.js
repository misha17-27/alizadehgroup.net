/*
 * Alizadeh Group — site behaviour
 * header state · mobile menu · slider · reveal · scroll-spy · request form (AJAX + files)
 */
(function () {
  "use strict";
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---------- header ---------- */
  var hdr = $("[data-header]");
  if (hdr) {
    var onScroll = function () { hdr.classList.toggle("is-solid", window.scrollY > 24); };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  /* ---------- mobile menu ---------- */
  var burger = $("[data-burger]");
  function setMenu(open) {
    document.body.classList.toggle("menu-open", open);
    if (!burger) return;
    burger.setAttribute("aria-expanded", open ? "true" : "false");
    burger.setAttribute("aria-label", burger.getAttribute(open ? "data-label-close" : "data-label-open") || "");
  }
  if (burger) {
    burger.addEventListener("click", function () { setMenu(!document.body.classList.contains("menu-open")); });
    $$("[data-mnav] a").forEach(function (a) { a.addEventListener("click", function () { setMenu(false); }); });
    document.addEventListener("keydown", function (e) { if (e.key === "Escape") setMenu(false); });
  }
  /* dropdown on touch devices: first tap opens, second follows the link */
  $$("[data-dd]").forEach(function (dd) {
    var btn = $("[data-dd-btn]", dd);
    if (!btn) return;
    btn.addEventListener("click", function (e) {
      if (window.matchMedia("(hover: hover)").matches) return;
      if (!dd.classList.contains("is-open")) { e.preventDefault(); dd.classList.add("is-open"); btn.setAttribute("aria-expanded", "true"); }
    });
    document.addEventListener("click", function (e) { if (!dd.contains(e.target)) { dd.classList.remove("is-open"); btn.setAttribute("aria-expanded", "false"); } });
  });

  /* ---------- slider ---------- */
  $$("[data-slider]").forEach(function (sl) {
    var slides = $$("[data-slide]", sl);
    var count = 0;
    slides.forEach(function (s) { count = Math.max(count, +s.getAttribute("data-slide") + 1); });
    if (!count) return;
    var cur = -1, timer = null;
    var delay = +sl.getAttribute("data-autoplay") || 0;
    /* the pictures of the other slides come after the page has loaded, so the first screen gets the bandwidth */
    function loadPictures() {
      $$("[data-srcset]", sl).forEach(function (s) { s.srcset = s.getAttribute("data-srcset"); s.removeAttribute("data-srcset"); });
      $$("[data-src]", sl).forEach(function (s) { s.src = s.getAttribute("data-src"); s.removeAttribute("data-src"); });
    }
    if (document.readyState === "complete") loadPictures(); else window.addEventListener("load", loadPictures);
    function go(i) {
      i = (i + count) % count;
      if (i === cur) return;
      if (cur !== -1) loadPictures();
      cur = i;
      slides.forEach(function (s) { s.classList.toggle("is-active", +s.getAttribute("data-slide") === i); });
      $$("[data-goto]", sl).forEach(function (b) {
        var on = +b.getAttribute("data-goto") === i;
        b.classList.toggle("is-active", on);
        b.setAttribute("aria-current", on ? "true" : "false");
        var bar = $("[data-progress]", b);
        if (bar) { bar.style.animation = "none"; void bar.offsetWidth; bar.style.animation = ""; }
      });
      restart();
    }
    function restart() {
      if (!delay || reduce || count < 2) return;
      clearTimeout(timer);
      timer = setTimeout(function () { go(cur + 1); }, delay);
    }
    sl.style.setProperty("--delay", (delay || 6000) + "ms");
    $$("[data-goto]", sl).forEach(function (b) { b.addEventListener("click", function () { go(+b.getAttribute("data-goto")); }); });
    if (delay) {
      sl.addEventListener("mouseenter", function () { clearTimeout(timer); sl.classList.add("is-paused"); });
      sl.addEventListener("mouseleave", function () { sl.classList.remove("is-paused"); restart(); });
      document.addEventListener("visibilitychange", function () { if (document.hidden) clearTimeout(timer); else restart(); });
    }
    var x0 = null;
    sl.addEventListener("touchstart", function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    sl.addEventListener("touchend", function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0; x0 = null;
      if (Math.abs(dx) > 50 && sl.hasAttribute("data-swipe")) go(cur + (dx < 0 ? 1 : -1));
    });
    go(0);
  });

  /* ---------- reveal on scroll ---------- */
  var rv = $$(".rv");
  if ("IntersectionObserver" in window && !reduce) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } });
    }, { threshold: 0.1, rootMargin: "0px 0px -40px 0px" });
    rv.forEach(function (el) { io.observe(el); });
  } else {
    rv.forEach(function (el) { el.classList.add("in"); });
  }

  /* ---------- scroll-spy (service page side nav) ---------- */
  var spy = $("[data-spy]");
  if (spy && "IntersectionObserver" in window) {
    var links = $$("a[href^='#']", spy), map = {};
    links.forEach(function (a) { var t = document.getElementById(a.getAttribute("href").slice(1)); if (t) map[t.id] = a; });
    var sio = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        links.forEach(function (a) { a.classList.remove("is-active"); });
        if (map[e.target.id]) map[e.target.id].classList.add("is-active");
      });
    }, { rootMargin: "-35% 0px -60% 0px" });
    Object.keys(map).forEach(function (id) { sio.observe(document.getElementById(id)); });
  }

  /* ---------- request form ---------- */
  var form = $("[data-rq]");
  if (!form) return;
  var serviceSel = $("[data-rq-service]", form);
  var details = $("[data-rq-details]", form);
  var hint = $("[data-rq-hint]", form);
  var stepNo = $("[data-step-no]", form);

  /* Turnstile: its script is loaded only when the form comes near the screen or gets touched */
  var ts = $("[data-ts-src]", form);
  if (ts) {
    var loadTs = function () {
      if (!ts) return;
      var s = document.createElement("script");
      s.src = ts.getAttribute("data-ts-src"); s.async = true;
      document.head.appendChild(s);
      ts = null;
    };
    form.addEventListener("focusin", loadTs);
    form.addEventListener("pointerdown", loadTs);
    if ("IntersectionObserver" in window) {
      var tio = new IntersectionObserver(function (en) { if (en[0].isIntersecting) { loadTs(); tio.disconnect(); } }, { rootMargin: "800px 0px" });
      tio.observe(form);
    } else loadTs();
  }

  function today() {
    var d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 10);
  }
  $$("[data-min-today]", form).forEach(function (i) { i.min = today(); });

  function syncService() {
    var opt = serviceSel.options[serviceSel.selectedIndex];
    var type = opt ? (opt.getAttribute("data-form") || "") : "";
    var any = false;
    $$("[data-for]", form).forEach(function (fs) {
      var on = !!type && fs.getAttribute("data-for") === type;
      fs.hidden = !on; fs.disabled = !on;
      if (on) any = true;
    });
    details.hidden = !any;
    if (hint) hint.hidden = !!serviceSel.value;
    if (stepNo) stepNo.textContent = any ? "3" : "2";
    if (type === "supply" && !$("[data-product]", form)) addProduct();
    syncReturn();
  }
  serviceSel.addEventListener("change", syncService);

  /* links/cards that preselect a service: stay on this page when the form is here */
  document.addEventListener("click", function (e) {
    var a = e.target.closest("[data-pick]");
    if (!a) return;
    var key = a.getAttribute("data-pick");
    if (!key || !$("option[value='" + key + "']", serviceSel)) return;
    e.preventDefault();
    serviceSel.value = key;
    syncService();
    var target = document.getElementById("form") || form;
    target.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "start" });
    setTimeout(function () { var f = $("[data-for]:not([hidden]) input:not([type=radio]), #rq-name", form); if (f) f.focus({ preventScroll: true }); }, reduce ? 0 : 650);
  });

  /* transport: round trip */
  var rt = $("[data-roundtrip]", form), ret = $("[data-return]", form);
  function syncReturn() {
    if (!rt || !ret) return;
    var on = rt.checked && !rt.disabled;
    ret.hidden = !on;
    $("input", ret).disabled = !on;
  }
  if (rt) rt.addEventListener("change", syncReturn);
  var dateIn = $("#rq-date", form), retIn = $("#rq-return", form);
  if (dateIn && retIn) dateIn.addEventListener("change", function () { retIn.min = dateIn.value || today(); });

  /* hotel: nights */
  var cin = $("[data-checkin]", form), cout = $("[data-checkout]", form), out = $("[data-nights]", form);
  function nights() {
    if (!cin || !cout || !out) return;
    if (cin.value) { var nx = new Date(cin.value); nx.setDate(nx.getDate() + 1); cout.min = nx.toISOString().slice(0, 10); }
    var n = cin.value && cout.value ? Math.round((new Date(cout.value) - new Date(cin.value)) / 864e5) : 0;
    out.classList.toggle("is-set", n > 0);
    out.textContent = n > 0 ? n + " " + (n === 1 ? out.dataset.one : out.dataset.many) : "—";
    cout.setCustomValidity(cin.value && cout.value && n < 1 ? cout.dataset.msg || "" : "");
  }
  if (cin && cout) { cin.addEventListener("change", nights); cout.addEventListener("change", nights); }

  /* supply: product rows */
  var list = $("[data-products]", form), tpl = $("[data-product-tpl]", form), seq = 0;
  function renumber() {
    var rows = $$("[data-product]", list);
    rows.forEach(function (r, i) { $("[data-product-no]", r).textContent = i + 1; $("[data-remove-product]", r).hidden = rows.length < 2; });
  }
  function addProduct(focus) {
    if (!list || !tpl) return;
    seq++;
    var row = tpl.content.firstElementChild.cloneNode(true);
    $$("[data-field]", row).forEach(function (inp) {
      var f = inp.getAttribute("data-field"), id = "rq-p" + seq + "-" + f;
      inp.id = id; inp.name = "products[" + seq + "][" + f + "]";
      var lab = $("[data-for-field='" + f + "']", row); if (lab) lab.setAttribute("for", id);
    });
    var file = $("input[type=file]", row), small = $("small", file.parentNode), def = small.textContent;
    file.addEventListener("change", function () {
      var has = file.files && file.files.length;
      small.textContent = has ? file.files[0].name : def;
      file.parentNode.classList.toggle("has-file", !!has);
    });
    list.appendChild(row);
    renumber();
    if (focus) $("input", row).focus();
  }
  var addBtn = $("[data-add-product]", form);
  if (addBtn) addBtn.addEventListener("click", function () { addProduct(true); });
  if (list) list.addEventListener("click", function (e) {
    var b = e.target.closest("[data-remove-product]");
    if (!b) return;
    b.closest("[data-product]").remove();
    renumber();
  });

  /* submit via fetch (falls back to a normal POST without JS) */
  var okBox = $(".rq-ok", form), errBox = $(".rq-err", form), btn = $("[data-rq-submit]", form);
  function showErr(msg) { errBox.hidden = false; $("span", errBox).textContent = msg; okBox.hidden = true; }
  form.addEventListener("submit", function (e) {
    nights();
    if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
    if (!window.fetch || !window.FormData) return;
    e.preventDefault();
    errBox.hidden = true;
    var label = $("span", btn), old = label.textContent;
    btn.disabled = true; label.textContent = form.getAttribute("data-msg-sending") || "…";
    fetch(form.action, { method: "POST", body: new FormData(form), headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }, credentials: "same-origin" })
      .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
      .then(function (j) {
        if (j && j.ok) {
          okBox.hidden = false;
          if (j.msg) $("span", okBox).textContent = j.msg;
          form.reset();
          $$("[data-product]", form).forEach(function (r) { r.remove(); });
          syncService(); nights();
          if (window.turnstile) try { window.turnstile.reset(); } catch (x) {}
          okBox.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "nearest" });
        } else {
          showErr((j && j.msg) || form.getAttribute("data-msg-server"));
          if (window.turnstile) try { window.turnstile.reset(); } catch (x) {}
        }
      })
      .catch(function () { showErr(form.getAttribute("data-msg-server")); })
      .then(function () { btn.disabled = false; label.textContent = old; });
  });

  syncService();
})();
