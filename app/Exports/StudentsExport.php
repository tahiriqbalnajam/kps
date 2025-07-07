<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilderInterface;
use Maatwebsite\Excel\Concerns\FromCollection;


class StudentsExport implements FromCollection
{
    protected $query;

    /**
     * @param EloquentBuilder|QueryBuilderInterface $query
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return Student::all();
    }

    /**
    * @return EloquentBuilder|QueryBuilderInterface
    */
    public function query()
    {
        // Ensure relationships needed for mapping are eager loaded by the query passed in
        // e.g., $this->query->with(['parents', 'stdclasses']);
        // This should ideally be handled by the StudentService when it prepares the query.
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Admission#',
            'Student Name',
            'Father Name',
            'Class', // Class name, potentially with section
            'Phone',
            'Gender',
            'DOB',
            'B Form',
            'Is Orphan',
            'Monthly Fee',
            'Status', // Added as it was in original client-side export
        ];
    }

    /**
    * @param Student $student
    */
    public function map($student): array
    {
        // Ensure $student has 'parents' and 'stdclasses' relationships loaded
        $className = '';
        if ($student->stdclasses) {
            $className = $student->stdclasses->name;
            // If stdclasses can be a section and you need "Parent Class - Section Name":
            // if ($student->stdclasses->parentClass) { // Assuming parentClass relationship on Classes model
            //    $className = $student->stdclasses->parentClass->name . ' - ' . $className;
            // }
        }

        return [
            $student->adminssion_number,
            $student->name,
            $student->parents->name ?? '',
            $className,
            $student->parents->phone ?? '',
            $student->gender,
            $student->dob ? Carbon::parse($student->dob)->format('d M, Y') : '',
            $student->b_form,
            $student->is_orphan ? 'Yes' : 'No', // Assuming is_orphan is boolean/int
            $student->monthly_fee,
            $student->status,
        ];
    }
}
