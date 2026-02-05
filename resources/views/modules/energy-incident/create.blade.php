@extends('layouts.qc-admin')
@section('title', 'Log Energy Incident')
@section('content')

<div style="max-width:960px;margin:40px auto;padding:0 12px;">

    {{-- HEADER --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('energy-incidents.index') }}" style="color:#2563eb;font-size:1.5rem;text-decoration:none;">&#8592;</a>
            <h1 style="font-size:2rem;font-weight:700;color:#111827;margin:0;">Log Energy Incident</h1>
        </div>
    </div>

    {{-- FORM --}}
    <form method="POST" action="{{ route('energy-incidents.store') }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:32px;background:#fff;border-radius:18px;box-shadow:0 4px 32px rgba(55,98,200,0.10);padding:36px 32px 28px 32px;">
        @csrf

        {{-- ---------------- Basic Incident Info ---------------- --}}
        <section style="padding:24px 0 0 0;border-radius:0;background:none;border-bottom:1px solid #e5e7eb;margin-bottom:0;">
            <h2 style="font-size:1.2rem;font-weight:600;color:#2563eb;margin-bottom:16px;">🆔 Basic Incident Info</h2>
            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <label>Facility <span style="color:#e11d48">*</span></label>
                    <select name="facility_id" required style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                        <option value="">Select Facility</option>
                        @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Incident Type <span style="color:#e11d48">*</span></label>
                    <select name="incident_type" required style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                        <option value="">Select Type</option>
                        <option>High Energy Consumption</option>
                        <option>Abnormal Spike</option>
                        <option>Equipment Malfunction</option>
                        <option>Power Interruption</option>
                    </select>
                </div>

                <div>
                    <label>Severity Level <span style="color:#e11d48">*</span></label>
                    <select name="severity" required style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                        <option value="">Select Severity</option>
                        <option>Low</option>
                        <option>Medium</option>
                        <option>High</option>
                    </select>
                </div>

                <div>
                    <label>Date Detected <span style="color:#e11d48">*</span></label>
                    <input type="date" name="date_detected" required style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                </div>

                <div>
                    <label>Time Detected <span style="color:#e11d48">*</span></label>
                    <input type="time" name="time_detected" required style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                </div>

                <div>
                    <label>Detected By <span style="color:#e11d48">*</span></label>
                    <select name="detected_by" required style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                        <option value="">Select</option>
                        <option>System</option>
                        <option>Energy Officer</option>
                        <option>Staff</option>
                    </select>
                </div>
            </div>
        </section>

        {{-- ---------------- Incident Description ---------------- --}}
        <section style="padding:24px 0 0 0;border-radius:0;background:none;border-bottom:1px solid #e5e7eb;margin-bottom:0;">
            <h2 style="font-size:1.2rem;font-weight:600;color:#2563eb;margin-bottom:12px;">📝 Incident Description</h2>
            <textarea name="description" rows="3" required style="width:100%;padding:14px 16px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;" placeholder="Describe the incident..."></textarea>
        </section>

        {{-- ---------------- Probable Cause ---------------- --}}
        <section style="padding:24px 0 0 0;border-radius:0;background:none;border-bottom:1px solid #e5e7eb;margin-bottom:0;">
            <h2 style="font-size:1.2rem;font-weight:600;color:#2563eb;margin-bottom:12px;">🧠 Probable Cause</h2>
            <select name="probable_cause[]" multiple style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                <option>Extended operating hours</option>
                <option>HVAC overuse</option>
                <option>Faulty equipment</option>
                <option>Additional load (events / activities)</option>
                <option>Weather-related increase</option>
                <option>Under investigation</option>
            </select>
        </section>

        {{-- ---------------- Immediate Action ---------------- --}}
        <section style="padding:24px 0 0 0;border-radius:0;background:none;border-bottom:1px solid #e5e7eb;margin-bottom:0;">
            <h2 style="font-size:1.2rem;font-weight:600;color:#2563eb;margin-bottom:12px;">🛠️ Immediate Action Taken</h2>
            <select name="immediate_action" style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                <option>None</option>
                <option>Inspection scheduled</option>
                <option>Equipment shutdown</option>
                <option>Load balancing</option>
                <option>Maintenance requested</option>
            </select>
        </section>

        {{-- ---------------- Attachments ---------------- --}}
        <section style="padding:24px 0 0 0;border-radius:0;background:none;border-bottom:1px solid #e5e7eb;margin-bottom:0;">
            <h2 style="font-size:1.2rem;font-weight:600;color:#2563eb;margin-bottom:12px;">📎 Attachments</h2>
            <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
        </section>

        {{-- ---------------- Incident Status ---------------- --}}
        <section style="padding:24px 0 0 0;border-radius:0;background:none;border-bottom:1px solid #e5e7eb;margin-bottom:0;">
            <h2 style="font-size:1.2rem;font-weight:600;color:#2563eb;margin-bottom:12px;">📌 Incident Status</h2>
            <select name="status" required style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                <option>Open</option>
                <option>Under Investigation</option>
                <option>Action Ongoing</option>
                <option>Resolved</option>
                <option>Closed</option>
            </select>
        </section>

        {{-- ---------------- Resolution & Outcome ---------------- --}}
        <section style="padding:24px 0 0 0;border-radius:0;background:none;margin-bottom:0;">
            <h2 style="font-size:1.2rem;font-weight:600;color:#2563eb;margin-bottom:12px;">🧾 Resolution & Outcome</h2>
            <textarea name="resolution_summary" rows="2" style="width:100%;padding:14px 16px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;" placeholder="Resolution summary..."></textarea>

            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:12px;">
                <div>
                    <label>Date Resolved</label>
                    <input type="date" name="date_resolved" id="date_resolved" style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;">
                </div>
                <div>
                    <label>Final Consumption Impact (kWh)</label>
                    <input type="number" name="final_consumption_impact" id="final_consumption_impact" step="0.01" style="width:100%;padding:13px 14px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;" placeholder="e.g. 120.50">
                </div>
            </div>

            <label style="margin-top:12px;display:block;">Preventive Recommendation</label>
            <textarea name="preventive_recommendation" rows="2" style="width:100%;padding:14px 16px;border-radius:10px;border:1.5px solid #d1d5db;background:#f9fafb;font-size:1.08rem;box-shadow:0 1px 4px #2563eb0a;" placeholder="Preventive recommendation..."></textarea>
        </section>

        {{-- ---------------- Submit Button ---------------- --}}
        <div style="text-align:right;margin-top:24px;">
            <button type="submit" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:700;border:none;border-radius:12px;padding:16px 48px;font-size:1.13rem;letter-spacing:0.5px;box-shadow:0 2px 8px #3762c81a;transition:background 0.2s,box-shadow 0.2s;">
                <span style="display:inline-flex;align-items:center;gap:8px;"><i class="fa fa-save"></i> Save Incident</span>
            </button>
        </div>
    </form>

    {{-- RESPONSIVE GRID --}}
    <style>
        @media (max-width: 800px) {
            .form-grid {
                grid-template-columns: 1fr !important;
            }
            form[method="POST"] {
                padding: 18px 6px 18px 6px !important;
            }
        }
        select, input, textarea {
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        select:focus, input:focus, textarea:focus {
            border-color: #2563eb;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 2px #2563eb22;
        }
        button[type="submit"]:hover {
            background:linear-gradient(90deg,#1746a0,#2563eb);
            box-shadow:0 4px 16px #3762c822;
        }
    </style>

</div>

@endsection
