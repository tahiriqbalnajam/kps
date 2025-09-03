namespace App\Http\Controllers;

use App\Models\SyllabusTracking;
use Illuminate\Http\Request;

class SyllabusCompletionController extends Controller
{
    public function index()
    {
        $completion = SyllabusTracking::with('class', 'subject', 'topic')->where('completed', false)->get();
        return response()->json(['completion' => $completion]);
    }

    public function markCompletion(Request $request, $id)
    {
        $tracking = SyllabusTracking::findOrFail($id);
        $tracking->update(['completed' => $request->completed]);
        return response()->json(['message' => 'Completion status updated successfully']);
    }
}
