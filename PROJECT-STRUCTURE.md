# DMT and Charts System - Project Structure

## Overview
Complete WordPress plugin system for economic data management and visualization.

## Repository Structure

```
DMT-and-Charts/
├── dmt-plugin/                 # Data Management Tool Plugin
│   ├── zc-dmt.php             # Main DMT plugin file
│   ├── includes/              # Core DMT classes
│   ├── admin/                 # Admin interface
│   ├── assets/               # DMT specific assets
│   └── templates/            # DMT templates
│
├── charts-plugin/             # Charts System Plugin  
│   ├── zc-charts.php         # Main Charts plugin file
│   ├── includes/             # Core Charts classes
│   ├── assets/               # Charts assets (React, CSS)
│   │   ├── js/               # Your existing Chart.js + React code
│   │   └── css/              # Chart styling
│   └── templates/            # Chart templates
│
├── shared/                   # Shared utilities (if any)
└── docs/                     # Documentation
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

🔧 **Phase 1**: DMT Plugin Core ← Current
🔧 **Phase 2**: Charts Plugin Integration  
🔧 **Phase 3**: Advanced Features
🔧 **Phase 4**: UI/UX Polish

---
*Built for Zestra Capital by Development Team*
