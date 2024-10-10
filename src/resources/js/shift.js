import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import { Modal } from 'bootstrap';
import * as holiday_jp from '@holiday-jp/holiday_jp';

var createModalEl = document.getElementById('createModal')
var deleteModalEl = document.getElementById('deleteModal')
var confirmModalEl = document.getElementById('confirmModal')
var cautionModalEl = document.getElementById('cautionModal')
var createModal = new Modal(createModalEl, {});
var deleteModal = new Modal(deleteModalEl, {});
var confirmModal = new Modal(confirmModalEl, {});
var cautionModal = new Modal(cautionModalEl, {});
// document.addEventListener('DOMContentLoaded', function () {
//   createModal.show();
// var editModal = new Modal(editModalEl,{});
// });
var calendarEl = document.getElementById('shift');

let calendar = new Calendar(calendarEl, {
  //表示テーマ
  themeSystem: 'bootstrap',
  contentHeight: '75vh',
  plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    // right: 'dayGridMonth,timeGridWeek,listWeek'
    right: 'dayGridMonth,listMonth'
  },
  // スマホでタップしたとき即反応
  selectLongPressDelay: 0,
  locale: 'ja',
  buttonText: {
    today: '今月',
    month: '月',
    list: 'リスト'
  },
  // all-day表示を終日にする
  allDayText: '終日',
  // デフォルトの6週間表示を自動調整
  fixedWeekCount: false,

  //祝日に赤spanタグを挿入
  dayCellContent: function (arg) {
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
      return { domNodes: arrayOfDomNodes }
    } else {
      //日本語化の日表示を外す
      arg.dayNumberText = arg.dayNumberText.replace('日', '');
      return arg.dayNumberText;
    }
  },

  eventDidMount: function (mountArg) {
    const el = mountArg.el
    if (mountArg.view.type == "listMonth") {
      const date = new Date();
      date.setFullYear(
        mountArg.event.start.getFullYear(),
        mountArg.event.start.getMonth(),
        mountArg.event.start.getDate()
      );
      const holiday = holiday_jp.between(new Date(date), new Date(date));
      if (holiday[0]) {
        console.log(holiday);
        el.previousSibling.classList.add('fc-day-hol');
      }
    };
  },

  events: function (info, successCallback, failureCallback) {
    const startDate = new Date();
    const endDate = new Date();
    startDate.setFullYear(
      info.start.getFullYear(),
      info.start.getMonth(),
      info.start.getDate()
    );
    endDate.setFullYear(
      info.end.getFullYear(),
      info.end.getMonth(),
      info.end.getDate()
    );

    // Laravelのイベント取得処理の呼び出し
    axios
      .post("/home/show", {
        start_date: info.start.valueOf(),
        end_date: info.end.valueOf(),
      })
      .then((response) => {
        // 一旦全てのイベントを削除
        calendar.removeAllEvents();
        // カレンダーに読み込み
        successCallback(response.data);
      })
      .catch(() => {
        // バリデーションエラーなど
        alert("取得に失敗しました");
      });
  },

  selectable: true,
  select: function (info) {
    axios // lock_month取得処理の呼び出し
      .post("/home/lock_month/show", {
        title: info.view.title
      })
      .then((response) => {
        var titleExists = response.data.titleExists;

        // チェックボックスのDOM要素を取得します
        let checkboxes = document.querySelectorAll('.form-check-input');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        // document.getElementById('createModal_description').innerText = info.startStr;
        document.getElementById('create_date').value = info.startStr;

        if (titleExists === true) { // データベースの月が1から始まる場合、+1を忘れずに
          cautionModal.show();
          document.getElementById('caution-text').innerText = "";
        } else {
          createModal.show();
          console.log(info);

          const session_times = document.create_form.session_times
          const close = document.getElementById('store-btn');
          const saveOnClick = async () => {
            // 再度titleExistsを取得して判定
            const response = await axios.post("/home/lock_month/show", {
              title: info.view.title
            });
            var titleExists = response.data.titleExists;

            if (titleExists === true) {
              cautionModal.show();
              document.getElementById('caution-text').innerText = info.event.startStr + " " + info.event.title;
            } else {
              // 値を日付型として取得
              const date = document.getElementById('create_date').valueAsDate

              // 各チェックボックスの状態を取得し、配列に格納します
              let checkboxStates = Array.from(checkboxes).map(checkbox => checkbox.checked ? '⚪︎' : '×').join('/');
              // Laravelのaxiosから登録処理の呼び出し
              // Laravelのaxiosから登録処理の呼び出し
              axios
                .post("/home/store", {
                  // start_date: info.start.valueOf(),
                  // end_date: info.end.valueOf(),
                  date: date,
                  checkboxStates: checkboxStates  // チェックボックスの状態を送信します
                })
                .then((response) => {
                  // カレンダーに読み込み
                  calendar.addEvent({
                    // PHP側から受け取ったevent_idをeventObjectのidにセット
                    id: response.data.id,
                    title: response.data.title,
                    color: response.data.color,
                    start: response.data.start
                  });
                  //renderevent();はv3まで
                  calendar.refetchEvents();
                  // console.log(response);
                })
                .catch(() => {
                  // バリデーションエラーなど
                  alert("取得に失敗しました");
                });
            }
          };
          //保存ボタンによる送信、その後イベントの解除
          close.addEventListener('click', saveOnClick)
          createModalEl.addEventListener('hidden.bs.modal', () => {
            //第二引数に値を指定する必要がある
            close.removeEventListener('click', saveOnClick);
          });
        }
      });
  },

  eventClick: function (info) {
    // イベントクリック時の月初めの値(info.view.currentStart)とDB(lock_month)群を比較
    axios // lock_month取得処理の呼び出し
      .post("/home/lock_month/show", {
        title: info.view.title
      })
      .then((response) => {
        var titleExists = response.data.titleExists;

        // チェックボックスのDOM要素を取得します
        let checkboxes = document.querySelectorAll('.form-check-input');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        // document.getElementById('createModal_description').innerText = info.startStr;
        document.getElementById('create_date').value = info.startStr;

        if (titleExists === true) { // データベースの月が1から始まる場合、+1を忘れずに
          cautionModal.show();
          document.getElementById('caution-text').innerText = info.event.startStr + " " + info.event.title;
        } else {
          deleteModal.show();
          document.getElementById('delete-text').innerText = info.event.startStr + " " + info.event.title;
          const close = document.getElementById('delete-btn');

          const deleteOnClick = async () => {
            // 再度titleExistsを取得して判定
            const response = await axios.post("/home/lock_month/show", {
              title: info.view.title
            });
            var titleExists = response.data.titleExists;

            if (titleExists === true) {
              cautionModal.show();
              document.getElementById('caution-text').innerText = info.event.startStr + " " + info.event.title;
            } else {
              axios
                .post("/home/delete", {
                  id: info.event.id
                })
                .then((response) => {
                  var event = calendar.getEventById(response.data.id)
                  event.remove();
                  // //renderevent();はv3まで
                  // calendar.refetchEvents();
                })
                .catch(() => {
                  // バリデーションエラーなど
                  alert("取得に失敗しました");
                });
            }
          };

          //保存ボタンによる送信、その後イベントの解除
          close.addEventListener('click', deleteOnClick)
          deleteModalEl.addEventListener('hidden.bs.modal', () => {
            //第二引数に値を指定する必要がある
            close.removeEventListener('click', deleteOnClick);
          });
        }
      });
  }
});

calendar.render();