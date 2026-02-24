# Roles and Permissions Matrix

This document summarizes the current app access rules implemented in UI, middleware, and controller guards.

## Roles

- `super admin`
- `admin`
- `energy_officer`
- `staff`

## Module Access (Summary)

| Module / Action | Super Admin | Admin | Energy Officer | Staff |
|---|---:|---:|---:|---:|
| Dashboard | Yes | Yes | Yes | Yes |
| Facilities (view list/details) | Yes | Yes | Yes | Yes (assigned facilities only) |
| Facilities (create/edit/delete master data) | Yes | Yes | No | No |
| Monthly Records (view/create/update) | Yes | Yes | Yes | Yes (assigned facilities only) |
| Energy Profile (view) | Yes | Yes | Yes | Yes |
| Energy Profile (add/edit) | Yes | Yes | Yes | No |
| Energy Profile (delete) | Yes | Yes | No | No |
| Energy Profile Approval Toggle | Super Admin / Engineer only | No | No | No |
| Energy Monitoring (dashboard/trend/annual) | Yes | Yes | Yes | Yes |
| Analytics / Reports | Yes | Yes | Yes | Yes |
| Reports PDF export | Yes | Yes | Yes | Yes |
| Reports Excel/CSV export | Yes | Yes | Yes | No |
| Maintenance (view) | Yes | Yes | Yes | Yes |
| Maintenance (schedule/update) | Yes | Yes | Yes | No |
| Maintenance mark `Completed` / archive | Yes | Yes | No | No |
| Maintenance History delete | Yes | Yes | No | No |
| Users / Roles | Yes | Yes (restricted visibility) | No | No |
| System Settings | Yes | No | No | No |

## Notes

- `staff` facility access is scoped to assigned facilities via middleware (direct URL checks included).
- `energy_officer` can access facility pages, but facility master-data actions are blocked.
- `energy_officer` can add/edit energy profiles and newly created profiles are auto-approved.
- `staff` can access analytics/reports but Excel/CSV exports are blocked (PDF-only policy).
- Some checks are enforced in UI and backend (controllers/routes), not UI-only.

## Export Rules (Current)

- Blocked for `staff`:
  - `reports.energy-export` (Excel)
  - `modules.energy.export-excel` (CSV fallback)
  - `modules.energy.annual.export-excel` (CSV fallback)
- Allowed for `staff`:
  - PDF export routes (report PDFs / annual PDF)

## Recommended Future Refactor

- Normalize role values to one format (recommended: snake_case only).
- Replace scattered role checks with centralized policies/permissions.
- Add feature tests per role for critical routes and actions.
