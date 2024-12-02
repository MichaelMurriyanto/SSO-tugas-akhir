let keycloakConfig = {
  "realm": "myrealm",
  "auth-server-url": "http://127.0.0.1:8080",
  "ssl-required": "external",
  "resource": "web-app3",
  "credentials": {
      "secret": "WIBGgzCN8P5JAtIy4OpbMu2U8Nri34rh"
  },
  "confidential-port": 0,
  "enable-cors": true,
  "clientId": "web-app3"
};
window.keycloak = new Keycloak(keycloakConfig);