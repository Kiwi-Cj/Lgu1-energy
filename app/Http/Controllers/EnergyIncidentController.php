<?php

namespace App\Http\Controllers;

use App\Models\EnergyIncident;
use Illuminate\Http\Request;

class EnergyIncidentController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('energy-incidents.index');
    }
}
