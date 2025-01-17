const validateWordPressRequest = (req, res, next) => {
  const apiKey = req.headers['x-api-key'];
  const wpSiteUrl = req.headers['x-wp-site'];

  // Check for required headers
  if (!apiKey || !wpSiteUrl) {
    return res.status(401).json({
      error: 'Missing authentication headers'
    });
  }

  // Validate API key
  if (apiKey !== process.env.WP_API_KEY) {
    return res.status(401).json({
      error: 'Invalid API key'
    });
  }

  // Validate WordPress site URL
  if (wpSiteUrl !== process.env.WP_SITE_URL) {
    return res.status(401).json({
      error: 'Unauthorized WordPress site'
    });
  }

  next();
};

module.exports = {
  validateWordPressRequest
};