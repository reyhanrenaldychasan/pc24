@extends('layouts.app')

@push('css-plugins')
    {{-- select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('css-scripts')
    <style>
        .doroppu {
            background-color: lightblue;
            outline: 2px dashed grey;
            outline-offset: -10px;
            height: 250px;
            width: 400px;
            padding: 10px;
            position: relative;
            transition: 0.2s;
            margin: auto;
        }

        .doroppu-text,
        .doroppu-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .doroppu-text {
            width: 80%;
        }

        .doroppu-label {
            width: 100%;
            height: 100%;
            padding: 10px;

        }

        .doroppu-label label {
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .doroppu-file {
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            position: absolute;
            z-index: -1;
        }

        .tags {
            width: 100%;
        }

        .error {
            color: rgb(230, 0, 0);

        }

        .doroppu.is-dragover {
            background-color: white;
        }

        #image {
            max-width: 400px;
            max-height: 250px;
            display: none;
        }

        @media (min-width: 768px) {
            .col-img-container {
                float: right;
            }
        }


        @media(min-width: 576px) {}

    </style>
@endpush

@section('content')
    <div class="container">
        <form action="{{ route('news.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row justify-content-center">
                <div class="col-sm-12">
                    {{-- <div class="doroppu">
                    <input class="doroppu-file" type="file" id="file" name="images">
                    <div class="doroppu-text">
                        <h1><span class="fa fa-download"></span></h1>
                        <strong>Choose a file</strong><span class="doroppu-dragndrop"> or drag it here</span>.
                    </div>
                    <div class="doroppu-label">
                        <label for="file"></label>
                    </div>

                    <div class="img-container">
                        <img id="image" src="">
                    </div>
                </div> --}}
                </div>
                <div class="col-sm-7">
                    <div class="form-group">
                        <label for="title">Thumbnail Image</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="customFile" name="image" accept="image/*">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
                        <div class="img-container mt-2">
                            <img id="image" src="{{ old('image') }}">
                        </div>
                        <div></div>
                        @error('image')
                            <span class="error">*{{ $message }}</span>
                        @enderror


                    </div>

                    <div class="form-group">
                        <label for="title">News Title</label>
                        <input type="text" id="title" class="form-control" name="title" value="{{ old('title') }}">
                        @error('title')
                            <span class="error">*{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        {{-- <input type="text" id="content" class="form-control" name="content" value="{{ old('content') }}"> --}}
                        <textarea name="content" id="editor" cols="30" rows="10">{{ old('content') }}</textarea>
                        @error('content')
                            <span class="error">*{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div>
                            <label for="tags">Tags</label>
                            <select class="tags form-control" name="tags[]" multiple="multiple">
                                @foreach ($tags as $tag)
                                    @php
                                        $selected = null;
                                        if (old('tags')) {
                                            foreach (old('tags') as $oldId) {
                                                if ($oldId == $tag->id) {
                                                    $selected = 'selected';
                                                }
                                            }
                                        }
                                    @endphp
                                    <option value="{{ $tag->id }}" {{ $selected }}>{{ $tag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('tags')
                            <span class="error">*{{ $message }}</span>
                        @enderror
                    </div>

                    <input type="submit" value="Submit" class="btn btn-primary">
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js-plugins')
    {{-- select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <script src="https://cdn.ckeditor.com/ckeditor5/26.0.0/classic/ckeditor.js"></script>

    <script src="{{ asset('js/CKeditorUploadAdapter.js') }}"></script>
@endpush

@push('js-scripts')
    <script>
        $(document).ready(function() {
            $('.tags').select2();

            var droppedFiles = false;
            var reader = new FileReader();
            var ext = null;
            $('.doroppu').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                })
                .on('dragover dragenter', function() {
                    $('.doroppu').addClass('is-dragover');
                })
                .on('dragleave dragend drop', function() {
                    $('.doroppu').removeClass('is-dragover');
                })
                .on('drop', function(e) {
                    droppedFiles = e.originalEvent.dataTransfer.files;
                    $('.doroppu-label').css('display', 'none');
                    reader.onload = imageIsLoaded;
                    reader.readAsDataURL(droppedFiles[0]);
                });

            $(":file").change(function() {
                if (this.files && this.files[0]) {
                    droppedFiles = this.files
                    reader.onload = imageIsLoaded;
                    reader.readAsDataURL(droppedFiles[0]);
                    var lastDot = droppedFiles[0].name.lastIndexOf('.');
                    ext = droppedFiles[0].name.substring(lastDot + 1);
                    console.log(ext);
                }
            });

            function imageIsLoaded(e) {
                $('#image').css('display', 'block');
                $('#image').attr('src', e.target.result);
                $('.doroppu-text').css("display", "none");
                var width = $('#image').width();
                var height = $('#image').height();
                $('.doroppu').css({
                    "width": width + 20,
                    "height": height + 20
                });


                // $('#image').css("max-width", "400px");
            }

            function adapterFunction(editor) {
                MyCustomUploadAdapterPlugin(editor, "{{env('APP_URL')}}");
            }

            ClassicEditor
            .create( document.querySelector( '#editor' ) , {
                extraPlugins: [ adapterFunction ],
            })
            .catch( error => {
                console.error( error );
            } );
        })
        
    </script>
@endpush
