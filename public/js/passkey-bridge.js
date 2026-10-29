(function() {
  'use strict';

  function hasNativeBridge() {
    return typeof window.NativeBridge !== 'undefined' &&
           typeof window.NativeBridge.passkeyGet === 'function' &&
           typeof window.NativeBridge.passkeyCreate === 'function';
  }

  function ensureCallback() {
    if (typeof window.__nativePasskeyCallback === 'function') {
      return;
    }
    window.__nativePasskeyCallbacks = window.__nativePasskeyCallbacks || {};
    window.__nativePasskeyCallback = function(envelope) {
      if (!envelope || typeof envelope.id !== 'string') return;
      var cb = window.__nativePasskeyCallbacks[envelope.id];
      if (typeof cb === 'function') {
        delete window.__nativePasskeyCallbacks[envelope.id];
        try {
          cb(envelope);
        } catch (e) {}
      }
    };
  }

  function generateId() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
      return window.crypto.randomUUID();
    }
    return 'pk_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2);
  }

  function bridgeSign(publicKeyOptions, callback) {
    ensureCallback();
    var id = generateId();
    var requestJson = JSON.stringify(publicKeyOptions);
    
    var done = false;
    var timeout = setTimeout(function() {
      if (done) return;
      done = true;
      if (window.__nativePasskeyCallbacks && window.__nativePasskeyCallbacks[id]) {
        delete window.__nativePasskeyCallbacks[id];
      }
    }, 60000);

    window.__nativePasskeyCallbacks[id] = function(envelope) {
      if (done) return;
      done = true;
      clearTimeout(timeout);
      if (!envelope || !envelope.ok) {
        return;
      }
      try {
        var cred = envelope.data;
        if (typeof callback === 'function') {
          callback(cred);
        }
      } catch (e) {}
    };

    try {
      window.NativeBridge.passkeyGet(id, requestJson);
    } catch (e) {
      if (!done) {
        done = true;
        clearTimeout(timeout);
        if (window.__nativePasskeyCallbacks && window.__nativePasskeyCallbacks[id]) {
          delete window.__nativePasskeyCallbacks[id];
        }
      }
      throw e;
    }
  }

  function bridgeRegister(publicKeyOptions, callback) {
    ensureCallback();
    var id = generateId();
    var requestJson = JSON.stringify(publicKeyOptions);
    
    var done = false;
    var timeout = setTimeout(function() {
      if (done) return;
      done = true;
      if (window.__nativePasskeyCallbacks && window.__nativePasskeyCallbacks[id]) {
        delete window.__nativePasskeyCallbacks[id];
      }
    }, 60000);

    window.__nativePasskeyCallbacks[id] = function(envelope) {
      if (done) return;
      done = true;
      clearTimeout(timeout);
      if (!envelope || !envelope.ok) {
        return;
      }
      try {
        var cred = envelope.data;
        if (typeof callback === 'function') {
          callback(cred);
        }
      } catch (e) {}
    };

    try {
      window.NativeBridge.passkeyCreate(id, requestJson);
    } catch (e) {
      if (!done) {
        done = true;
        clearTimeout(timeout);
        if (window.__nativePasskeyCallbacks && window.__nativePasskeyCallbacks[id]) {
          delete window.__nativePasskeyCallbacks[id];
        }
      }
      throw e;
    }
  }

  function installBridge() {
    if (!hasNativeBridge()) return false;
    if (window.__nativePasskeyBridgeInstalled) return true;

    if (typeof window.WebAuthn === 'function' && window.WebAuthn.prototype) {
      var origSign = window.WebAuthn.prototype.sign;
      var origRegister = window.WebAuthn.prototype.register;

      window.WebAuthn.prototype.sign = function(publicKey, callback) {
        if (!hasNativeBridge()) {
          if (typeof origSign === 'function') return origSign.call(this, publicKey, callback);
          throw new Error('WebAuthn not supported');
        }
        try {
          var self = this;
          bridgeSign(publicKey, function(cred) {
            callback.call(self, cred);
          });
          return;
        } catch (e) {
          if (this._notify) {
            this._notify(e.name || 'Error', e.message || String(e), true);
          }
          throw e;
        }
      };

      window.WebAuthn.prototype.register = function(publicKey, callback) {
        if (!hasNativeBridge()) {
          if (typeof origRegister === 'function') return origRegister.call(this, publicKey, callback);
          throw new Error('WebAuthn not supported');
        }
        try {
          var self = this;
          bridgeRegister(publicKey, function(cred) {
            callback.call(self, cred);
          });
          return;
        } catch (e) {
          if (this._notify) {
            this._notify(e.name || 'Error', e.message || String(e), true);
          }
          throw e;
        }
      };
    }

    window.__nativePasskeyBridgeInstalled = true;
    return true;
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', installBridge);
  } else {
    installBridge();
  }
})();
