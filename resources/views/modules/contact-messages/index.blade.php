@extends('layouts.qc-admin')
@section('title', 'Contact Inbox')

@section('content')
<style>
    .contact-inbox-page {
        display: grid;
        gap: 16px;
    }
    .contact-inbox-header {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    }
    .ci-flash {
        margin-top: 12px;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 0.86rem;
        font-weight: 600;
        border: 1px solid;
    }
    .ci-flash.success {
        background: #ecfdf3;
        border-color: #86efac;
        color: #166534;
    }
    .ci-flash.error {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #991b1b;
    }
    .contact-inbox-title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
    }
    .contact-inbox-subtitle {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 0.9rem;
    }
    .contact-tab-row {
        margin-top: 12px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .contact-tab-chip {
        border: 1px solid #dbe2ea;
        background: #fff;
        color: #334155;
        border-radius: 999px;
        padding: 7px 12px;
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .contact-tab-chip.active {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .contact-tab-chip .count {
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.06);
        padding: 1px 6px;
        font-size: 0.68rem;
    }
    .contact-tab-chip.active .count {
        background: rgba(37, 99, 235, 0.12);
    }
    .contact-inbox-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }
    .ci-stat {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 12px;
        padding: 10px 12px;
    }
    .ci-stat-label {
        margin: 0;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .ci-stat-value {
        margin: 4px 0 0;
        color: #0f172a;
        font-size: 1.15rem;
        font-weight: 800;
    }
    .contact-inbox-layout {
        display: grid;
        grid-template-columns: 390px minmax(0, 1fr);
        gap: 16px;
        min-height: 560px;
        height: calc(100vh - 270px);
        max-height: calc(100vh - 210px);
        align-items: stretch;
    }
    .contact-inbox-layout.tab-hidden {
        display: none;
    }
    .contact-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .contact-panel-head {
        padding: 12px;
        border-bottom: 1px solid #eef2f7;
        background: #fcfdff;
    }
    .contact-search {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
    }
    .contact-search input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.9rem;
    }
    .contact-search button,
    .contact-search a {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 74px;
    }
    .contact-filters {
        margin-top: 10px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .contact-filter-chip {
        border: 1px solid #dbe2ea;
        background: #fff;
        color: #334155;
        border-radius: 999px;
        padding: 6px 10px;
        text-decoration: none;
        font-size: 0.74rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .contact-filter-chip.active {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .contact-filter-chip-count {
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.06);
        padding: 1px 6px;
        font-size: 0.68rem;
    }
    .contact-filter-chip.active .contact-filter-chip-count {
        background: rgba(37, 99, 235, 0.12);
    }
    .contact-sort-row {
        margin-top: 8px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .contact-sort-label {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        margin-right: 2px;
    }
    .contact-sort-chip {
        border: 1px solid #dbe2ea;
        background: #fff;
        color: #334155;
        border-radius: 999px;
        padding: 5px 9px;
        text-decoration: none;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .contact-sort-chip.active {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .contact-list {
        max-height: none;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
    }
    .contact-item {
        display: block;
        text-decoration: none;
        color: inherit;
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s ease;
    }
    .contact-item:hover {
        background: #f8fafc;
    }
    .contact-item.active {
        background: #eff6ff;
        border-left: 3px solid #2563eb;
        padding-left: 9px;
    }
    .contact-item.unread {
        background: #f8fbff;
    }
    .contact-item-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 3px;
    }
    .contact-item-name-wrap {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }
    .contact-item-new {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 2px 6px;
        font-size: 0.62rem;
        font-weight: 800;
        color: #b45309;
        background: #fffbeb;
        border: 1px solid #fde68a;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .contact-item-unread-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #2563eb;
        display: inline-block;
        flex: 0 0 auto;
    }
    .contact-item-name {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
    }
    .contact-item-date {
        color: #64748b;
        font-size: 0.72rem;
        white-space: nowrap;
    }
    .contact-item-preview {
        margin: 0;
        color: #475569;
        font-size: 0.78rem;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .contact-item-status {
        margin-top: 6px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 0.7rem;
        font-weight: 700;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .contact-item-status.warn {
        border-color: #fde68a;
        background: #fffbeb;
        color: #92400e;
    }
    .contact-item-replies {
        margin-top: 6px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 0.68rem;
        font-weight: 700;
        color: #334155;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .contact-empty {
        padding: 24px 16px;
        text-align: center;
        color: #64748b;
        font-size: 0.9rem;
    }
    .sent-replies-layout {
        display: grid;
        gap: 16px;
    }
    .sent-reply-list {
        display: grid;
        gap: 12px;
        padding: 14px;
    }
    .sent-reply-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        padding: 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }
    .sent-reply-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }
    .sent-reply-subject {
        margin: 0;
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 800;
    }
    .sent-reply-meta {
        margin: 0;
        color: #64748b;
        font-size: 0.76rem;
        line-height: 1.35;
    }
    .sent-reply-body {
        margin: 10px 0 0;
        color: #0f172a;
        font-size: 0.82rem;
        line-height: 1.45;
        white-space: pre-wrap;
        word-break: break-word;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .sent-reply-actions {
        margin-top: 10px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .sent-reply-link {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        border-radius: 10px;
        padding: 6px 10px;
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .contact-detail {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 0;
        overflow: hidden;
        flex: 1;
    }
    .detail-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
        flex: 1;
    }
    .detail-card-head {
        padding: 14px 14px 10px;
        border-bottom: 1px solid #f1f5f9;
        background: #fcfdff;
        flex-shrink: 0;
    }
    .detail-title {
        margin: 0 0 4px;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }
    .detail-meta {
        margin: 0;
        color: #64748b;
        font-size: 0.8rem;
    }
    .detail-actions {
        margin-top: 10px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .detail-action-btn {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        color: #334155;
        padding: 8px 12px;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .detail-action-btn.primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }
    .detail-action-btn:hover {
        background: #f8fafc;
    }
    .detail-action-btn.primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }
    .detail-action-form {
        margin: 0;
        display: inline-flex;
    }
    .thread-scroll-body {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        background: #fff;
        scroll-behavior: smooth;
    }
    .detail-collapse {
        margin: 0;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
    }
    .detail-collapse summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 12px 14px;
        font-weight: 800;
        color: #0f172a;
    }
    .detail-collapse summary::-webkit-details-marker {
        display: none;
    }
    .detail-collapse-meta {
        color: #64748b;
        font-size: 0.74rem;
        font-weight: 600;
        text-align: right;
    }
    .detail-collapse-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 999px;
        border: 1px solid #dbe2ea;
        background: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        flex: 0 0 auto;
    }
    .detail-collapse[open] .detail-collapse-icon {
        transform: rotate(180deg);
    }
    .detail-collapse summary:focus-visible {
        outline: 2px solid #93c5fd;
        outline-offset: -2px;
        border-radius: 8px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 14px;
    }
    .detail-field {
        border: 1px solid #edf2f7;
        border-radius: 10px;
        background: #f8fafc;
        padding: 10px;
    }
    .detail-label {
        margin: 0 0 4px;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .detail-value {
        margin: 0;
        color: #0f172a;
        font-size: 0.84rem;
        line-height: 1.35;
        word-break: break-word;
    }
    .detail-message-wrap {
        padding: 14px;
    }
    .detail-message-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        color: #0f172a;
        line-height: 1.55;
        white-space: pre-wrap;
        word-break: break-word;
        min-height: 180px;
    }
    .detail-notes {
        padding: 14px;
        border-top: 1px solid #f1f5f9;
        background: #fcfdff;
        color: #475569;
        font-size: 0.8rem;
    }
    .detail-notes strong {
        color: #0f172a;
    }
    .conversation-wrap {
        padding: 14px;
        border-top: 1px solid #f1f5f9;
        background: #fff;
        overflow: visible;
        max-height: none;
    }
    .conversation-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .conversation-head-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .conversation-title {
        margin: 0;
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 800;
    }
    .conversation-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: 0.72rem;
        font-weight: 700;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .conversation-toggle-link {
        border: 1px solid #dbe2ea;
        background: #fff;
        color: #334155;
        border-radius: 999px;
        padding: 5px 9px;
        text-decoration: none;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .conversation-toggle-link.active {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .conversation-hint {
        margin: 0 0 8px;
        color: #64748b;
        font-size: 0.74rem;
    }
    .conversation-list {
        display: grid;
        gap: 12px;
    }
    .conversation-item {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr);
        gap: 10px;
        align-items: start;
    }
    .conversation-avatar {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.67rem;
        font-weight: 800;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .conversation-avatar.reply {
        border-color: #bfdbfe;
        background: #dbeafe;
    }
    .conversation-bubble {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        padding: 12px;
    }
    .conversation-bubble.reply {
        border-color: #bfdbfe;
        background: #eff6ff;
    }
    .conversation-bubble.latest-reply {
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.14);
    }
    .conversation-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }
    .conversation-author {
        margin: 0;
        color: #0f172a;
        font-size: 0.8rem;
        font-weight: 800;
    }
    .conversation-submeta {
        margin: 0;
        color: #64748b;
        font-size: 0.72rem;
        line-height: 1.35;
    }
    .conversation-body {
        margin: 0;
        color: #0f172a;
        font-size: 0.82rem;
        line-height: 1.5;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .conversation-empty {
        margin: 0;
        color: #64748b;
        font-size: 0.8rem;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        background: #f8fafc;
        padding: 10px 12px;
    }
    .detail-message-wrap,
    .reply-history-wrap {
        display: none;
    }
    .reply-history-wrap {
        padding: 14px;
        border-top: 1px solid #f1f5f9;
        background: #fff;
    }
    .reply-history-title {
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 800;
    }
    .reply-history-list {
        display: grid;
        gap: 10px;
    }
    .reply-history-item {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
        padding: 12px;
    }
    .reply-history-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
        flex-wrap: wrap;
    }
    .reply-history-subject {
        margin: 0;
        color: #0f172a;
        font-size: 0.84rem;
        font-weight: 800;
    }
    .reply-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        padding: 3px 8px;
        font-size: 0.7rem;
        font-weight: 700;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .reply-status-pill.failed {
        border-color: #fca5a5;
        background: #fef2f2;
        color: #991b1b;
    }
    .reply-history-meta {
        margin: 0 0 8px;
        color: #64748b;
        font-size: 0.75rem;
    }
    .reply-history-body {
        margin: 0;
        color: #0f172a;
        font-size: 0.82rem;
        line-height: 1.45;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .reply-attachments {
        margin-top: 8px;
        display: grid;
        gap: 6px;
    }
    .reply-attachments-title {
        margin: 0;
        color: #475569;
        font-size: 0.73rem;
        font-weight: 700;
    }
    .reply-attachments-list {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .reply-attachment-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 5px 9px;
        text-decoration: none;
        font-size: 0.73rem;
        font-weight: 700;
    }
    .reply-form-wrap {
        padding: 14px;
        border-top: 1px solid #f1f5f9;
        background: #fcfdff;
        position: static;
        box-shadow: 0 -8px 18px rgba(15, 23, 42, 0.04);
        flex-shrink: 0;
        max-height: none;
        overflow: visible;
    }
    .reply-composer-collapse {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: none;
    }
    .reply-composer-collapse summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 12px;
        font-weight: 800;
        color: #0f172a;
        background: #f8fafc;
        border-bottom: 1px solid transparent;
    }
    .reply-composer-collapse summary::-webkit-details-marker {
        display: none;
    }
    .reply-composer-collapse[open] summary {
        border-bottom-color: #e2e8f0;
        background: #fcfdff;
    }
    .reply-composer-meta {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .reply-composer-chevron {
        display: inline-flex;
        width: 20px;
        height: 20px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid #dbe2ea;
        background: #fff;
        color: #475569;
        font-size: 0.7rem;
    }
    .reply-composer-collapse[open] .reply-composer-chevron {
        transform: rotate(180deg);
    }
    .reply-composer-body {
        padding: 12px;
        background: #fcfdff;
        overflow-y: auto;
        max-height: min(38vh, 300px);
    }
    .reply-form-title {
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 800;
    }
    .reply-form-grid {
        display: grid;
        gap: 10px;
    }
    .reply-field label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .reply-field input,
    .reply-field textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.86rem;
        color: #0f172a;
        background: #fff;
    }
    .reply-field textarea {
        min-height: 110px;
        resize: vertical;
        line-height: 1.45;
    }
    .reply-field input[type="file"] {
        padding: 8px;
        background: #fff;
    }
    .reply-field .reply-error-text {
        margin-top: 6px;
        color: #b91c1c;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .reply-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        position: sticky;
        bottom: 0;
        background: #fcfdff;
        padding-top: 8px;
        margin-top: 2px;
        border-top: 1px solid #eef2f7;
    }
    .reply-form-note {
        color: #64748b;
        font-size: 0.75rem;
    }
    .reply-attachment-note {
        margin-top: 6px;
        color: #64748b;
        font-size: 0.72rem;
        line-height: 1.35;
    }
    .reply-send-btn {
        border: 1px solid #2563eb;
        background: #2563eb;
        color: #fff;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.84rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .reply-send-btn:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }
    .ci-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px;
        border-top: 1px solid #eef2f7;
        background: #fcfdff;
    }
    .ci-pagination-meta {
        color: #64748b;
        font-size: 0.78rem;
    }
    .ci-pagination-actions {
        display: flex;
        gap: 8px;
    }
    .ci-page-btn {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        color: #334155;
        padding: 7px 10px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
    }
    .ci-page-btn.disabled {
        opacity: .45;
        pointer-events: none;
    }
    @media (max-width: 1100px) {
        .contact-inbox-layout {
            grid-template-columns: 1fr;
            height: auto;
            max-height: none;
        }
        .contact-list {
            max-height: 360px;
            flex: initial;
        }
        .conversation-wrap {
            max-height: none;
        }
        .reply-form-wrap {
            position: static;
            box-shadow: none;
            max-height: none;
            overflow: visible;
        }
        .reply-composer-collapse {
            max-height: none;
        }
        .reply-composer-body {
            max-height: none;
            overflow: visible;
        }
        .reply-form-actions {
            position: static;
            border-top: none;
            padding-top: 0;
            margin-top: 0;
        }
    }
    @media (max-width: 640px) {
        .contact-inbox-stats {
            grid-template-columns: 1fr;
        }
        .detail-grid {
            grid-template-columns: 1fr;
        }
        .contact-search {
            grid-template-columns: 1fr;
        }
    }

    body.dark-mode .contact-inbox-header,
    body.dark-mode .contact-panel,
    body.dark-mode .detail-card {
        background: #111827 !important;
        border-color: #334155 !important;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.22);
    }

    body.dark-mode .contact-panel-head,
    body.dark-mode .detail-card-head,
    body.dark-mode .reply-form-wrap,
    body.dark-mode .reply-composer-collapse,
    body.dark-mode .reply-composer-collapse summary,
    body.dark-mode .reply-composer-body,
    body.dark-mode .ci-pagination,
    body.dark-mode .thread-scroll-body,
    body.dark-mode .conversation-wrap,
    body.dark-mode .reply-history-wrap {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    body.dark-mode .contact-inbox-title,
    body.dark-mode .detail-title,
    body.dark-mode .conversation-title,
    body.dark-mode .contact-item-name,
    body.dark-mode .conversation-author,
    body.dark-mode .detail-collapse summary,
    body.dark-mode .reply-composer-collapse summary,
    body.dark-mode .reply-field label,
    body.dark-mode .reply-history-title,
    body.dark-mode .reply-history-subject,
    body.dark-mode .detail-label {
        color: #e5e7eb !important;
    }

    body.dark-mode .contact-inbox-subtitle,
    body.dark-mode .detail-meta,
    body.dark-mode .conversation-submeta,
    body.dark-mode .contact-item-date,
    body.dark-mode .contact-sort-label,
    body.dark-mode .reply-composer-meta,
    body.dark-mode .reply-form-note,
    body.dark-mode .reply-attachment-note,
    body.dark-mode .ci-pagination-meta,
    body.dark-mode .contact-empty,
    body.dark-mode .conversation-hint,
    body.dark-mode .detail-collapse-meta,
    body.dark-mode .reply-history-meta {
        color: #94a3b8 !important;
    }

    body.dark-mode .contact-search input,
    body.dark-mode .reply-field input,
    body.dark-mode .reply-field textarea,
    body.dark-mode .reply-field input[type="file"] {
        background: #0b1220 !important;
        color: #e5e7eb !important;
        border-color: #334155 !important;
        color-scheme: dark;
    }

    body.dark-mode .contact-search input::placeholder,
    body.dark-mode .reply-field textarea::placeholder {
        color: #64748b !important;
    }

    body.dark-mode .contact-search button,
    body.dark-mode .contact-search a {
        background: #111827 !important;
        color: #e5e7eb !important;
        border-color: #334155 !important;
    }

    body.dark-mode .contact-search button:hover,
    body.dark-mode .contact-search a:hover {
        background: #0f172a !important;
        border-color: #475569 !important;
    }

    body.dark-mode .ci-flash.success {
        background: rgba(22, 101, 52, 0.18) !important;
        border-color: rgba(74, 222, 128, 0.35) !important;
        color: #bbf7d0 !important;
    }

    body.dark-mode .ci-flash.error {
        background: rgba(127, 29, 29, 0.22) !important;
        border-color: rgba(248, 113, 113, 0.35) !important;
        color: #fecaca !important;
    }

    body.dark-mode .contact-item {
        border-bottom-color: #1f2937 !important;
    }

    body.dark-mode .contact-item:hover {
        background: #0f172a !important;
    }

    body.dark-mode .contact-item.unread {
        background: #0b1323 !important;
    }

    body.dark-mode .contact-item.active {
        background: #10213f !important;
        border-left-color: #60a5fa !important;
    }

    body.dark-mode .contact-item-preview,
    body.dark-mode .detail-value,
    body.dark-mode .conversation-body,
    body.dark-mode .contact-filter-chip-count {
        color: #cbd5e1 !important;
    }

    body.dark-mode .contact-filter-chip-count {
        background: rgba(148, 163, 184, 0.14) !important;
    }

    body.dark-mode .contact-filter-chip.active .contact-filter-chip-count,
    body.dark-mode .contact-sort-chip.active .contact-filter-chip-count {
        background: rgba(59, 130, 246, 0.22) !important;
        color: #bfdbfe !important;
    }

    body.dark-mode .contact-item-status,
    body.dark-mode .conversation-count,
    body.dark-mode .reply-status-pill,
    body.dark-mode .reply-attachment-link,
    body.dark-mode .contact-filter-chip.active,
    body.dark-mode .contact-sort-chip.active {
        background: #1e3a5f !important;
        color: #bfdbfe !important;
        border-color: #3b82f6 !important;
    }

    body.dark-mode .contact-item-status.warn,
    body.dark-mode .reply-status-pill.failed {
        background: #3a2410 !important;
        color: #fcd34d !important;
        border-color: #92400e !important;
    }

    body.dark-mode .contact-item-new {
        background: #2b2412 !important;
        color: #fde68a !important;
        border-color: #6b4f1d !important;
    }

    body.dark-mode .contact-item-replies,
    body.dark-mode .contact-filter-chip,
    body.dark-mode .contact-sort-chip,
    body.dark-mode .ci-page-btn,
    body.dark-mode .conversation-toggle-link,
    body.dark-mode .reply-composer-chevron {
        background: #111827 !important;
        color: #e5e7eb !important;
        border-color: #334155 !important;
    }

    body.dark-mode .detail-field,
    body.dark-mode .conversation-bubble,
    body.dark-mode .conversation-empty,
    body.dark-mode .detail-collapse,
    body.dark-mode .detail-notes,
    body.dark-mode .detail-message-box,
    body.dark-mode .reply-history-item {
        background: #0f172a !important;
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }

    body.dark-mode .conversation-wrap,
    body.dark-mode .reply-history-wrap,
    body.dark-mode .detail-notes {
        border-top-color: #334155 !important;
    }

    body.dark-mode .detail-collapse-icon {
        background: #111827 !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .detail-collapse summary:hover,
    body.dark-mode .reply-composer-collapse summary:hover {
        background: #111827 !important;
    }

    body.dark-mode .detail-collapse summary:focus-visible,
    body.dark-mode .reply-composer-collapse summary:focus-visible,
    body.dark-mode .contact-search input:focus-visible,
    body.dark-mode .reply-field input:focus-visible,
    body.dark-mode .reply-field textarea:focus-visible {
        outline-color: #60a5fa !important;
    }

    body.dark-mode .conversation-bubble.reply {
        background: #10213f !important;
        border-color: #2f4f7f !important;
    }

    body.dark-mode .conversation-avatar {
        background: #0f172a !important;
        color: #93c5fd !important;
        border-color: #334155 !important;
    }

    body.dark-mode .conversation-avatar.reply {
        background: #10213f !important;
        border-color: #2f4f7f !important;
    }

    body.dark-mode .reply-form-actions {
        background: #0f172a !important;
        border-top-color: #334155 !important;
    }

    body.dark-mode .reply-field input[type="file"]::file-selector-button {
        background: #1f2937;
        color: #e5e7eb;
        border: 1px solid #334155;
        border-radius: 8px;
        padding: 6px 10px;
        margin-right: 8px;
        cursor: pointer;
    }

    body.dark-mode .reply-send-btn {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #fff !important;
    }

    body.dark-mode .reply-send-btn:hover {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }
</style>

<div class="contact-inbox-page">
    <section class="contact-inbox-header">
        <h1 class="contact-inbox-title">Contact Inbox</h1>
        <p class="contact-inbox-subtitle">
            Messages submitted from your website Contact form are stored here in the system (DB inbox).
        </p>
        @if(session('inbox_success'))
            <div class="ci-flash success">{{ session('inbox_success') }}</div>
        @endif

        @if(session('reply_success'))
            <div class="ci-flash success">{{ session('reply_success') }}</div>
        @endif
        @if(session('reply_error'))
            <div class="ci-flash error">{{ session('reply_error') }}</div>
        @endif

    </section>

    <section class="contact-inbox-layout">
        <div class="contact-panel">
            <div class="contact-panel-head">
                <form method="GET" action="{{ route('modules.contact-messages.index') }}" class="contact-search">
                    <input type="hidden" name="tab" value="inbox">
                    <input type="hidden" name="filter" value="{{ $filter ?? 'all' }}">
                    <input type="hidden" name="sort" value="{{ $sort ?? 'latest_activity' }}">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search name, email, subject, or message..."
                    >
                    <button type="submit">Search</button>
                    @if($search !== '')
                        <a href="{{ route('modules.contact-messages.index', array_filter(['tab' => 'inbox', 'filter' => $filter ?? 'all', 'sort' => $sort ?? 'latest_activity'])) }}">Clear</a>
                    @endif
                </form>
                <div class="contact-filters">
                    @php
                        $filterLabels = [
                            'all' => 'All',
                            'unread' => 'Unread',
                            'replied' => 'Has Replies',
                            'failed' => 'Failed Sends',
                        ];
                    @endphp
                    @foreach($filterLabels as $filterKey => $filterLabel)
                        <a
                            href="{{ route('modules.contact-messages.index', array_filter(['tab' => 'inbox', 'filter' => $filterKey, 'sort' => $sort ?? 'latest_activity', 'q' => $search !== '' ? $search : null])) }}"
                            class="contact-filter-chip{{ ($filter ?? 'all') === $filterKey ? ' active' : '' }}"
                        >
                            {{ $filterLabel }}
                            <span class="contact-filter-chip-count">{{ (int) (($filterCounts[$filterKey] ?? 0)) }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="contact-sort-row">
                    <span class="contact-sort-label">Sort:</span>
                    @php
                        $sortOptions = [
                            'latest_activity' => 'Latest Activity',
                            'latest_incoming' => 'Latest Incoming',
                            'oldest' => 'Oldest',
                        ];
                    @endphp
                    @foreach($sortOptions as $sortKey => $sortLabel)
                        <a
                            href="{{ route('modules.contact-messages.index', array_filter(['tab' => 'inbox', 'filter' => $filter ?? 'all', 'sort' => $sortKey, 'q' => $search !== '' ? $search : null])) }}"
                            class="contact-sort-chip{{ ($sort ?? 'latest_activity') === $sortKey ? ' active' : '' }}"
                        >
                            {{ $sortLabel }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="contact-list">
                @forelse($messages as $message)
                    @php
                        $isSelected = (int) ($selectedMessage->id ?? 0) === (int) $message->id;
                        $itemUrl = route('modules.contact-messages.index', array_merge(request()->query(), ['message' => $message->id]));
                        $deliveryWarn = filled($message->email_error) && blank($message->emailed_at);
                        $isUnread = blank($message->read_at);
                        $isNewIncoming = $isUnread && optional($message->created_at)?->greaterThanOrEqualTo(now()->subDay());
                    @endphp
                    <a href="{{ $itemUrl }}" class="contact-item{{ $isSelected ? ' active' : '' }}{{ $isUnread ? ' unread' : '' }}">
                        <div class="contact-item-top">
                            <div class="contact-item-name-wrap">
                                @if($isUnread)
                                    <span class="contact-item-unread-dot" title="Unread"></span>
                                @endif
                                <p class="contact-item-name">{{ $message->name }}</p>
                                @if($isNewIncoming)
                                    <span class="contact-item-new">New</span>
                                @endif
                            </div>
                            <span class="contact-item-date">{{ $message->created_at?->timezone('Asia/Manila')->format('M d, h:i A') }}</span>
                        </div>
                        <p class="contact-item-preview">{{ $message->email }}</p>
                        <span class="contact-item-status{{ $deliveryWarn ? ' warn' : '' }}">
                            {{ $isUnread ? 'Unread' : 'Read' }} | {{ $deliveryWarn ? 'Email notify failed' : 'Saved in system' }}
                        </span>
                        @if(($message->replies_count ?? 0) > 0)
                            <span class="contact-item-replies">
                                <i class="fa-solid fa-paper-plane"></i>
                                {{ (int) $message->replies_count }} repl{{ (int) $message->replies_count === 1 ? 'y' : 'ies' }}
                            </span>
                        @endif
                    </a>
                @empty
                    <div class="contact-empty">
                        No contact messages found{{ $search !== '' ? ' for this search' : '' }}.
                    </div>
                @endforelse
            </div>

            <div class="ci-pagination">
                <div class="ci-pagination-meta">
                    Page {{ $messages->currentPage() }} of {{ $messages->lastPage() }}
                    ({{ number_format($messages->total()) }} total)
                </div>
                <div class="ci-pagination-actions">
                    <a href="{{ $messages->previousPageUrl() ?: '#' }}" class="ci-page-btn{{ $messages->onFirstPage() ? ' disabled' : '' }}">Previous</a>
                    <a href="{{ $messages->nextPageUrl() ?: '#' }}" class="ci-page-btn{{ $messages->hasMorePages() ? '' : ' disabled' }}">Next</a>
                </div>
            </div>
        </div>

        <div class="contact-panel">
            @if($selectedMessage)
                @php
                    $replySubject = 'Re: ' . ($selectedMessage->subject ?: 'Your message to LGU Energy System');
                    $replyBody = "Hello {$selectedMessage->name},\n\nThank you for your message.\n\n--- Your message ---\n" . $selectedMessage->message . "\n\nRegards,\nLGU Energy Team";
                @endphp
                <div class="contact-detail">
                    <div class="detail-card">
                        <div class="detail-card-head">
                            <h2 class="detail-title">{{ $selectedMessage->subject ?: 'No subject' }}</h2>
                            <p class="detail-meta">
                                Received {{ $selectedMessage->created_at?->timezone('Asia/Manila')->format('F d, Y h:i A') }}
                            </p>
                        </div>

                        <div class="thread-scroll-body" id="threadScrollBody">
                            <details class="detail-collapse">
                                <summary>
                                    <span>Message Details</span>
                                    <span class="detail-collapse-meta">
                                        {{ $selectedMessage->name }} | {{ $selectedMessage->email }}
                                        <span class="detail-collapse-icon"><i class="fa-solid fa-chevron-down"></i></span>
                                    </span>
                                </summary>
                                <div class="detail-grid">
                                    <div class="detail-field">
                                        <p class="detail-label">Sender Name</p>
                                        <p class="detail-value">{{ $selectedMessage->name }}</p>
                                    </div>
                                    <div class="detail-field">
                                        <p class="detail-label">Sender Email</p>
                                        <p class="detail-value">
                                            <a href="mailto:{{ $selectedMessage->email }}">{{ $selectedMessage->email }}</a>
                                        </p>
                                    </div>
                                    <div class="detail-field">
                                        <p class="detail-label">Read Status</p>
                                        <p class="detail-value">
                                            @if($selectedMessage->read_at)
                                                Read {{ $selectedMessage->read_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                            @else
                                                Unread
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </details>

                        @php
                            $sortedReplies = ($selectedMessage->replies ?? collect())->sortBy('created_at')->values();
                            $latestReplyId = optional($sortedReplies->last())->id;
                            $conversationCount = 1 + $sortedReplies->count();
                            $threadView = request('thread_view', 'recent');
                            $threadView = in_array($threadView, ['recent', 'all'], true) ? $threadView : 'recent';
                            $recentReplyLimit = 5;
                            $visibleReplies = $sortedReplies;
                            if ($threadView !== 'all' && $sortedReplies->count() > $recentReplyLimit) {
                                $visibleReplies = $sortedReplies->slice($sortedReplies->count() - $recentReplyLimit)->values();
                            }
                            $hiddenReplyCount = max(0, $sortedReplies->count() - $visibleReplies->count());
                            $showAllThreadUrl = request()->fullUrlWithQuery(['thread_view' => 'all']);
                            $showRecentThreadUrl = request()->fullUrlWithQuery(['thread_view' => 'recent']);
                        @endphp

                        <div class="conversation-wrap" id="conversationThread">
                            <div class="conversation-head">
                                <h3 class="conversation-title">Conversation</h3>
                                <div class="conversation-head-actions">
                                    <span class="conversation-count">
                                        <i class="fa-solid fa-comments"></i>
                                        {{ $conversationCount }} message{{ $conversationCount === 1 ? '' : 's' }}
                                    </span>
                                    <a href="{{ $showRecentThreadUrl }}" class="conversation-toggle-link{{ $threadView === 'recent' ? ' active' : '' }}">
                                        <i class="fa-solid fa-clock"></i> Recent
                                    </a>
                                    <a href="{{ $showAllThreadUrl }}" class="conversation-toggle-link{{ $threadView === 'all' ? ' active' : '' }}">
                                        <i class="fa-solid fa-list"></i> Show All
                                    </a>
                                </div>
                            </div>

                            @if($hiddenReplyCount > 0 && $threadView !== 'all')
                                <p class="conversation-hint">
                                    Showing latest {{ $visibleReplies->count() }} replies. {{ $hiddenReplyCount }} older repl{{ $hiddenReplyCount === 1 ? 'y is' : 'ies are' }} hidden to reduce scrolling.
                                </p>
                            @endif

                            <div class="conversation-list">
                                <div class="conversation-item">
                                    <div class="conversation-avatar">IN</div>
                                    <div class="conversation-bubble">
                                        <div class="conversation-meta">
                                            <p class="conversation-author">{{ $selectedMessage->name }} (Original Message)</p>
                                            <p class="conversation-submeta">{{ $selectedMessage->created_at?->timezone('Asia/Manila')->format('M d, Y h:i A') }}</p>
                                        </div>
                                        <p class="conversation-submeta" style="margin:0 0 8px;">{{ $selectedMessage->email }}</p>
                                        <p class="conversation-body">{{ $selectedMessage->message }}</p>
                                    </div>
                                </div>

                                @forelse($visibleReplies as $reply)
                                    <div class="conversation-item" id="{{ (int) $reply->id === (int) $latestReplyId ? 'latestReplyCard' : '' }}">
                                        <div class="conversation-avatar reply">RE</div>
                                        <div class="conversation-bubble reply{{ (int) $reply->id === (int) $latestReplyId ? ' latest-reply' : '' }}">
                                            <div class="conversation-meta">
                                                <p class="conversation-author">
                                                    Your Reply
                                                    @if($reply->sender)
                                                        ({{ $reply->sender->full_name ?? $reply->sender->name ?? $reply->sender->username }})
                                                    @endif
                                                </p>
                                                <span class="reply-status-pill{{ $reply->send_status === 'failed' ? ' failed' : '' }}">
                                                    {{ strtoupper($reply->send_status ?? 'sent') }}
                                                </span>
                                            </div>
                                            <p class="conversation-submeta" style="margin:0 0 8px;">
                                                Subject: {{ $reply->subject }} | To: {{ $reply->recipient_email }} | {{ optional($reply->sent_at ?? $reply->created_at)?->timezone('Asia/Manila')?->format('M d, Y h:i A') }}
                                            </p>
                                            <p class="conversation-body">{{ $reply->message }}</p>

                                            @if(!empty($reply->attachments))
                                                <div class="reply-attachments">
                                                    <p class="reply-attachments-title">Attachments</p>
                                                    <div class="reply-attachments-list">
                                                        @foreach(($reply->attachments ?? []) as $attachment)
                                                            @php
                                                                $attachmentPath = (string) ($attachment['path'] ?? '');
                                                            @endphp
                                                            @if($attachmentPath !== '')
                                                                <a href="{{ asset('storage/' . ltrim($attachmentPath, '/')) }}" target="_blank" class="reply-attachment-link">
                                                                    <i class="fa-solid fa-paperclip"></i>
                                                                    {{ $attachment['original_name'] ?? basename($attachmentPath) }}
                                                                </a>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($reply->send_status === 'failed' && $reply->error_message)
                                                <div class="detail-notes" style="margin-top:8px;border-top:none;border:1px solid #fecaca;background:#fff1f2;padding:10px 12px;border-radius:10px;">
                                                    <strong>Send Error:</strong> {{ $reply->error_message }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="conversation-empty">No replies yet. Use "Reply In System" below to send your first reply.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="detail-message-wrap">
                            <div class="detail-label" style="margin-bottom:8px;">Message</div>
                            <div class="detail-message-box">{{ $selectedMessage->message }}</div>
                        </div>

                        @if(($selectedMessage->replies?->count() ?? 0) > 0)
                            <div class="reply-history-wrap">
                                <h3 class="reply-history-title">Reply History</h3>
                                <div class="reply-history-list">
                                    @foreach($selectedMessage->replies->sortBy('created_at') as $reply)
                                        <div class="reply-history-item">
                                            <div class="reply-history-head">
                                                <p class="reply-history-subject">{{ $reply->subject }}</p>
                                                <span class="reply-status-pill{{ $reply->send_status === 'failed' ? ' failed' : '' }}">
                                                    {{ strtoupper($reply->send_status ?? 'sent') }}
                                                </span>
                                            </div>
                                            <p class="reply-history-meta">
                                                By {{ $reply->sender?->full_name ?? $reply->sender?->name ?? $reply->sender?->username ?? 'System' }}
                                                • To {{ $reply->recipient_email }}
                                                • {{ optional($reply->sent_at ?? $reply->created_at)?->timezone('Asia/Manila')?->format('M d, Y h:i A') }}
                                            </p>
                                            <p class="reply-history-body">{{ $reply->message }}</p>

                                            @if(!empty($reply->attachments))
                                                <div class="reply-attachments">
                                                    <p class="reply-attachments-title">Attachments</p>
                                                    <div class="reply-attachments-list">
                                                        @foreach(($reply->attachments ?? []) as $attachment)
                                                            @php
                                                                $attachmentPath = (string) ($attachment['path'] ?? '');
                                                            @endphp
                                                            @if($attachmentPath !== '')
                                                                <a href="{{ asset('storage/' . ltrim($attachmentPath, '/')) }}" target="_blank" class="reply-attachment-link">
                                                                    <i class="fa-solid fa-paperclip"></i>
                                                                    {{ $attachment['original_name'] ?? basename($attachmentPath) }}
                                                                </a>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($reply->send_status === 'failed' && $reply->error_message)
                                                <div class="detail-notes" style="margin-top:8px;border-top:none;border:1px solid #fecaca;background:#fff1f2;">
                                                    <strong>Send Error:</strong> {{ $reply->error_message }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($selectedMessage->email_error)
                            <div class="detail-notes">
                                <strong>Email Notification Error:</strong>
                                {{ $selectedMessage->email_error }}
                            </div>
                        @endif
                        </div>

                        @php
                            $replyComposerOpen = session('reply_error')
                                || $errors->has('reply_subject')
                                || $errors->has('reply_message')
                                || $errors->has('reply_attachments')
                                || $errors->has('reply_attachments.*');
                        @endphp
                        <div class="reply-form-wrap">
                            <details class="reply-composer-collapse" id="replyComposer" {{ $replyComposerOpen ? 'open' : '' }}>
                                <summary>
                                    <span>Reply In System</span>
                                    <span class="reply-composer-meta">
                                        <span>Toggle</span>
                                        <span class="reply-composer-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                                    </span>
                                </summary>
                                <div class="reply-composer-body">
                            <form method="POST" action="{{ route('modules.contact-messages.reply', $selectedMessage) }}" class="reply-form-grid" enctype="multipart/form-data">
                                @csrf
                                <div class="reply-field">
                                    <label for="reply_subject">Subject</label>
                                    <input
                                        id="reply_subject"
                                        type="text"
                                        name="reply_subject"
                                        value="{{ old('reply_subject', $replySubject) }}"
                                        maxlength="190"
                                        required
                                    >
                                    @error('reply_subject')
                                        <div class="reply-error-text">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="reply-field">
                                    <label for="reply_message">Message</label>
                                    <textarea
                                        id="reply_message"
                                        name="reply_message"
                                        maxlength="8000"
                                        required
                                    >{{ old('reply_message', "Hello {$selectedMessage->name},\n\nThank you for your message.\n\nRegards,\nLGU Energy Team") }}</textarea>
                                    @error('reply_message')
                                        <div class="reply-error-text">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="reply-field">
                                    <label for="reply_attachments">Attachments (optional)</label>
                                    <input
                                        id="reply_attachments"
                                        type="file"
                                        name="reply_attachments[]"
                                        multiple
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
                                    >
                                    @error('reply_attachments')
                                        <div class="reply-error-text">{{ $message }}</div>
                                    @enderror
                                    @error('reply_attachments.*')
                                        <div class="reply-error-text">{{ $message }}</div>
                                    @enderror
                                    <div class="reply-attachment-note">
                                        Max 5 files, up to 5MB each. Allowed: images, PDF, DOC/DOCX, XLS/XLSX, CSV, TXT.
                                    </div>
                                </div>

                                <div class="reply-form-actions">
                                    <div class="reply-form-note">
                                        This sends email using your current system mail settings.
                                    </div>
                                    <button type="submit" class="reply-send-btn">
                                        <i class="fa-solid fa-paper-plane"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                                </div>
                            </details>
                        </div>

                    </div>
                </div>
            @else
                <div class="contact-empty" style="padding: 48px 16px;">
                    No message selected.
                </div>
            @endif
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var stateKey = 'contactInboxUiState:v2:' + window.location.pathname + window.location.search;
    var listPanel = document.querySelector('.contact-list');
    var detailPanel = document.querySelector('.contact-detail');
    var threadScrollBody = document.getElementById('threadScrollBody');
    var conversationPanel = document.getElementById('conversationThread');
    var replyForm = document.querySelector('.reply-form-wrap form');
    var replySubjectInput = document.getElementById('reply_subject');
    var replyMessageInput = document.getElementById('reply_message');
    var replyAttachmentsInput = document.querySelector('input[name="reply_attachments[]"]');
    var autoRefreshTimer = null;
    var AUTO_REFRESH_MS = 30000;
    var initialReplySubject = replySubjectInput ? replySubjectInput.value : '';
    var initialReplyMessage = replyMessageInput ? replyMessageInput.value : '';

    function saveContactInboxState() {
        try {
            var payload = {
                windowScrollY: window.scrollY || window.pageYOffset || 0,
                listScrollTop: listPanel ? listPanel.scrollTop : 0,
                detailScrollTop: detailPanel ? detailPanel.scrollTop : 0,
                threadScrollTop: threadScrollBody ? threadScrollBody.scrollTop : 0,
                conversationScrollTop: conversationPanel ? conversationPanel.scrollTop : 0,
                savedAt: Date.now()
            };
            sessionStorage.setItem(stateKey, JSON.stringify(payload));
        } catch (e) {}
    }

    function restoreContactInboxState() {
        try {
            var raw = sessionStorage.getItem(stateKey);
            if (!raw) return;

            var payload = JSON.parse(raw);
            if (!payload || typeof payload !== 'object') return;

            // Ignore very old saved states to avoid confusing restores.
            if (payload.savedAt && (Date.now() - payload.savedAt) > 10 * 60 * 1000) {
                sessionStorage.removeItem(stateKey);
                return;
            }

            if (listPanel && typeof payload.listScrollTop === 'number') {
                listPanel.scrollTop = payload.listScrollTop;
            }
            if (detailPanel && typeof payload.detailScrollTop === 'number' && !@json((bool) session('reply_success'))) {
                detailPanel.scrollTop = payload.detailScrollTop;
            }
            if (threadScrollBody && typeof payload.threadScrollTop === 'number' && !@json((bool) session('reply_success'))) {
                threadScrollBody.scrollTop = payload.threadScrollTop;
            }
            if (conversationPanel && typeof payload.conversationScrollTop === 'number' && !@json((bool) session('reply_success'))) {
                conversationPanel.scrollTop = payload.conversationScrollTop;
            }
            if (typeof payload.windowScrollY === 'number') {
                window.scrollTo({ top: payload.windowScrollY, behavior: 'auto' });
            }
        } catch (e) {}
    }

    function hasUnsavedReplyDraft() {
        try {
            if (!replyForm) return false;

            if (document.activeElement && replyForm.contains(document.activeElement)) {
                return true;
            }

            if (replySubjectInput && replySubjectInput.value !== initialReplySubject) {
                return true;
            }

            if (replyMessageInput && replyMessageInput.value !== initialReplyMessage) {
                return true;
            }

            if (replyAttachmentsInput && replyAttachmentsInput.files && replyAttachmentsInput.files.length > 0) {
                return true;
            }
        } catch (e) {}

        return false;
    }

    function scheduleAutoRefresh(delay) {
        if (autoRefreshTimer) {
            clearTimeout(autoRefreshTimer);
        }

        autoRefreshTimer = setTimeout(function () {
            if (document.hidden) {
                scheduleAutoRefresh(AUTO_REFRESH_MS);
                return;
            }

            if (hasUnsavedReplyDraft()) {
                scheduleAutoRefresh(10000);
                return;
            }

            saveContactInboxState();
            window.location.reload();
        }, typeof delay === 'number' ? delay : AUTO_REFRESH_MS);
    }

    restoreContactInboxState();

    document.querySelectorAll('.js-copy-email-btn').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var email = btn.getAttribute('data-email') || '';
            if (!email) return;

            var originalText = btn.innerHTML;

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(email);
                } else {
                    var temp = document.createElement('input');
                    temp.value = email;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                }

                btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            } catch (e) {
                btn.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Failed';
            }

            setTimeout(function () {
                btn.innerHTML = originalText;
            }, 1400);
        });
    });

    document.querySelectorAll('.js-focus-reply-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var composer = document.getElementById('replyComposer');
            var textarea = document.getElementById('reply_message');
            if (composer && composer.tagName === 'DETAILS') {
                composer.open = true;
            }
            if (composer) composer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            setTimeout(function () {
                if (textarea) textarea.focus();
            }, 120);
        });
    });

    // Save current panel/window scroll positions before navigations and form submits.
    document.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            saveContactInboxState();
        });
    });
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (autoRefreshTimer) {
                clearTimeout(autoRefreshTimer);
            }
            saveContactInboxState();
        });
    });
    window.addEventListener('beforeunload', saveContactInboxState);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            scheduleAutoRefresh(AUTO_REFRESH_MS);
        }
    });

    if (replyForm) {
        ['input', 'change', 'focusin', 'focusout'].forEach(function (evtName) {
            replyForm.addEventListener(evtName, function () {
                scheduleAutoRefresh(AUTO_REFRESH_MS);
            });
        });
    }

    scheduleAutoRefresh(AUTO_REFRESH_MS);

    @if(session('reply_success'))
    (function () {
        var latestReply = document.getElementById('latestReplyCard');
        if (latestReply) {
            latestReply.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    })();
    @endif
});
</script>
@endsection
