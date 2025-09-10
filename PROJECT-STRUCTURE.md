# DMT and Charts System - Project Structure

## Overview
Complete WordPress plugin system for economic data management and visualization.

## Repository Structure

```
zc-dmt/                       # Plugin root
├── admin/                    # Admin interface templates and JS
│   ├── dashboard.php
│   ├── indicators.php
│   ├── import.php
│   ├── history.php
│   ├── settings.php
│   ├── sources.php
│   └── js/
│       └── indicators.js     # Admin JS for indicators CRUD
├── includes/                 # Core PHP classes
│   ├── class-database.php
│   ├── class-data-sources.php
│   ├── class-indicators.php
│   ├── class-csv-importer.php
│   ├── class-fred-api.php
│   ├── class-rest-api.php
│   ├── class-backup.php
│   ├── class-calculations.php
│   ├── class-charts.php
│   └── class-error-logger.php
├── static-charts/            # Static chart assets (for shortcode without search/compare)
│   └── [CSS/JS as needed]
├── charts-frontend.js        # Frontend logic for dynamic charts
├── backup-admin.js           # Admin JS for backup integration
├── zc-dmt.php                # Main plugin bootstrap
├── zestra-charts.php         # Charts plugin integration point
├── README.md                 # Project overview
└── PROJECT-STRUCTURE.md      # This file
 # Documentation
```


## Plugins Communication

- **DMT Plugin**: Pure data management (sources, indicators, imports)
- **Charts Plugin**: Pure visualization (charts, dashboards, UI)
- **Communication**: REST API + WordPress hooks

## Installation Order

1. Install DMT Plugin first (handles data)
2. Install Charts Plugin second (consumes DMT data)
3. Configure data sources in DMT
4. Use chart shortcodes in pages/posts

## Development Status

🔧 **Phase 1**: DMT Plugin Core 
🔧 **Phase 2**: Charts Plugin Integration  ← Current
🔧 **Phase 3**: Advanced Features
🔧 **Phase 4**: UI/UX Polish

---
*Built for Zestra Capital by Development Team*
