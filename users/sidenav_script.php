<!-- Include at the end of <body> or with defer -->
<script>
  (function markActiveNav() {
    const stripSlash = p => p.replace(/\/+$/, ""); // remove trailing slash
    const here = stripSlash(new URL(window.location.href).pathname);

    const links = Array.from(document.querySelectorAll(".nav a[href]"));
    if (!links.length) return;

    // Remove any pre-set actives
    links.forEach(a => a.classList.remove("active"));

    // Build list of candidates with their normalized absolute path
    const candidates = links.map(a => {
      const abs = new URL(a.getAttribute("href"), window.location.href).pathname;
      return {
        el: a,
        path: stripSlash(abs)
      };
    });

    // 1) Exact match first
    let best = candidates.find(c => c.path === here);

    // 2) Otherwise, pick the longest path that is a prefix of current path
    if (!best) {
      best = candidates
        .filter(c => here.startsWith(c.path) && c.path.length > 0)
        .sort((a, b) => b.path.length - a.path.length)[0];
    }

    // 3) Fallback: if nothing matched (e.g., 404), don’t mark anything
    if (best && best.el) {
      best.el.classList.add("active");
      best.el.setAttribute("aria-current", "page");
    }
  })();
</script>