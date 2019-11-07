require('dotenv').config();

// Require et pas import pour rester full CommonJS,
// sinon webpack impose à module.exports d'être en lecture seule
// or typeorm attend un module.exports, pas un export default
const { connectionOptions } = require('../schema');

module.exports = connectionOptions;
