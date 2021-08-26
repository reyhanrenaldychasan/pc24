@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <div class="card">
                    <div class="card-header">Update tags</div>
                    <div class="card-body">
                        <form action="{{ route('tags.update', $tag->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="">Name</label>
                                <input type="text" name="name" class="form-control" value="{{old('name') ? old('name') : $tag->name}}">
                            </div>
                            <input type="submit" value="Save Changes" class="btn btn-primary float-right">
                            @error('name')
                            <span class="error">{{ $message }}</span>
                            @enderror
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
