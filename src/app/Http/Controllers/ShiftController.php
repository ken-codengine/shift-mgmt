<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Models\Schedule;
use App\Models\SessionTime;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShiftController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		//
		$session_times = SessionTime::getSessionTimes();
		$data = ['session_times' => $session_times];
		return view('home', $data);
	}

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
		//
		// バリデーション
		$request->validate([
			'date' => 'required|string',
			'checkboxStates' => 'required|string',
			// 'start_date' => 'required|integer',
			// 'end_date' => 'required|integer'
		]);

		//textカラムに挿入
		$shift = new Shift();
		//requestを結合してtextカラムに挿入
		$shift->uuid = (string)Str::uuid();
		$shift->user_id = Auth::id();
		$shift->date = date('Y-m-d', strtotime($request->input('date')));
		$text = $request->input('checkboxStates');
		$shift->text = $text;
		$shift->save();

		$user = User::select()
			->where('id', Auth::id())
			->select(
				// FullCalendarの形式に合わせる
				'name',
				'color',
			)
			->first();
		$shift->name = $user->name;
		$shift->color = $user->color;

		$data = fn ($shift) => $this->convertToEventByShiftForFullcalendar($shift);

		return $data($shift);  // 無名関数を実行します
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Request $request, Shift $shift)
	{
		// バリデーション
		$request->validate([
			'start_date' => 'required|integer',
			'end_date' => 'required|integer'
		]);

		// カレンダー表示期間
		$start_date = date('Y-m-d', $request->input('start_date') / 1000);
		$end_date = date('Y-m-d', $request->input('end_date') / 1000);

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

		$id = Auth::id();
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
			->where('user_id', $id)
			->get()
			->map(fn ($shift) => $this->convertToEventByShiftForFullcalendar($shift))
			->toArray();

		$data = array_merge($shifts, $schedules);
		return $data;
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Shift $shift)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(UpdateShiftRequest $request, Shift $shift)
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
			'title' => $shift->text,
			// 'title' => $shift->name . ':' . $shift->text,
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
}
