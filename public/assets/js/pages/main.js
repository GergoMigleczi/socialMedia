window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
      // This is triggered when navigating back using browser controls
      window.location.reload();
    }
});