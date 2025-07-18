<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\Classes;
use App\Models\Parents;
use App\Models\Student;
use App\Models\Stdclass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function uploadCsv(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $file = $request->file('file');
            
            // Ensure the imports directory exists
            if (!Storage::disk('public')->exists('imports')) {
                Storage::disk('public')->makeDirectory('imports');
            }
            
            // Store file first - if this fails, no DB entry will be created
            $path = $file->store('imports', 'public');
            
            // Verify file was actually stored
            if (!$path || !Storage::disk('public')->exists($path)) {
                throw new \Exception('File storage failed');
            }
            
            // Only create DB entry if file storage was successful
            $import = Import::create([
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'status' => 'pending',
                'total_records' => 0,
                'processed_records' => 0,
                'successful_records' => 0,
                'failed_records' => 0,
            ]);

            return response()->json(['import_id' => $import->id]);
        } catch (\Exception $e) {
            // Clean up file if DB entry creation failed
            if (isset($path) && $path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage(),
                'message' => 'An error occurred while uploading the file. Please try again.'
            ], 500);
        }
    }

    public function processImport(Request $request)
    {
        $import = Import::findOrFail($request->import_id);
        $filePath = storage_path('app/public/' . $import->file_path);
        
        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }
        
        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file); // Get headers from first row
        
        $errors = [];
        $successCount = 0;
        $totalRecords = 0;
        $rowNumber = 1; // Start from 1 (header row)

        // Use database transaction for the entire import process
        DB::beginTransaction();
        
        try {
            while (($data = fgetcsv($file)) !== FALSE) {
                $rowNumber++;
                $totalRecords++;
                
                try {
                    // Convert array to associative array using headers
                    $record = array_combine($headers, $data);
                    $this->processRecord($record, $rowNumber);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'data' => $record ?? $data
                    ];
                    // If any record fails, rollback and break
                    throw $e;
                }
            }
            
            fclose($file);

            // If we get here, all records were processed successfully
            DB::commit();
            
            $import->update([
                'status' => 'completed',
                'total_records' => $totalRecords,
                'processed_records' => $totalRecords,
                'successful_records' => $successCount,
                'failed_records' => 0,
                'errors' => null
            ]);

            return response()->json([
                'message' => 'Import completed successfully',
                'total' => $totalRecords,
                'successful' => $successCount,
                'failed' => 0,
                'errors' => []
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            
            $import->update([
                'status' => 'failed',
                'total_records' => $totalRecords,
                'processed_records' => $totalRecords,
                'successful_records' => 0,
                'failed_records' => count($errors),
                'errors' => json_encode($errors)
            ]);

            return response()->json([
                'message' => 'Import failed - all changes reverted',
                'total' => $totalRecords,
                'successful' => 0,
                'failed' => count($errors),
                'errors' => $errors
            ], 422);
        }
    }

    private function processRecord($record, $offset)
    {
        // Validate required fields
        $requiredFields = ['student_name', 'parent_name', 'parent_cnic', 'class_name', 'monthly_fee', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($record[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Check if parent exists
        $parent = Parents::where('cnic', $record['parent_cnic'])
                             ->where('name', $record['parent_name'])
                             ->first();

        if (!$parent) {
            // Create new parent
            $parent = Parents::create([
                'name' => $record['parent_name'],
                'cnic' => $record['parent_cnic'],
                'phone' => $record['parent_phone'] ?? null,
                'address' => $record['parent_address'] ?? null,
                'email' => $record['parent_email'] ?? null,
                'password' => bcrypt($record['password']), // Add password field
            ]);
        }

        // Find class
        $class = Classes::where('name', $record['class_name'])->first();
        if (!$class) {
            throw new \Exception("Class not found: {$record['class_name']}");
        }

        // Check if student already exists
        $existingStudent = Student::where('name', $record['student_name'])
                                 ->where('parent_id', $parent->id)
                                 ->first();

        if ($existingStudent) {
            throw new \Exception("Student already exists: {$record['student_name']}");
        }

        // Create student
        Student::create([
            'name' => $record['student_name'],
            'parent_id' => $parent->id,
            'class_id' => $class->id,
            'monthly_fee' => $record['monthly_fee'],
            'phone' => $record['student_phone'] ?? null,
            'address' => $record['student_address'] ?? null,
            'dob' => $record['dob'] ?? '1977-01-01', // Default date if not provided
            'doa' => $record['doa'] ?? '1977-01-01', // Default date if not provided
            'b_form' => $record['b_form'] ?? '11111-1111111-9', // Default date if not provided
            'gender' => $record['gender'] ?? null,
            'roll_no' => $record['roll_no'] ?? null,
            'adminssion_number' => $record['adminssion_number'] ?? null,
        ]);
    }

    public function downloadExample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_example.csv"',
        ];

        $csvData = [
            ['student_name', 'parent_name', 'parent_cnic', 'parent_phone', 'parent_email', 'parent_address', 'class_name', 'monthly_fee', 'dob', 'doa', 'gender', 'roll_no','adminssion_number','password'],
            ['John Doe', 'Robert Doe', '12345-6789012-3', '03001234567', 'robert@example.com', '123 Main St', 'Grade 1', '5000',  '2010-01-15','2010-01-15', 'Male', 'R001','ADR001','1234'],
            ['Jane Smith', 'Mary Smith', '98765-4321098-7', '03117654321', 'mary@example.com', '456 Oak Ave', 'Grade 2', '5500', '2009-05-20','2010-01-15', 'Female', 'R002','1234'],
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
