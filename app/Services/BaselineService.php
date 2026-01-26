
namespace App\Services;

use App\Models\Facility;
use App\Models\EnergyReading;
use Carbon\Carbon;

class BaselineService
{
    /**
     * Handle baseline creation for NEW facilities
     * Uses first 3 months of data
     */
    public static function handle(Facility $facility)
    {
        // If baseline already active, stop
        if ($facility->baseline_status === 'active') {
            return [
                'status' => 'active',
                'message' => 'Baseline already established'
            ];
        }

        // Get first 3 monthly readings
        $readings = EnergyReading::where('facility_id', $facility->id)
            ->orderBy('year')
            ->orderBy('month')
            ->take(3)
            ->get();

        // If less than 3 months, still collecting
        if ($readings->count() < 3) {
            return [
                'status' => 'collecting',
                'message' => 'Collecting baseline data (' . $readings->count() . '/3)'
            ];
        }

        // Compute average kWh
        $averageKwh = round($readings->avg('kwh'), 2);

        // Mark readings as baseline data
        foreach ($readings as $reading) {
            $reading->update([
                'is_baseline_data' => true
            ]);
        }

        // Activate baseline
        $facility->update([
            'baseline_kwh' => $averageKwh,
            'baseline_status' => 'active',
            'baseline_start_date' => Carbon::now()
        ]);

        return [
            'status' => 'activated',
            'baseline_kwh' => $averageKwh,
            'message' => 'Baseline activated successfully'
        ];
    }

    /**
     * Compute efficiency percentage for a facility for a given month/year
     * Returns null if baseline is not active or no data
     * Efficiency (%) = (Baseline kWh / Current Month kWh) × 100
     */
    public static function efficiencyPercent(Facility $facility, $month = null, $year = null)
    {
        if ($facility->baseline_status !== 'active' || !$facility->baseline_kwh) {
            return null;
        }
        $now = Carbon::now();
        $month = $month ?: $now->month;
        $year = $year ?: $now->year;
        $reading = EnergyReading::where('facility_id', $facility->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
        if (!$reading || !$reading->kwh) {
            return null;
        }
        $eff = ($facility->baseline_kwh / $reading->kwh) * 100;
        return round($eff, 2);
    }
}
