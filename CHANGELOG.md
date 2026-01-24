# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-01-24

### Added
- CRUD generation for initial entities
- Repositories and translations
- Design and views for:
    - Product
    - Warehouse
    - Hotel
    - Display
    - Rack
    - Stock
    - Distribution
    - Intervention
- Quantity calculation logic for interventions
- Hotel detail view
- Intervention detail view
- CSV Stock Export functionality
- User avatar support
- Product list by rack view
- Delete confirmation popups
- Logout confirmation modal

### Changed
- Global refactoring
- Menu arrangement
- Updated README
- Removed `vendor` directory from the repository (now handled via `.gitignore`)
- Externalized export logic into dedicated services
- Cleaned up project structure (removed unused `compose.yaml` and `importmap.php`)

### Fixed
- Code style improvements (CS-Fixer)
- Git configuration (added vendor to .gitignore)
- Stock readjustment logic when an intervention is cancelled
- User roles and security configuration improvements
- Enhanced form validations for User and other entities
