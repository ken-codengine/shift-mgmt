@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
  <h1>管理画面</h1>
@stop

@section('content')
  <x-adminlte-card>
    <!-- シフト提出期限を過ぎたら管理画面からカレンダーをロックしたい -->
    <!-- 年のプルダウン -->
    <p>カレンダーをロック</p>
    <label for="yearSelect">西暦</label>
    <select id="yearSelect">
      <?php for ($i = 2024; $i <= 2099; $i++): ?>
      <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
      <?php endfor; ?>
    </select>

    <div class="calendar-lock">
      <!-- 月のチェックボックス -->
      <?php for ($i = 1; $i <= 12; $i++): ?>
      <div>
        <input type="checkbox" class="monthCheckbox" id="monthCheckbox<?php echo $i; ?>">
        <label for="monthCheckbox<?php echo $i; ?>"><?php echo $i; ?>月</label>
      </div>
      <?php endfor; ?>
    </div>

    <!-- adminLTEプラグインfullcalendar.js読み込み -->
    <div id='calendar'></div>

    <!-- 提出されたシフト確認・削除用confirmModal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">提出されたシフト</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            </button>
          </div>
          <div class="modal-body">
            <div id="confirm-text"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
            <button type="button" class="btn btn-danger" id="delete-btn" data-dismiss="modal">削除する</button>
          </div>
        </div>
      </div>
    </div>
  </x-adminlte-card>
  @push('js')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/js/holiday_jp.js"></script>
    <script>
      function updateCheckboxes(year) {
        // すべてのチェックボックスをリセット
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function(checkbox) {
          checkbox.checked = false;
        });

        // 新しい年に基づいてチェックボックスを更新
        axios.get('/admin/home/lock_month', {
            params: {
              year: year
            }
          })
          .then(function(response) {
            // レスポンスの処理
            console.log(response.data.message); // 成功メッセージを表示
            // console.log(response.data.data); // 保存されたデータを表示
            // チェックボックスにチェックを入れる
            response.data.data.forEach(function(month) {
              document.getElementById('monthCheckbox' + month).checked = true;
            });
          })
          .catch(function(error) {
            // エラーの処理
            console.error(error.response.data.message); // エラーメッセージを表示
          });
      }

      document.getElementById('yearSelect').addEventListener('change', function() {
        var year = this.value;
        updateCheckboxes(year);
      });

      // ページ読み込み時にyearSelectの値を取得してチェックボックスを更新
      document.addEventListener('DOMContentLoaded', function() {
        var year = document.getElementById('yearSelect').value;
        updateCheckboxes(year);
      });

      // チェックが切り替わるたびにそのyearとmonthをDB(lock_month)に送信
      var checkboxes = document.querySelectorAll('input[type="checkbox"]');
      checkboxes.forEach(function(checkbox, i) {
        checkbox.addEventListener('change', function() {
          var year = document.getElementById('yearSelect').value;
          var month = (i + 1).toString(); // 月を文字列として取得

          // axiosでデータ送信
          if (this.checked) {
            // チェックが入った場合はデータを保存
            axios.post('/admin/home/lock_month/store', {
                year: year, // ここで作成した日付を送信
                month: month // ここで作成した日付を送信
              })
              .then(function(response) {
                // レスポンスの処理
                console.log(response.data.message); // 成功メッセージを表示
              })
              .catch(function(error) {
                // エラーの処理
                console.error(error.response.data.message); // エラーメッセージを表示
              });
          } else {
            // チェックが外れた場合はデータを削除
            axios.delete('/admin/home/lock_month/delete', {
                data: {
                  year: year,
                  month: month
                }
              })
              .then(function(response) {
                // レスポンスの処理
                console.log(response.data.message); // 成功メッセージを表示
              })
              .catch(function(error) {
                // エラーの処理
                console.error(error.response.data.message); // エラーメッセージを表示
              });
          }
        });
      });

      document.addEventListener('DOMContentLoaded', function() {
        // const editModal = new Modal(document.getElementById('editModal'));
        var calendarEl = document.getElementById('calendar');
        let calendar = new FullCalendar.Calendar(calendarEl, {
          //表示テーマ
          themeSystem: 'bootstrap',
          contentHeight: '90vh',
          // plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin],
          initialView: 'dayGridMonth',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
          },
          // スマホでタップしたとき即反応
          selectLongPressDelay: 0,
          locale: 'ja',

          //祝日に赤spanタグを挿入
          dayCellContent: function(arg) {
            // console.log(arg);
            const date = new Date();
            date.setFullYear(
              arg.date.getFullYear(),
              arg.date.getMonth(),
              arg.date.getDate()
            );
            const holiday = holiday_jp.between(new Date(date), new Date(date));
            let hol_tag = document.createElement('span')
            if (holiday[0]) {
              hol_tag.innerHTML = `${arg.date.getDate()}`
              hol_tag.className = 'fc-day-hol';

              let arrayOfDomNodes = [hol_tag]
              return {
                domNodes: arrayOfDomNodes
              }
            } else {
              //日本語化の日表示を外す
              arg.dayNumberText = arg.dayNumberText.replace('日', '');
              return arg.dayNumberText;
            }
          },

          events: function(info, successCallback, failureCallback) {
            document.getElementById('yearSelect').value = info.start.getFullYear();

            // Laravelのイベント取得処理の呼び出し
            axios
              .post("/admin/home/events", {
                start_date: info.start.valueOf(),
                end_date: info.end.valueOf(),
              })
              .then((response) => {
                // 一旦全てのイベントを削除
                calendar.removeAllEvents();
                // // カレンダーに読み込み
                successCallback(response.data);
                // console.log(response.data);
                // console.log(info);
              })
              .catch(() => {
                // バリデーションエラーなど
                alert("取得に失敗しました");
              });
          },

          eventClick: function(info) {

            if (info.event.role = 'admin') {
              $('#deleteModal').modal('show');
              $('#delete-text').text(info.event.startStr + " " + info.event.title);
              // document.getElementById('edit_id').value = info.event.id;
              // document.getElementById('edit_text').value = info.event.extendedProps.text;
              // document.getElementById('edit_date').value = info.event.startStr;
              // console.log(info);
            }
            if (info.event.role = 'staff') {
              console.log(info);
              $('#confirmModal').modal('show');
              $('#confirm-text').text(info.event.startStr + " " + info.event.title);
              $(document).ready(function() {
                var close = $('#delete-btn');
                var deleteOnClick = function() {
                  axios
                    .post("/admin/home/destroy", {
                      id: info.event.id
                    })
                    .then((response) => {
                      var event = calendar.getEventById(response.data.id)
                      event.remove();
                    })
                    .catch(() => {
                      // バリデーションエラーなど
                      alert("取得に失敗しました");
                    });
                };

                // 保存ボタンによる送信、その後イベントの解除
                close.on('click', deleteOnClick);
                $('#confirmModal').on('hidden.bs.modal', function() {
                  console.log('hidden');
                  // 第二引数に値を指定する必要がある
                  close.off('click', deleteOnClick);
                });
              });
            }
          }

          // selectable: true,
          // select: function(info) {
          //   document.getElementById('create_date').value = info.startStr;
          //   $('#createModal').modal('show');

          //   const close = document.getElementById('store-btn');
          //   const saveOnClick = () => {
          //     // 値を日付型として取得
          //     const date = document.getElementById('create_date').valueAsDate
          //     const session_time = document.getElementById('session_time');
          //     const selected_option_text = session_time.options[session_time.selectedIndex].text;
          //     // const [start_time, end_time] = selected_option_text.split(' ~ ');
          //     const user = document.getElementById('create_user').value

          //     // Laravelのaxiosから登録処理の呼び出し
          //     axios
          //       .post("/admin/home/store", {
          //         start_date: info.start.valueOf(),
          //         end_date: info.end.valueOf(),
          //         date: date,
          //         text: text,
          //         user: user,
          //         session_time: session_time,
          //       })
          //       .then((response) => {
          //         // カレンダーに読み込み
          //         calendar.addEvent({
          //           // PHP側から受け取ったevent_idをeventObjectのidにセット
          //           id: response.data.id,
          //           title: response.data.title,
          //           color: response.data.color,
          //           start: response.data.start
          //         });
          //         //renderevent();はv3まで
          //         calendar.refetchEvents();
          //         // console.log(response);
          //       })
          //       .catch(() => {
          //         // バリデーションエラーなど
          //         alert("取得に失敗しました");
          //       });
          //   };

          //   //保存ボタンによる送信、その後イベントの解除
          //   close.addEventListener('click', saveOnClick)
          //   var createModalEl = document.getElementById('createModal')
          //   createModalEl.addEventListener('hidden.bs.modal', () => {
          //     //第二引数に値を指定する必要がある
          //     close.removeEventListener('click', saveOnClick);
          //   });
          // },
        });
        calendar.render();
      });
    </script>
  @endpush
@stop

@section('css')
  <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
  <script>
    // console.log('Hi!');
  </script>
@stop
