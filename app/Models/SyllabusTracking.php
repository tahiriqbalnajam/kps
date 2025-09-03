namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusTracking extends Model
{
    use HasFactory;

    protected $fillable = ['class_id', 'subject_id', 'topic_id', 'date_range', 'completed'];

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic()
    {
        return $this->belongsTo(Syllabus::class, 'topic_id');
    }
}
