use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Delete first3months_data for a facility
Route::delete('/facilities/{facility}/first3months-data', function($facility) {
    $deleted = DB::table('first3months_data')->where('facility_id', $facility)->delete();
    return response()->json(['success' => $deleted > 0]);
});
