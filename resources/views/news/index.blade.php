@extends('layouts.app')

@push('css-scripts')
    <style>
        /* h5 {
            font-size: 18px;
        } */

        a {
            color: black;
        }

        .card, .my-card {
            transition: 0.3s;
            height: 100%;
            /* max-width: 225px; */
            border-bottom: 5px solid transparent;
        }

        .img-container {
            position: relative;
            /* width: 250px; */
            /* height: 190px; */
        }

        .card-img {
            /* height: 318px; */
            height: 190px;
            width: 100%;
        }

        .card:hover, .my-card:hover {
            transform: scale(1.01);
            z-index: 1;
            /* box-shadow: 0 .2rem .5rem rgba(0, 0, 177, 0.5) !important */
            border-bottom: 5px solid #FFD263;
        }

        .card-text {
            color: rgb(99, 99, 99);
            margin-bottom: auto;
        }

        img {
            object-fit: cover;
        }

        .my-row {
            margin-left: -15px;
            margin-right: -15px;
        }

        .my-col {
            flex-basis: 50%;
            padding: 15px;
        }

        .my-card {
            display: flex;
            flex-direction: column;
            /* border: 1px solid rgba(0,0,0,.125); */
            border-radius: .25rem;
            position: relative;
            background-color: #eee;
            word-wrap: break-word;
            box-sizing: border-box;
            margin-bottom: -5px;
        }

        .my-card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 1rem;
            padding-bottom: -5px;
        }

        .my-date {
            /* position: absolute; */
            /* right: 0px;
            top: 0px;
            padding-right: 10px;
            padding-top: 5px; */
            text-decoration: none;
            font-size: 14px;
            color: gray;
        }

        /* .my-card .img-container {
            flex-basis: 20%;
        } */

        .card-span {
            background-color: rgb(209, 209, 209);
            font-size: 14px;
            display: inline-block;
        }


        @media (min-width: 576px) {
            
        }

        @media (min-width: 768px) {
            .my-col {
                flex-basis: 33%;
            }
        }

        @media (min-width: 992px) {
            .my-col {
                flex-basis: 25%;
            }
        }

    </style>
@endpush

@section('content')
<div class="container">
    <div class="d-flex align-items-center mb-4">
        <div class="d-flex flex-column">
            <div class="mb-2">
                <h4>News List</h4>
            </div>
            <div>
                <a href="{{ route('news.create') }}" class="btn btn-primary btn-block">Create New</a>
            </div>

        </div>
        <div class="ml-auto">
            <div class="input-group">
                {{-- <form action="{{ route('news.index.search') }}" method="GET"> --}}
                    <input type="text" class="form-control" id="search" name="keyword" placeholder="Search" value="{{ $keyword ?? null }}">
                    <div class="input-group-append">
                      <a href="javascript:;" class="btn btn-primary" id="btn-search"><span class="fa fa-search"></span></a>
                      {{-- <button type="submit" class="btn btn-primary"><span class="fa fa-search"></span></button> --}}
                    </div>
                {{-- </form> --}}
              </div>
        </div>
    </div>
    @if(session('message'))
        <div class="alert alert-success mt-2">{{ session('message') }}</div>
    @endif
    <div class="d-flex flex-wrap my-row">

        @forelse($news as $item)
        {{-- <div class="my-col">

            <a href="{{ route('news.edit', $item->id) }}" style="text-decoration: none">
                <div class="card shadow">
                    <div class="img-container">
                        <img class="card-img" src="{{ $item->thumbnail_image }}" alt="Card image cap">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $item->title }}</h5>
                        <p class="card-text">{{ $item->raw_content }}</p>
                    </div>
                </div>
            </a>
        </div> --}}
        
        <div class="my-col">
            <a href="{{ route('news.edit', $item->id) }}" style="text-decoration: none">
                <div class="my-card">
                    <div class="img-container">
                        <img class="card-img" src="{{ $item->thumbnail_image }}" alt="Card image cap">
                    </div>
    
                    <div class="my-card-body">
                        <h5 class="card-title mt-1">{{ $item->title }}</h5>
                        <div class="card-text">
                            <p class="card-text">{{ $item->raw_content }}</p>
                        </div>
                        <div class="tags-container">
                            @foreach ($item->tags as $tag)
                                <span class="card-span pt-1 pb-1 pr-2 pl-2 mt-1">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                        <div>
                            <div class="my-date mt-2">{{ $item->created_at->diffForHumans() }}, {{ $item->created_at->format('m/d/Y') }}</div>
                        </div>
                    </div>
                </div>
            </a>

        </div>
        @empty
        <div class="mt-4" style="width: 100%; text-align: center; color: gray"><h5>No news yet.</h5></div>
        @endforelse
    </div>
    <div class="mt-2 float-right">
        {{ $news->links() }}
    </div>
</div>
@endsection

@push('js-scripts')
    <script>
        $(document).ready(function() {
            $('#btn-search').click(function() {
                var keyword = $('#search').val();
                if(keyword){
                    var url = "{{url('/news/search')}}" + '/' + keyword;
                    // console.log(url);
                    window.location.href = url;
                } else {
                    window.location.href = "{{url('/news')}}";
                }
            })

            $('#search').keyup(function(e){
                if(e.keyCode == 13)
                {
                    $('#btn-search').click();
                }
            });

        })
    </script>
@endpush