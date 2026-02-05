<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Student;
use App\Models\Settings;
use App\Models\SmsQueue;
use App\Models\Test;
use App\Models\TestResult;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SmsQueue as SMS;
use Illuminate\Http\JsonResponse;
use App\Laravue\JsonResponse as LaravueJsonResponse;

class SmsQueueController extends Controller
{
    const ITEM_PER_PAGE = 30;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $date = $request->get('date');
        $pending = $request->get('status');
        $smsqueue = SMS::with('student')
                    ->when($date, function($q) use($date) {
                        $start_date = Carbon::parse($date[0])->startOfDay();
                        $end_date = Carbon::parse($date[1])->endOfDay();
                        return $q->whereBetween('created_at', [$start_date, $end_date]);
                    })
                    ->where('status','pending')
                    ->paginate($limit);
        return response()->json(new LaravueJsonResponse(['smsqueue' => $smsqueue]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if ($request->channel === 'push') {
                $message = $request->message;
                $pushUsers = $request->push_users;
                
                if (empty($message)) {
                     return response()->json([
                        'success' => false,
                        'message' => 'Message is required.'
                    ], 422);
                }

                // If no users selected, broadcast to all
                if (empty($pushUsers)) {
                    try {
                        \OneSignal::sendNotificationToAll(
                            $message, 
                            $url = null, 
                            $data = null, 
                            $buttons = null, 
                            $schedule = null
                        );
                        
                        // Log to DB
                        $sms = new SMS();
                        $sms->message = $message;
                        $sms->channel = 'push';
                        $sms->phone = 'broadcast';
                        $sms->status = 'sent';
                        $sms->save();

                        return response()->json([
                            'success' => true,
                            'message' => 'Push notification broadcasted to all users.',
                            'data' => ['sms' => $sms]
                        ]);

                    } catch (\Exception $e) {
                         return response()->json([
                            'success' => false,
                            'message' => 'Failed to broadcast push notification: ' . $e->getMessage()
                        ], 500);
                    }
                } else {
                    // Send to specific users
                    $users = User::whereIn('id', $pushUsers)->whereNotNull('player_id')->get();
                    
                    if ($users->isEmpty()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected users do not have associated devices (Player IDs).'
                        ], 422);
                    }

                    $playerIds = $users->pluck('player_id')->toArray();
                    
                    try {
                        \OneSignal::sendNotificationToUser(
                            $message, 
                            $playerIds, 
                            $url = null, 
                            $data = null, 
                            $buttons = null, 
                            $schedule = null
                        );

                        // Log to DB for each user
                        foreach($users as $user) {
                             $sms = new SMS();
                             $sms->message = $message;
                             $sms->channel = 'push';
                             $sms->phone = $user->email; // Use email as identifier in phone column
                             $sms->student_id = $user->id; // Use student_id to store user_id (assuming loose relation or just for ID ref)
                             // Note: student_id usually links to students table, this might break relation view if not careful. 
                             // If student_id is strict foreign key, we might need to leave it null or find linked student.
                             // Checking User model: $user->student() exists.
                             if ($user->student) {
                                 $sms->student_id = $user->student->id;
                             }
                             $sms->status = 'sent';
                             $sms->save();
                        }

                        return response()->json([
                            'success' => true,
                            'message' => 'Push notification sent to selected users.',
                            'data' => ['count' => count($users)]
                        ]);

                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to send push notification: ' . $e->getMessage()
                        ], 500);
                    }
                }
            }

            if($request->get('smstype') == 'Single'){
                // Validate required fields for single SMS
                $rules = ['message' => 'required'];
                
                // Phone is required only if channel is NOT push
                if($request->channel !== 'push') {
                    if (!$request->phone) {
                         return response()->json([
                            'success' => false,
                            'message' => 'Phone number is required.'
                        ], 422);
                    }
                }

                if (!$request->message) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Message is required.'
                    ], 422);
                }
                
                if (!$request->channel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Message channel is required.'
                    ], 422);
                }
                
                $sms = new SMS();
                $sms->message = $request->message;
                $sms->channel = $request->channel;
                
                if ($request->channel === 'push') {
                    $sms->phone = 'push-user'; // Or retrieve user name/email if available
                } else {
                    $sms->phone = $this->format_phone($request->phone);
                }
                
                $sms->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'SMS added to queue successfully.',
                    'data' => ['sms' => $sms]
                ]);
                
             } elseif($request->get('smstype') == 'Multiple') {
                // Validate required fields for multiple SMS
                if (!$request->message) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Message is required.'
                    ], 422);
                }
                
                if (!$request->classes || empty($request->classes)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'At least one class must be selected.'
                    ], 422);
                }
                
                if (!$request->channel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Message channel is required.'
                    ], 422);
                }
                 $classes = $request->classes;
                 $message = $request->message;
                 $data = array();
                 $studentCount = 0;
                 
                 foreach($classes as $sclass) {
                     $students = Student::with('parents')->where('class_id', $sclass)->where('status', 'enable')->get();
                     foreach($students as $student) {
                         $phone = isset($student->parents->phone) ? $this->format_phone($student->parents->phone) : null;
                         if ($phone) { // Only add if phone number exists
                             $data[] = array(
                                 'student_id' => $student->id, 
                                 'message' => $message, 
                                 'phone' => $phone, 
                                 'channel' => $request->channel
                             );
                             $studentCount++;
                         }
                     }
                 }
                 
                 if (empty($data)) {
                     return response()->json([
                         'success' => false,
                         'message' => 'No students with valid phone numbers found in selected classes.'
                     ], 422);
                 }
                 
                 SMS::insert($data);
                 
                 return response()->json([
                     'success' => true,
                     'message' => "SMS added to queue for {$studentCount} students successfully.",
                     'data' => ['count' => $studentCount]
                 ]);
             } else {
                $settingsCollection = Settings::where('setting_key', [
                    'school_name', 
                    'address', 
                    'phone', 
                    'fee_sms_template',
                    'message_channel'
                ])->pluck('setting_value', 'setting_key');
                $schoolName = $settingsCollection->get('school_name', 'Your School'); // Default if not set
                $schoolAddress = $settingsCollection->get('address', '');
                $schoolPhone = $settingsCollection->get('phone', '');
                $messageChannel = $settingsCollection->get('message_channel', 'sms');
                 $data = array();
                 $smsz = $request->get('sms');
                 $find = array('{{parent_name}}', '{{student_name}}');
                 foreach($smsz as $msg){
                     if(!$msg['phone'])
                         continue;

                    $sms = []; // Initialize sms array here
                    $sms['student_id'] = $msg['id'];
                    $sms['channel'] = $messageChannel; // Use channel from settings
                    $parentName = $msg['parents']['name'] ?? 'Parent';
                    $studentName = $msg['name'] ?? 'Student';
                    $className = $msg['stdclasses']['name'] ?? 'N/A';
                    $pendingSmsTemplate = $settingsCollection->get('fee_sms_template', "Dear [[parent_name]], your child [[student_name]] (Class [[class_title]]) has a pending fee. Please pay fee at earliest as possible. Thank you. - [[school_name]]"); // Default template
                    $message = str_replace(
                        ['[[parent_name]]', '[[student_name]]', '[[class_title]]', '[[school_name]]', '[[school_address]]', '[[school_phone]]'],
                        [$parentName, $studentName, $className, $schoolName, $schoolAddress, $schoolPhone],
                        $pendingSmsTemplate
                    );
                    $sms['message'] = $message; // Assign the processed message
                    $sms['phone'] = isset($msg['phone']) ? $this->format_phone($msg['phone']) : null;
                    
                    // Only create SMS queue if phone number is valid
                    if ($msg['phone']) {
                        SmsQueue::create($sms);
                    }
                 }
                 return response()->json([
                     'success' => true,
                     'message' => 'Bulk SMS added to queue successfully.',
                     'data' => ['sms' => true]
                 ]);
             }
        }
        catch(Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }


    }

    function format_phone($phone) {
        $phone = ltrim($phone, '0'); //remove 0 from start
        $phone = ((strpos( $phone, "92" ) === 0)) ? $phone : '92'.$phone;
        $find = array(' ', '-');
        $replace = array('','');
        $phone = str_replace($find, $replace, $phone);
        return $phone;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sms = SMS::find($id);
        return response()->json(new LaravueJsonResponse(['sms' => $sms]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $sms = SMS::find($id);
        $sms->message = $request->message;
        $sms->phone = $request->phone;
        $sms->channel = $request->channel;
        $sms->save();
        
        return response()->json([
            'success' => true,
            'message' => 'SMS updated successfully.',
            'data' => ['sms' => $sms]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        SMS::destroy($id);
        return response()->json([
            'success' => true,
            'message' => 'SMS deleted successfully.'
        ]);
    }

    public function sendWhatsapp()
    {
        $records = SMS::select(['id', 'message', 'phone'])->where('status', 'pending')->where('channel', 'whatsapp')->get()->take(50);
        return response()->json(new LaravueJsonResponse(['records' => $records]));
    }
    public function sendsms() {
        if($this->check_internet_connection())
		{
            SMS::where('status', 'pending')->where('channel', 'sms')
            ->chunkById(50, function ($smsz) {
                foreach ($smsz as $sms) {
                    $username = "QayadatSch";
					$password = "Qay!@#123";
					$ClientID = "QayadatSch";
					$language = "English";
					$mask = "Qayadat Sch";
					$to = $this->format_phone($sms->phone);
					$message = urlencode($sms->message);
					// Prepare data for POST request
					$data = "userName=".$username."&password=".$password."&ClientID=".$ClientID."&mask=".$mask.
					"&msg=".$message."&to=".$to."&language=".$language;
					// Send the POST request with cURL
					$ch = curl_init('http://www.smspoint.pk/api/smsapi/');
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result = curl_exec($ch); //get return String
					curl_close($ch);
					if ($result == "Sent Successfully") {
                        $sms->update(['status' => 'sent']);
					} else {
                        $sms->api_error = $result;
                        $sms->save();
                    }
                }
                //$smsz->each->update(['status' => 'sent']);
            }, $column = 'id');
            return response()->json(new LaravueJsonResponse(['sms' => 'Sent successfully.']));
        } else {
            return response()->json(new LaravueJsonResponse(['sms' => 'Sorry no internet connection.']));
        }


    }

    function check_internet_connection($sCheckHost = 'www.google.com')
	{
		return (bool) @fsockopen($sCheckHost, 80, $iErrno, $sErrStr, 5);
	}
    function change_status(Request $request){
        SMS::where('status','pending')->update(['status' => 'Sent']);
        return response()->json(new LaravueJsonResponse(['sms' => 'Status Changed successfully.']));
    }
    
    function changeWhatsAppStatus(Request $request) {
        if ($request->has('message_ids')) {
            if ($request->status == 'sent')
                SMS::whereIn('id', $request->message_ids)->where('status','pending')->update(['status' => 'Sent']);
            return response()->json(new LaravueJsonResponse(['sms' => 'Status Changed successfully.']));
        }
    }

    /**
     * Store bulk test SMS for multiple students in one operation
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBulkTestSMS(Request $request)
    {
        try {
            // Validate required fields
            if (!$request->test_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test ID is required.'
                ], 422);
            }

            // Get test data with all necessary relationships
            $test = Test::with(['class', 'subject', 'testResults.student.parents'])
                       ->find($request->test_id);

            if (!$test) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test not found.'
                ], 404);
            }

            // Get SMS template from settings
            $settings = Settings::whereIn('setting_key', ['test_sms_template', 'school_name', 'message_channel'])
                               ->pluck('setting_value', 'setting_key');

            $template = $settings->get('test_sms_template');
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test SMS template not configured. Please set it in Settings > Message Settings.'
                ], 422);
            }

            // Get test results and filter out absent students
            $testResults = $test->testResults->filter(function ($result) {
                return $result->absent !== 'yes' && $result->score !== null && $result->score !== '';
            })->sortByDesc(function ($result) {
                return (float) $result->score;
            });

            if ($testResults->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid test results found (all students are absent or have no marks).'
                ], 422);
            }

            // Default channel to WhatsApp
            $channel = $settings->get('message_channel', 'whatsapp');
            $schoolName = $settings->get('school_name', 'Your School');

            $smsData = [];
            $validCount = 0;

            foreach ($testResults as $index => $result) {
                // Skip if no parent phone number
                if (!$result->student || !$result->student->parents || !$result->student->parents->phone) {
                    continue;
                }

                $position = $index + 1;

                // Prepare SMS content with template replacement
                $smsContent = str_replace(
                    ['[[parent_name]]', '[[student_name]]', '[[class_title]]', '[[test_title]]', '[[obtained_marks]]', '[[total_marks]]', '[[position]]', '[[school_name]]'],
                    [
                        $result->student->parents->name ?: '',
                        $result->student->name ?: '',
                        $test->class->name ?: '',
                        $test->title ?: '',
                        $result->score ?: '0',
                        $test->total_marks ?: '',
                        (string) $position,
                        $schoolName
                    ],
                    $template
                );

                $smsData[] = [
                    'student_id' => $result->student->id,
                    'message' => $smsContent,
                    'phone' => $this->format_phone($result->student->parents->phone),
                    'channel' => $channel,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $validCount++;
            }

            if (empty($smsData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students with valid phone numbers found.'
                ], 422);
            }

            // Insert all SMS records in one batch operation
            SMS::insert($smsData);

            return response()->json([
                'success' => true,
                'message' => "Bulk test SMS added to queue for {$validCount} students successfully.",
                'data' => ['count' => $validCount]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all SMS records from the queue
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll()
    {
        try {
            // Get count before deleting
            $deletedCount = SMS::count();
            
            if ($deletedCount === 0) {
                return response()->json([
                    'success' => true,
                    'message' => "No SMS records to delete."
                ]);
            }
            
            // Use delete() instead of truncate() to respect foreign keys and model events
            // If you want to use truncate, you need to disable foreign key checks first
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            SMS::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            return response()->json([
                'success' => true,
                'message' => "All {$deletedCount} SMS record(s) deleted successfully."
            ]);
        } catch (Exception $e) {
            // Re-enable foreign key checks in case of error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (Exception $ex) {
                // Ignore if this fails
            }
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting SMS records: ' . $e->getMessage()
            ], 500);
        }
    }
}
