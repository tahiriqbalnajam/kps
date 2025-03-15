<?php

namespace App\Http\Controllers;

use App\Models\SmsQueue as SMS;
use App\Models\Settings;
use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

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
        return response()->json(new JsonResponse(['smsqueue' => $smsqueue]));
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
                $sms = new SMS();
                $sms->message = $request->message;
                $sms->channel = $request->channel;
                $sms->phone = $this->format_phone($request->phone);
                $sms->save();
                return response()->json(new JsonResponse(['sms' => $sms]));
             } elseif($request->get('smstype') == 'Multiple') {
                 $classes = $request->classes;
                 $message = $request->message;
                 $data = array();
                 foreach($classes as $sclass) {
                     $students = Student::with('parents')->where('class_id', $sclass)->get();
                     foreach($students as $student) {
                         $phone = $student->parents->phone;
                         $data[] = array('student_id' => $student->id, 'message' => $message, 'phone' => $phone, 'channel' => $request->channel);
                     }
                 }
                 print_r($data);
                 SMS::insert($data);
             } else {
                 $smstext = Settings::find(1)->fee_sms;
                 $data = array();
                 $smsz = $request->get('sms');
                 $find = array('{{parent_name}}', '{{student_name}}');
                 foreach($smsz as $sms){
                     if(!$sms['phone'])
                         continue;

                     $replace = array($sms['parent'], $sms['name']);
                     $text = str_replace($find, $replace, $smstext);
                     $phone = $this->format_phone($sms['phone']);
                     $data[] = array('student_id' => $sms['id'], 'message' => $text, 'phone' => $phone, 'channel' => $request->channel);
                 }

                 SMS::insert($data); // Eloquent approach
                 return response()->json(new JsonResponse(['sms' => true]));
             }
        }
        catch(Exception $e) {
            return response()->json(new JsonResponse(['error' => $e->getMessage()]));
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
        return response()->json(new JsonResponse(['sms' => $sms]));
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
        $sms->save();
        return response()->json(new JsonResponse(['sms' => $sms]));
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
        return response()->json(new JsonResponse(['msg' => 'Deleted successfully.']));
    }

    public function sendWhatsapp()
    {
        $records = SMS::select(['id', 'message', 'phone'])->where('status', 'pending')->where('channel', 'whatsapp')->get()->take(50);
        return response()->json(new JsonResponse(['records' => $records]));
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
            return response()->json(new JsonResponse(['sms' => 'Sent successfully.']));
        } else {
            return response()->json(new JsonResponse(['sms' => 'Sorry no internet connection.']));
        }


    }

    function check_internet_connection($sCheckHost = 'www.google.com')
	{
		return (bool) @fsockopen($sCheckHost, 80, $iErrno, $sErrStr, 5);
	}
    function change_status(Request $request){
       // echo "<pre>";
       // print_r($request->all());
        SMS::where('status','pending')->update(['status' => 'Sent']);
        return response()->json(new JsonResponse(['sms' => 'Status Changed successfully.']));
    }
    function changeWhatsAppStatus(Request $request) {
        if ($request->has('message_ids')) {
            if ($request->status == 'sent')
                SMS::whereIn('id', $request->message_ids)->where('status','pending')->update(['status' => 'Sent']);
            return response()->json(new JsonResponse(['sms' => 'Status Changed successfully.']));
        }
    }
}
