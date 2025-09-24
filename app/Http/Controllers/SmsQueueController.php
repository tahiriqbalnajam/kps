<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Student;
use App\Models\Settings;
use App\Models\SmsQueue;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            if($request->get('smstype') == 'Single'){
                // Validate required fields for single SMS
                if (!$request->phone || !$request->message) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Phone number and message are required.'
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
                $sms->phone = $this->format_phone($request->phone);
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
}
