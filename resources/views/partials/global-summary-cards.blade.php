<style id="global-summary-card-system">
    :root {
        --app-summary-card-bg: #ffffff;
        --app-summary-card-border: #dbe5f2;
        --app-summary-card-text: #0f172a;
        --app-summary-card-muted: #64748b;
    }

    :where(
        .stats-grid > .stat-card,
        .facility-stat-grid > .stat-card,
        .stats-grid > .stat-box,
        .stat-grid > .stat-card,
        .metrics-grid > .metric-card,
        .incident-metrics > .metric-card,
        .history-metrics > .metric-card,
        .energy-kpis > .kpi-card,
        .eff-kpis > .eff-kpi,
        .overview-cards > .metric-card,
        .activity-overview > .overview-card,
        .notification-summary-grid > .notification-summary-card,
        .settings-summary > .settings-summary-card,
        .users-stat-grid > .users-stat-card,
        .energy-trend-page .summary-grid > .summary-card,
        .roles-stats > .roles-stat,
        .approval-summary > .approval-stat,
        .submeter-kpis > .submeter-kpi,
        .audit-metrics > .metric
    ) {
        --app-summary-accent: var(--stat-accent, #2563eb);
        --app-summary-soft: var(--stat-soft, #eff6ff);
        position: relative;
        box-sizing: border-box;
        height: 100%;
        padding: 20px !important;
        overflow: hidden;
        background: var(--app-summary-card-bg) !important;
        border: 1px solid var(--app-summary-card-border) !important;
        border-radius: 18px !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06) !important;
        color: var(--app-summary-card-text);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease !important;
    }

    :where(
        .stats-grid > .stat-card,
        .facility-stat-grid > .stat-card,
        .stats-grid > .stat-box,
        .stat-grid > .stat-card,
        .metrics-grid > .metric-card,
        .incident-metrics > .metric-card,
        .history-metrics > .metric-card,
        .energy-kpis > .kpi-card,
        .eff-kpis > .eff-kpi,
        .overview-cards > .metric-card,
        .activity-overview > .overview-card,
        .notification-summary-grid > .notification-summary-card,
        .settings-summary > .settings-summary-card,
        .users-stat-grid > .users-stat-card,
        .energy-trend-page .summary-grid > .summary-card,
        .roles-stats > .roles-stat,
        .approval-summary > .approval-stat,
        .submeter-kpis > .submeter-kpi,
        .audit-metrics > .metric
    )::before {
        content: '';
        position: absolute;
        z-index: 1;
        inset: 0 0 auto;
        height: 4px;
        background: var(--app-summary-accent);
    }

    :where(
        .stats-grid > .stat-card,
        .facility-stat-grid > .stat-card,
        .stats-grid > .stat-box,
        .stat-grid > .stat-card,
        .metrics-grid > .metric-card,
        .incident-metrics > .metric-card,
        .history-metrics > .metric-card,
        .energy-kpis > .kpi-card,
        .eff-kpis > .eff-kpi,
        .overview-cards > .metric-card,
        .activity-overview > .overview-card,
        .notification-summary-grid > .notification-summary-card,
        .settings-summary > .settings-summary-card,
        .users-stat-grid > .users-stat-card,
        .energy-trend-page .summary-grid > .summary-card,
        .roles-stats > .roles-stat,
        .approval-summary > .approval-stat,
        .submeter-kpis > .submeter-kpi,
        .audit-metrics > .metric
    ):hover {
        transform: translateY(-2px) !important;
        border-color: var(--app-summary-accent) !important;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .10) !important;
    }

    :where(
        .stats-grid,
        .facility-stat-grid,
        .stat-grid,
        .metrics-grid,
        .incident-metrics,
        .history-metrics,
        .energy-kpis,
        .eff-kpis,
        .overview-cards,
        .activity-overview,
        .notification-summary-grid,
        .settings-summary,
        .users-stat-grid,
        .roles-stats,
        .approval-summary,
        .submeter-kpis,
        .audit-metrics
    ) {
        align-items: stretch;
        gap: 16px !important;
    }

    :where(.critical, .high, .inactive, .alert, .metric-alert, .stat-needing) {
        --app-summary-accent: #e11d48;
        --app-summary-soft: #fff1f2;
    }
    :where(.active, .completed, .read, .metric-cost, .stat-completed) {
        --app-summary-accent: #16a34a;
        --app-summary-soft: #ecfdf5;
    }
    :where(.pending, .open, .unread, .maintenance, .stat-pending) {
        --app-summary-accent: #d97706;
        --app-summary-soft: #fffbeb;
    }
    :where(.ongoing, .top, .stat-ongoing) {
        --app-summary-accent: #0891b2;
        --app-summary-soft: #ecfeff;
    }
    :where(.roles-stat, .users-stat-roles) {
        --app-summary-accent: #7c3aed;
        --app-summary-soft: #f5f3ff;
    }

    .dashboard-page .stats-grid > .stat-card:nth-child(2),
    .facilities-page .stats-grid > .stat-card:nth-child(2),
    .settings-summary > .settings-summary-card:nth-child(3) { --app-summary-accent:#16a34a; --app-summary-soft:#ecfdf5; }
    .dashboard-page .stats-grid > .stat-card:nth-child(3),
    .facilities-page .stats-grid > .stat-card:nth-child(3) { --app-summary-accent:#d97706; --app-summary-soft:#fffbeb; }
    .dashboard-page .stats-grid > .stat-card:nth-child(4),
    .facilities-page .stats-grid > .stat-card:nth-child(4) { --app-summary-accent:#e11d48; --app-summary-soft:#fff1f2; }
    .dashboard-page .stats-grid > .stat-card:nth-child(5),
    .settings-summary > .settings-summary-card:nth-child(2) { --app-summary-accent:#7c3aed; --app-summary-soft:#f5f3ff; }

    .facilities-page .facility-stat-grid > .stat-card.is-selected {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 1px rgba(37, 99, 235, .18), 0 12px 26px rgba(15, 23, 42, .09) !important;
        transform: translateY(-1px) !important;
    }

    :where(
        .stat-label,
        .metric-label,
        .kpi-label,
        .eff-kpi-label,
        .overview-label,
        .facility-stat-label,
        .notification-summary-label,
        .roles-stat-label,
        .approval-stat-label
    ) {
        color: var(--app-summary-card-muted);
        font-weight: 800;
        letter-spacing: .045em;
    }

    :where(
        .stat-value,
        .metric-value,
        .kpi-value,
        .eff-kpi-value,
        .overview-value,
        .facility-stat-value,
        .notification-summary-value,
        .roles-stat-value,
        .approval-stat-value
    ) {
        color: var(--app-summary-card-text);
        font-weight: 850;
        letter-spacing: -.035em;
    }

    :where(
        .stat-label,
        .kpi-label,
        .notification-summary-label
    ) > i,
    .settings-summary-card > i {
        width: 38px;
        height: 38px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: var(--app-summary-soft) !important;
        color: var(--app-summary-accent) !important;
    }

    .facility-stat-grid > .stat-card[data-status-filter="active"] { --app-summary-accent:#16a34a; --app-summary-soft:#ecfdf5; }
    .facility-stat-grid > .stat-card[data-status-filter="maintenance"] { --app-summary-accent:#d97706; --app-summary-soft:#fffbeb; }
    .facility-stat-grid > .stat-card[data-status-filter="inactive"] { --app-summary-accent:#e11d48; --app-summary-soft:#fff1f2; }
    .facility-stat-grid .card-icon-box {
        background: var(--app-summary-soft) !important;
        color: var(--app-summary-accent) !important;
    }

    .eff-kpis > .eff-kpi.avg { --app-summary-accent:#0891b2; --app-summary-soft:#ecfeff; }
    .eff-kpis > .eff-kpi.high { --app-summary-accent:#16a34a; --app-summary-soft:#ecfdf5; }
    .eff-kpis > .eff-kpi.low { --app-summary-accent:#e11d48; --app-summary-soft:#fff1f2; }
    .eff-kpi-icon {
        background: var(--app-summary-soft) !important;
        color: var(--app-summary-accent) !important;
    }

    body.dark-mode {
        --app-summary-card-bg: #0f172a;
        --app-summary-card-border: #2a3850;
        --app-summary-card-text: #f8fafc;
        --app-summary-card-muted: #aebbd0;
    }

    body.dark-mode :where(
        .stats-grid > .stat-card,
        .facility-stat-grid > .stat-card,
        .stats-grid > .stat-box,
        .stat-grid > .stat-card,
        .metrics-grid > .metric-card,
        .incident-metrics > .metric-card,
        .history-metrics > .metric-card,
        .energy-kpis > .kpi-card,
        .eff-kpis > .eff-kpi,
        .overview-cards > .metric-card,
        .activity-overview > .overview-card,
        .notification-summary-grid > .notification-summary-card,
        .settings-summary > .settings-summary-card,
        .users-stat-grid > .users-stat-card,
        .energy-trend-page .summary-grid > .summary-card,
        .roles-stats > .roles-stat,
        .approval-summary > .approval-stat,
        .submeter-kpis > .submeter-kpi,
        .audit-metrics > .metric
    ) {
        background: var(--app-summary-card-bg) !important;
        border-color: var(--app-summary-card-border) !important;
        color: var(--app-summary-card-text) !important;
        box-shadow: 0 10px 26px rgba(2, 6, 23, .35) !important;
    }

    @media (max-width: 680px) {
        :where(
            .stats-grid > .stat-card,
            .facility-stat-grid > .stat-card,
            .stats-grid > .stat-box,
            .stat-grid > .stat-card,
            .metrics-grid > .metric-card,
            .incident-metrics > .metric-card,
            .history-metrics > .metric-card,
            .energy-kpis > .kpi-card,
            .eff-kpis > .eff-kpi,
            .overview-cards > .metric-card,
            .activity-overview > .overview-card,
            .notification-summary-grid > .notification-summary-card,
            .settings-summary > .settings-summary-card,
            .users-stat-grid > .users-stat-card,
            .energy-trend-page .summary-grid > .summary-card,
            .roles-stats > .roles-stat,
            .approval-summary > .approval-stat,
            .submeter-kpis > .submeter-kpi,
            .audit-metrics > .metric
        ) {
            min-height: auto !important;
            padding: 18px !important;
            border-radius: 15px !important;
        }
    }
</style>
