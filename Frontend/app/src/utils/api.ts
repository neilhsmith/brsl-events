import axios from "axios";

const namespace = "brsl/v1";

const instance = axios.create({
  baseURL: wpApiSettings.root + namespace,
  headers: { "X-WP-Nonce": wpApiSettings.nonce },
});

export default instance;
