<?php 

/*USER MANAGEMENT*/
Route::group(['prefix'=>'admin','middleware' => ['web']], function()
{
	/*CALENDAR*/
	Route::get('calendar/getlist','MspPack\DDSCalendar\Http\CalendarController@getList');
	Route::resource('calendar','MspPack\DDSCalendar\Http\CalendarController');
});
