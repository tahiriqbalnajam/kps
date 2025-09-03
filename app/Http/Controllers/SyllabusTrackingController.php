namespace App\Http\Controllers;

use App\Models\SyllabusTracking;
use Illuminate\Http\Request;

class SyllabusTrackingController extends Controller
{
    public function index()
    {
        $tracking = SyllabusTracking::with('class', 'subject', 'topic')->get();
        return response()->json(['tracking' => $tracking]);
    }

    public function store(Request $request)
    {
        $tracking = SyllabusTracking::create($request->all());
        return response()->json(['message' => 'Tracking entry created successfully', 'tracking' => $tracking]);
    }

    public function update(Request $request, $id)
    {
        $tracking = SyllabusTracking::findOrFail($id);
        $tracking->update($request->all());
        return response()->json(['message' => 'Tracking entry updated successfully', 'tracking' => $tracking]);
    }

    public function destroy($id)
    {
        SyllabusTracking::destroy($id);
        return response()->json(['message' => 'Tracking entry deleted successfully']);
    }
}
