module.exports = {
  "/api": {
    target: `http://localhost:${process.env.FRONT_DEV_PORT || 8080}`,
    secure: false,
  },
};
