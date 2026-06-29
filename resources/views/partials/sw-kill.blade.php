{{-- One-time cleanup of a retired service worker that cached CSS/JS and caused
     blank ("black") screens after deploys. Runs before the rest of the page so
     a stale visitor is healed immediately: unregister the worker, drop all
     caches, then reload once (guarded by sessionStorage) to fetch fresh assets. --}}
<script>
(function () {
  if (!('serviceWorker' in navigator)) return;
  try {
    navigator.serviceWorker.getRegistrations().then(function (regs) {
      if (!regs || !regs.length) return;
      Promise.all(regs.map(function (r) { return r.unregister(); }))
        .then(function () {
          return (window.caches && caches.keys)
            ? caches.keys().then(function (ks) { return Promise.all(ks.map(function (k) { return caches.delete(k); })); })
            : null;
        })
        .then(function () {
          if (!sessionStorage.getItem('cx_sw_cleared')) {
            sessionStorage.setItem('cx_sw_cleared', '1');
            location.reload();
          }
        }).catch(function () {});
    }).catch(function () {});
  } catch (e) {}
})();
</script>
