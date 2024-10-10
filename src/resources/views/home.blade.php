@extends('layouts.app')

@section('content')
  <section class="shift">
    <!-- カレンダーshift.jsの読み込み -->
    <div id='shift'></div>
  </section>
  <!-- CreateModal -->
  <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">予定を登録</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form name="create_form">
          <div class="modal-body">
            <label for="create_date" class="col-label">登録予定日:</label>
            <input type="date" class="form-control" id="create_date" name="date" value="">
            @foreach ($session_times as $key => $times)
              <div class="col-md-2 py-1 ml-4 my-auto">
                {{ Form::checkbox('session_times', $key, [], ['id' => 'tag' . $key, 'class' => 'form-check-input']) }}
                {{ Form::label($times['start_time'] . ' ~ ' . $times['end_time'], [], ['class' => 'form-check-label']) }}
              </div>
            @endforeach
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            <button type="button" class="btn btn-primary" id="store-btn" data-bs-dismiss="modal">保存する</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">予定確認</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <h5>登録されている予定</h5>
          <div id="confirm-text"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
          {{-- <button type="button" class="btn btn-danger" id="delete-btn" data-bs-dismiss="modal">削除する</button> --}}
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="cautionModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">予定確認</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <h5>提出期間を過ぎているため変更はできません</h5>
          <div id="caution-text"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
          {{-- <button type="button" class="btn btn-danger" id="delete-btn" data-bs-dismiss="modal">削除する</button> --}}
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">削除確認</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <h5>削除しますか？</h5>
          <div id="delete-text"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
          <button type="button" class="btn btn-danger" id="delete-btn" data-bs-dismiss="modal">削除する</button>
        </div>
      </div>
    </div>
  </div>
  {{-- <!-- EditModal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">予定を編集</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @method('patch')
                <form method="POST" action="{{ route('shift.update') }}">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" id="edit_id" value="" name="id">
                        <label for="edit_date" class="col-label">登録予定日:</label>
                        <input type="date" class="form-control" id="edit_date" name="date" value="">
                        <label for="edit_text" class="col-form-label">連絡事項:</label>
                        <input type="text" class="form-control" id="edit_text" name="text" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                        <button type="submit" class="btn btn-primary">保存する</button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            削除する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">削除確認</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">削除しますか？</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">やめる</button>
                    @method('delete')
                    <form method="POST" action="{{ route('shift.destroy') }}">
                        @csrf
                        <input type="hidden" id="delete_id" value="" name="id">
                        <button type="submit" class="btn btn-danger">はい</button>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
@endsection
