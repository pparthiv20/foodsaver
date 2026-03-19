# GitHub Pages Documentation

This site is the landing page for the Food Saver project, deployed via GitHub Pages.

For the full application (API, dashboards, admin panel), please refer to the [main README](../README.md).

## Pages

- **Home**: Landing page with project overview
- **About**: Project information and mission
- **Features**: Key features of Food Saver
- **Contact**: Get in touch or start using the app

## Development

To modify this landing page:

1. Edit files in the `docs/` folder
2. Commit and push to GitHub
3. Changes will be live within seconds

### Structure

```
docs/
├── index.html              # Main landing page
├── assets/
│   ├── css/
│   │   └── style.css      # Styling
│   ├── js/
│   │   └── main.js        # Interactivity
│   └── images/            # Images and assets
└── README.md              # This file
```

## Local Testing

Test locally before pushing:

```bash
# Python 3
python -m http.server 8000

# Python 2
python -m SimpleHTTPServer 8000

# Node.js (with http-server)
npx http-server
```

Then visit: http://localhost:8000/docs/

---

**GitHub Pages URL**: https://your-username.github.io/food-saver-php/
