@extends('adminlte::page')

@section('title', 'ユーザー一覧')

@section('content_header')
    <h1>ユーザー一覧</h1>
@stop

@section('content')
    {{-- 完了メッセージ --}}
    @if (session('message'))
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                ×
            </button>
            {{ session('message') }}
        </div>
    @endif

    {{-- 新規登録画面へ --}}
    <a class="btn btn-primary mb-2" href="{{ route('user.create') }}" role="button">新規登録</a>

    <div class="card">
        <div class="card-body" style="overflow: scroll">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ユーザー名</th>
                        <th>パスワード</th>
                        <th>配色</th>
                        <th>作成日</th>
                        <th>更新日</th>
                        <th style="width: 70px"></th>
                        {{-- <th class="col-sm-4"></th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td></td>
                            <td>{{ $user->color }}</td>
                            <td>{{ $user->created_at }}</td>
                            <td>{{ $user->updated_at }}</td>
                            <td>
                                <a class="btn btn-primary btn-sm mb-2" href="{{ route('user.edit', $user->id) }}"
                                    role="button">編集</a>
                                <form action="{{ route('user.destroy', $user->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    {{-- 簡易的に確認メッセージを表示 --}}
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('削除してもよろしいですか?');">
                                        削除
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
