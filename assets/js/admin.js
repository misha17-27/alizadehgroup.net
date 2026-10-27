/* Alizadeh Group — admin panel behaviour */
(function () {
  "use strict";
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var iconSvg = function (n) { return '<svg class="ico" aria-hidden="true"><use href="#i-' + (n || "check") + '"/></svg>'; };

  /* confirm before destructive actions (data-confirm on a form or on one of its buttons) */
  document.addEventListener("submit", function (e) {
    var f = e.target, msg = f.getAttribute && f.getAttribute("data-confirm");
    if (msg && !window.confirm(msg)) e.preventDefault();
  }, true);
  document.addEventListener("click", function (e) {
    var b = e.target.closest && e.target.closest("button[data-confirm]");
    if (b && !window.confirm(b.getAttribute("data-confirm"))) e.preventDefault();
  }, true);

  /* icon picker preview */
  document.addEventListener("change", function (e) {
    var s = e.target;
    if (s.matches && s.matches("[data-icon-select]")) {
      var p = s.parentNode.querySelector("[data-icon-prev]");
      if (p) p.innerHTML = iconSvg(s.value);
    }
    if (s.matches && s.matches("[data-link-select]")) {
      var u = s.parentNode.querySelector("[data-link-url]");
      if (u) { u.hidden = s.value !== "__custom"; if (!u.hidden) u.focus(); }
    }
    if (s.matches && s.matches("[data-img-input]") && s.files && s.files[0]) {
      var prev = s.closest(".imgfield").querySelector("[data-img-prev]");
      var r = new FileReader();
      r.onload = function () { prev.innerHTML = '<img src="' + r.result + '" alt="">'; };
      r.readAsDataURL(s.files[0]);
    }
  });

  /* repeaters */
  var seq = 0;
  function repCount(rep) {
    var c = rep.querySelector(".rep-head .muted");
    if (c) c.textContent = $$("[data-rep-row]", $("[data-rep-list]", rep)).length;
  }
  document.addEventListener("click", function (e) {
    var b = e.target.closest("[data-rep-add],[data-rep-del],[data-rep-up],[data-rep-down]");
    if (!b) return;
    var rep = b.closest("[data-rep]"), list = $("[data-rep-list]", rep);
    if (b.hasAttribute("data-rep-add")) {
      var html = $("[data-rep-tpl]", rep).innerHTML.replace(/__i__/g, "n" + (++seq) + Date.now().toString(36));
      var tmp = document.createElement("div");
      tmp.innerHTML = html;
      var row = tmp.firstElementChild;
      list.appendChild(row);
      var first = $("input[type=text],textarea", row);
      if (first) first.focus();
    } else {
      var r = b.closest("[data-rep-row]");
      if (b.hasAttribute("data-rep-del")) r.remove();
      else if (b.hasAttribute("data-rep-up") && r.previousElementSibling) list.insertBefore(r, r.previousElementSibling);
      else if (b.hasAttribute("data-rep-down") && r.nextElementSibling) list.insertBefore(r.nextElementSibling, r);
    }
    markDirty(rep.closest("form"));
    repCount(rep);
  });

  /* bulk select */
  $$("[data-check-all]").forEach(function (c) {
    c.addEventListener("change", function () {
      $$("input[name='ids[]']", c.closest("form")).forEach(function (x) { x.checked = c.checked; });
    });
  });

  /* unsaved changes warning */
  var dirty = null;
  function markDirty(f) { if (f && f.hasAttribute("data-dirty")) dirty = f; }
  $$("form[data-dirty]").forEach(function (f) {
    f.addEventListener("input", function () { dirty = f; });
    f.addEventListener("change", function () { dirty = f; });
    f.addEventListener("submit", function () { dirty = null; });
  });
  window.addEventListener("beforeunload", function (e) {
    if (dirty) { e.preventDefault(); e.returnValue = ""; }
  });

  /* fade success messages */
  setTimeout(function () {
    $$(".flash-ok[data-flash]").forEach(function (f) { f.style.opacity = "0"; setTimeout(function () { f.remove(); }, 450); });
  }, 4500);

  /* close the mobile menu after navigation */
  $$(".side nav a").forEach(function (a) { a.addEventListener("click", function () { var t = $("#navtoggle"); if (t) t.checked = false; }); });
})();
