<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\SessionTime;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ScheduleController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		//
		$session_times = SessionTime::getSessionTimes();
		$users = User::get();

		return view('admin.home', compact('users', 'session_times'));
		// return view('admin.home')->with(
		//     ['session_times' => $session_times]
		// );
	}

	// public function getEventsForPeriod(Request $request)
	// {
	// 	// バリデーション
	// 	$request->validate([
	// 		'start_date' => 'required|integer',
	// 		'end_date' => 'required|integer'
	// 	]);

	// 	// カレンダー表示期間
	// 	$start_date = date('Y-m-d', $request->input('start_date') / 1000);
	// 	$end_date = date('Y-m-d', $request->input('end_date') / 1000);

	// 	$schedules = Schedule::select(
	// 		'date',
	// 		'text as title',
	// 	)
	// 		->whereBetween('date', [$start_date, $end_date])
	// 		->get();

	// 	return $schedules;
	// }

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		// バリデーション
		$request->validate([
			'date' => 'required|string',
			'user' => 'required|string',
			'session_time' => 'required|string',
			'text' => 'required|max:32',
		]);

		//textカラムに挿入
		$schedule = new Schedule();
		//requestを結合してtextカラムに挿入
		$schedule->uuid = (string)Str::uuid();
		$schedule->date = $request->input('date');
		$text = $request->input('session_time') . $request->input('user') . $request->input('text');
		$schedule->text = $text;
		$schedule->save();

		return redirect()->route('admin.home');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Request $request, Schedule $schedules)
	{
		// バリデーション
		$request->validate([
			'start_date' => 'required|integer',
			'end_date' => 'required|integer'
		]);

		// カレンダー表示期間
		$start_date = date('Y-m-d', $request->input('start_date') / 1000);
		$end_date = date('Y-m-d', $request->input('end_date') / 1000);

		// $id = Auth::id();
		$schedules = Schedule::select()
			->select(
				// FullCalendarの形式に合わせる
				'uuid',
				'date',
				'text'
			)
			->whereBetween('date', [$start_date, $end_date])
			// ->where('user_id', $id)
			->get()
			->map(fn ($schedule) => $this->convertToEventByScheduleForFullcalendar($schedule))
			->toArray();

		// return $schedules;

		// $id = Auth::id();
		$shifts = Shift::select()
			->join('users', 'users.id', '=', 'shifts.user_id')
			->select(
				// FullCalendarの形式に合わせる
				'uuid',
				'name',
				'color',
				'date',
				'text'
			)
			->whereBetween('date', [$start_date, $end_date])
			// ->where('user_id', $id)
			->get()
			->map(fn ($shift) => $this->convertToEventByShiftForFullcalendar($shift))
			->toArray();

		// $data = $shifts->concat($schedules)->toArray();
		$data = array_merge($shifts, $schedules);

		return $data;
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Schedule $schedule)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Schedule $schedule)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, Shift $shift)
	{
		// バリデーション
		$request->validate([
			'id' => 'required|string'
		]);

		// 送信されてきたidをEventテーブルに登録されているデータと紐付ける
		// 送信されてきたidをuuidカラムと比較して、一致するデータを取得する
		$shift = Shift::where('uuid', $request->input('id'))->first();
		$uuid = $shift->uuid;  // 削除する前にUUIDを保存します
		$shift->delete();

		return [
			'id' => $uuid  // 削除したシフトのUUIDを返します
		];
	}

	//FullCalendarで使えるeventの配列に整形
	private function convertToEventByShiftForFullcalendar(Shift $shift): array
	{
		return [
			'id' => $shift->uuid,
			'title' => $shift->name . ':' . $shift->text,
			'start' => $shift->date,
			'color' => $shift->color,
			'extendedProps' => [
				'text' => $shift->text,
				'array' => [
					'id' => $shift->uuid
				]
			]
		];
	}

	//FullCalendarで使えるeventの配列に整形
	private function convertToEventByScheduleForFullcalendar(Schedule $schedule): array
	{
		return [
			// 'id' => $schedule->uuid,
			'title' => $schedule->text,
			'start' => $schedule->date,
			// 'color' => $schedule->color,
			'extendedProps' => [
				// 'text' => $schedule->text,
				'array' => [
					'id' => $schedule->uuid
				]
			]
		];
	}

	// "2019-12-12T00:00:00+09:00"のようなデータを今回のDBに合うように"2019-12-12"に整形
	private function formatDate($date)
	{
		return str_replace('T00:00:00+09:00', '', $date);
	}
}
