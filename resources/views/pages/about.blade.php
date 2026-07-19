@extends('layouts.qc-admin')

@section('title', 'About LGU Energy System')

@section('content')
<div class="about-page">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fa-solid fa-info-circle"></i>
            </div>
            <div>
                <h1>About LGU Energy System</h1>
                <p class="header-subtitle">Empowering local government units with intelligent energy efficiency solutions</p>
            </div>
        </div>
    </div>

    <div class="about-grid">
        <div class="about-card mission-card">
            <div class="card-icon"><i class="fa-solid fa-bullseye"></i></div>
            <h3>Our Mission</h3>
            <p>To provide local government units with a comprehensive, data-driven platform for monitoring, managing, and optimizing energy consumption across all facilities, enabling smarter resource allocation and sustainable operations.</p>
        </div>

        <div class="about-card vision-card">
            <div class="card-icon"><i class="fa-solid fa-eye"></i></div>
            <h3>Our Vision</h3>
            <p>A future where every local government unit in the Philippines operates with maximum energy efficiency, reduced carbon footprint, and optimized operational costs through intelligent energy management.</p>
        </div>

        <div class="about-card values-card">
            <div class="card-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
            <h3>Our Core Values</h3>
            <ul>
                <li><i class="fa-solid fa-check-circle"></i> Transparency & Accountability</li>
                <li><i class="fa-solid fa-check-circle"></i> Innovation & Continuous Improvement</li>
                <li><i class="fa-solid fa-check-circle"></i> Sustainability & Environmental Responsibility</li>
                <li><i class="fa-solid fa-check-circle"></i> Collaboration & Community Service</li>
            </ul>
        </div>
    </div>

    <div class="features-section">
        <h2><i class="fa-solid fa-star"></i> Key Features</h2>
        <div class="features-grid">
            <div class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-bolt"></i></span>
                <div>
                    <h4>Real-time Energy Monitoring</h4>
                    <p>Track energy consumption across all facilities with live data visualization.</p>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-building"></i></span>
                <div>
                    <h4>Facility Management</h4>
                    <p>Manage multiple facilities, meters, and energy profiles from a central dashboard.</p>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-wrench"></i></span>
                <div>
                    <h4>Maintenance Tracking</h4>
                    <p>Schedule and track maintenance activities for all energy equipment.</p>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-chart-bar"></i></span>
                <div>
                    <h4>Comprehensive Reporting</h4>
                    <p>Generate detailed reports on energy usage, efficiency, and cost savings.</p>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div>
                    <h4>Incident Management</h4>
                    <p>Log and track energy-related incidents for better response and prevention.</p>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-leaf"></i></span>
                <div>
                    <h4>Energy Conservation</h4>
                    <p>Get AI-powered recommendations for energy savings and efficiency improvements.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="stats-section">
        <div class="stat-item">
            <span class="stat-number">95%</span>
            <span class="stat-label">User Satisfaction</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">100+</span>
            <span class="stat-label">LGUs Using the System</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">30%</span>
            <span class="stat-label">Average Energy Savings</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">24/7</span>
            <span class="stat-label">System Availability</span>
        </div>
    </div>

    <div class="back-section">
        <a href="{{ url()->previous() }}" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>
</div>

<style>
.about-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0;
}

.page-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
    border-radius: 16px;
    padding: 32px 40px;
    margin-bottom: 32px;
    color: #fff;
    box-shadow: 0 4px 20px rgba(30, 58, 95, 0.25);
}

.header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.header-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
    flex-shrink: 0;
}

.page-header h1 {
    font-size: 28px;
    font-weight: 600;
    margin: 0;
    color: #fff;
}

.header-subtitle {
    font-size: 15px;
    opacity: 0.85;
    margin: 4px 0 0 0;
}

.about-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.about-card {
    background: #fff;
    border-radius: 14px;
    padding: 28px 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e9edf4;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.about-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 28px rgba(0,0,0,0.10);
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 16px;
}

.mission-card .card-icon {
    background: #dbeafe;
    color: #2563eb;
}

.vision-card .card-icon {
    background: #d1fae5;
    color: #059669;
}

.values-card .card-icon {
    background: #fef3c7;
    color: #d97706;
}

.about-card h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 12px 0;
    color: #1e293b;
}

.about-card p {
    font-size: 14px;
    line-height: 1.7;
    color: #475569;
    margin: 0;
}

.about-card ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.about-card ul li {
    font-size: 14px;
    color: #475569;
    padding: 5px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.about-card ul li i {
    color: #2563eb;
    font-size: 16px;
}

.features-section {
    background: #fff;
    border-radius: 14px;
    padding: 32px 28px;
    margin-bottom: 32px;
    border: 1px solid #e9edf4;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.features-section h2 {
    font-size: 22px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 24px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.features-section h2 i {
    color: #f59e0b;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #eef2f7;
    transition: background 0.2s ease;
}

.feature-item:hover {
    background: #f1f5f9;
}

.feature-icon {
    width: 40px;
    height: 40px;
    background: #dbeafe;
    color: #2563eb;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.feature-item h4 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 4px 0;
    color: #1e293b;
}

.feature-item p {
    font-size: 13px;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
}

.stats-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-item {
    background: #fff;
    border-radius: 12px;
    padding: 24px 20px;
    text-align: center;
    border: 1px solid #e9edf4;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.stat-number {
    display: block;
    font-size: 32px;
    font-weight: 700;
    color: #1e3a5f;
    line-height: 1.2;
}

.stat-label {
    display: block;
    font-size: 14px;
    color: #64748b;
    margin-top: 4px;
}

.back-section {
    text-align: center;
    margin-top: 8px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    background: #e9edf4;
    color: #475569;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: background 0.2s ease;
}

.btn-back:hover {
    background: #d1d9e6;
    color: #1e293b;
}

/* Dark Mode */
body.dark-mode .about-card {
    background: #1e293b;
    border-color: #334155;
    box-shadow: 0 2px 12px rgba(0,0,0,0.25);
}

body.dark-mode .about-card h3 {
    color: #e2e8f0;
}

body.dark-mode .about-card p {
    color: #cbd5e1;
}

body.dark-mode .about-card ul li {
    color: #cbd5e1;
}

body.dark-mode .features-section {
    background: #1e293b;
    border-color: #334155;
}

body.dark-mode .features-section h2 {
    color: #e2e8f0;
}

body.dark-mode .feature-item {
    background: #0f172a;
    border-color: #334155;
}

body.dark-mode .feature-item h4 {
    color: #e2e8f0;
}

body.dark-mode .feature-item p {
    color: #94a3b8;
}

body.dark-mode .stat-item {
    background: #1e293b;
    border-color: #334155;
}

body.dark-mode .stat-number {
    color: #93c5fd;
}

body.dark-mode .stat-label {
    color: #94a3b8;
}

body.dark-mode .btn-back {
    background: #334155;
    color: #cbd5e1;
}

body.dark-mode .btn-back:hover {
    background: #475569;
    color: #f1f5f9;
}

body.dark-mode .page-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}
</style>
@endsection