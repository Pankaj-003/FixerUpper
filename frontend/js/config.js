
(function () {
  'use strict';

  const localHosts = new Set(['localhost', '127.0.0.1']);
  const isLocal = localHosts.has(window.location.hostname) || window.location.protocol === 'file:';

  window.AppConfig = Object.freeze({
    API_BASE_URL: window.FIXERUPPER_API_URL
      || (isLocal
          ? 'http://localhost:8080'
          : 'https://fixerupper-1.onrender.com'),
    REQUEST_TIMEOUT_MS: 15000
  });
}());