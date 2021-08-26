@extends('layouts.app')

@section('content')
    <div class="container">
        @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <div class="card">
                    <div class="card-header">Add Tags</div>
                    <div class="card-body">
                        <form action="{{ route('tags.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="{{old('name')}}">
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
