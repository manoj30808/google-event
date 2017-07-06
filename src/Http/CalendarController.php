<?php namespace MspPack\DDSCalendar\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use View;
use DB;
use Auth;
use App\Http\Controllers\Controller;
use MspPack\DDSCalendar\Calendar;
use Spatie\GoogleCalendar\Event as GEvent;

class CalendarController extends Controller
{
	private $view_path;
    private $ctrl_url;

    public function __construct()
    {
        $this->middleware('auth');
        $this->ctrl_url = '/admin/calendar';

        $this->view_path = 'admin.calendar';
        View::share(['ctrl_url'=>$this->ctrl_url,'view_path'=>$this->view_path,'module_name'=> 'Calendar','title'=>'Calendar']);
    }

    public function index()
    {
        return view($this->view_path.'.index');
    }
    public function getList()
    {
        $items = Calendar::select('id as _id','title','start','end',DB::raw("'bg-danger' AS className"))->where('user_id','=',Auth::user()->id)->get();
        return json_encode($items);
    }
    public function store(Request $request)
    {
        $inputs = $request->except('_token','_method');
        $data   = array_except($inputs,array('save','save_exit'));
        $data['user_id'] = Auth::user()->id;
        
        if($event_id = Calendar::create($data)->getKey()){

            $g_event = GEvent::create([
               'name' => $data['title'],
               'startDateTime' => \Carbon\Carbon::parse($data['start']),
               'endDateTime' => \Carbon\Carbon::parse($data['end']),
               'sendNotifications' => true,
               'attendees' => [
                    ['email' => Auth::user()->email],
               ],
               'reminders' => array(
                    'useDefault' => FALSE,
                    'overrides' => array(
                        array('method' => 'email', 'minutes' => 24 * 60),
                        array('method' => 'popup', 'minutes' => 10),
                    ),
                ),
            ]);

            /*UPDATE google event id in database*/
            Calendar::where('id','=',$event_id)->update(['g_event_id'=>$g_event->id]);
            
            return 'Success';
        }

        return 'Fail';
    }

    public function update(Request $request,$id)
    {
        $calendar_event_details = Calendar::where('id','=',$id)
                                  ->where('user_id','=',Auth::user()->id)
                                  ->first();

        if(!empty($calendar_event_details)){
            Calendar::where('id','=',$id)->update(['title'=>$request->title]);
            
            $g_event = GEvent::find($calendar_event_details->g_event_id);    
            $g_event->name = $request->title;
            $g_event->summary = $request->title;
            $g_event->title = $request->title;
            $g_event->save();

            return 'Success';
        }
        return 'Fail';
    }

    public function destroy(Request $request,$id)
    {
        $calendar_event_details = Calendar::where('id','=',$id)
                                  ->where('user_id','=',Auth::user()->id)
                                  ->first();

        if(!empty($calendar_event_details)){
            //DELETE GOOGLE EVENT
            GEvent::find($calendar_event_details->g_event_id)->delete();
            
            Calendar::find($id)->delete();
            return 'Success';
        }
        return 'Fail';
    }
}
