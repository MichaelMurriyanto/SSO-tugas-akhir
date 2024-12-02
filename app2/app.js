let keycloakConfig = {
  "realm": "myrealm",
  "auth-server-url": "http://127.0.0.1:8080/",
  "ssl-required": "external",
  "resource": "web-app2",
  "credentials": {
      "secret": "Pirc8v3CMi2zDLyC1wpiLFs7EL6JNXQb"
  },
  "confidential-port": 0,
  "enable-cors": true,
  "clientId": "web-app2"
};
window.keycloak = new Keycloak(keycloakConfig);
