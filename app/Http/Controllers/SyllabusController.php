namespace App\Http\Controllers;

use App\Models\Syllabus;
use Illuminate\Http\Request;

class SyllabusController extends Controller
{
    public function index()
    {
        $syllabus = Syllabus::with('class', 'subject')->get();
        return response()->json(['syllabus' => $syllabus]);
    }

    public function store(Request $request)
    {
        $syllabus = Syllabus::create($request->all());
        return response()->json(['message' => 'Syllabus created successfully', 'syllabus' => $syllabus]);
    }

    public function update(Request $request, $id)
    {
        $syllabus = Syllabus::findOrFail($id);
        $syllabus->update($request->all());
        return response()->json(['message' => 'Syllabus updated successfully', 'syllabus' => $syllabus]);
    }

    public function destroy($id)
    {
        Syllabus::destroy($id);
        return response()->json(['message' => 'Syllabus deleted successfully']);
    }
}
