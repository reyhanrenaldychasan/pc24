@extends('layouts.app')

@push('css-scripts')
    <style>
        .btn {
            /* margin: -0.375rem -0.75rem; */
        }

        .my-btn {
            margin: -0.375rem -0.75rem;
        }

        .edit-button {
            margin-right: 0;
        }

        /* .my-btn:hover {
            background-color: #eee;
            text-decoration: none;
        } */

        .my-pr {
            padding-right: 2.50rem;
        }

        .modal-title {
            color: rgb(226, 0, 0);
        }
    </style>
@endpush
@section('content')
<div class="container">
    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-sm-12">
            <div class="card mt-3">
                <div class="card-header">Tags List <span class="ml-3"><a href="{{ route('tags.create') }}" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span></a></span></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach ($tags as $tag)
                            <li class="list-group-item" id="tags-{{$tag->id}}">
                                <div class="d-flex w-100">
                                    <div class="mr-auto">{{$tag->name}}</div>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tags.edit', $tag->id) }}" class="btn btn-primary my-btn edit-button">Edit</a>
                                        <a href="javascript:;" data-route="{{ route('tags.delete', $tag->id) }}" class="btn btn-danger my-btn delete-button" data-toggle="modal" data-target="#delete-modal">Delete</a>
                                    </div>

                                </div>
                            </li>
                            
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle"><span class="fa fa-exclamation-triangle"></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <h5>Are you sure?</h5>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
            <a href="#" class="btn btn-primary modal-yes">Yes</a>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('js-scripts')
    <script>
        $(document).ready(function() {
            @if (session('message'))
                var id = {{session('id')}};
                console.log(id);
                $('html, body').animate({
                    scrollTop: $("#tags-" + id).offset().top
                }, 1000);
            @endif
        })

        $('.delete-button').click(function() {
            $('.modal-yes').attr('href', $(this).data('route'));
        })
    </script>
@endpush
